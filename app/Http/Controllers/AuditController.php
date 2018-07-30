<?php

namespace App\Http\Controllers;

use App\User;
use Exception;
use App\Download;
use App\AuditResult;
use App\Events\AuditResultCreated;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Promise as GuzzlePromise;
use Symfony\Component\BrowserKit\Response;
use Symfony\Component\DomCrawler\Crawler;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuditController extends Controller
{

    protected $guzzleConfig;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->guzzleConfig = [
            'timeout' => 120,
            'verify' => false,
            'delay' => 500
            ];
        if (\Config::get('app.debug')) {
            $this->guzzleConfig['proxy'] = 'http://127.0.0.1:8888';
        }
    }

    /**
     * Show the application scan.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('scan');
    }

    /**
     * Start web scraping.
     *
     * @return \Illuminate\Http\Response
     */
    public function scan(Request $request)
    {
        $validatedData = $request->validate([
            'domain' => 'required|regex:/^(?!:\/\/)([a-zA-Z0-9-_]+\.)*[a-zA-Z0-9][a-zA-Z0-9-_]+\.[a-zA-Z]{2,11}?$/'
        ]);
        
        $domain = request('domain');
        $hostip = gethostbyname($domain);
        $risk_info = 1;
        $risk_low = 0;
        $risk_medium = 0;
        $risk_high = 0;
        $audit_data = [];
        $audit_data['web_domain'] = $domain;
        $audit_data['host_ip'] = $hostip;

        $client = new GuzzleClient($this->guzzleConfig);

        $header_asn = [
            'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/64.0.3282.140 Safari/537.36',
            'Referer' => 'https://www.ultratools.com/tools/asnInfo'
        ];
        $header_digicert = [
            'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/64.0.3282.140 Safari/537.36',
            'Origin' => 'https://www.digicert.com',
            'Referer' => 'https://www.digicert.com/help/',
            'X-Requested-With' => 'XMLHttpRequest',
            'Content-Type' => 'application/x-www-form-urlencoded; charset=UTF-8'
        ];
        $header_mx = [
            'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/64.0.3282.140 Safari/537.36',
            'Origin' => 'https://mxtoolbox.com',
            'Referer' => 'https://mxtoolbox.com/SuperTool.aspx',
            'X-Requested-With' => 'XMLHttpRequest',
            'Content-Type' => 'application/json'
        ];
        $header_openresolver = [
            'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/64.0.3282.140 Safari/537.36',
            'Referer' => 'http://openresolver.com/'
        ];


        if (\Config::get('app.simultanous')) {
            // Simultaneous Request
            $promises = [
                'asn' => $client->getAsync('https://www.ultratools.com/tools/asnInfoResult?domainName='.$hostip, ['headers' => $header_asn]),
                'ssl' => $client->postAsync('https://www.digicert.com/api/check-host.php', ['headers' => $header_digicert, 'body' => "r=".rand(0, 1000)."&host=$domain&order_id="]),
                'heartbleed' => $client->postAsync('https://www.digicert.com/api/check-vuln.php', ['headers' => $header_digicert, 'body' => "r=".rand(0, 1000)."&host=$domain&order_id="]),
                'dns' => $client->postAsync('https://mxtoolbox.com/Public/Lookup.aspx/DoLookup2', ['headers' => $header_mx, 'body' => "{\"inputText\":\"dns:$domain\",\"resultIndex\":1}"]),
                'cname' => $client->postAsync('https://mxtoolbox.com/Public/Lookup.aspx/DoLookup2', ['headers' => $header_mx, 'body' => "{\"inputText\":\"cname:$domain\",\"resultIndex\":1}"]),
                'txt' => $client->postAsync('https://mxtoolbox.com/Public/Lookup.aspx/DoLookup2', ['headers' => $header_mx, 'body' => "{\"inputText\":\"txt:$domain\",\"resultIndex\":1}"]),
                'whois' => $client->postAsync('https://mxtoolbox.com/Public/Lookup.aspx/DoLookup2', ['headers' => $header_mx, 'body' => "{\"inputText\":\"whois:$domain\",\"resultIndex\":1}"]),
                'openresolver' => $client->getAsync('http://openresolver.com/?ip='.$domain, ['headers' => $header_openresolver]),
                'mx' => $client->postAsync('https://mxtoolbox.com/Public/Lookup.aspx/DoLookup2', ['headers' => $header_mx, 'body' => "{\"inputText\":\"mx:$domain\",\"resultIndex\":1}"]),
                'smtp' => $client->postAsync('https://mxtoolbox.com/Public/Lookup.aspx/DoLookup2', ['headers' => $header_mx, 'body' => "{\"inputText\":\"smtp:$domain\",\"resultIndex\":1}"]),
                'dmarc' => $client->postAsync('https://mxtoolbox.com/Public/Lookup.aspx/DoLookup2', ['headers' => $header_mx, 'body' => "{\"inputText\":\"dmarc:$domain\",\"resultIndex\":1}"]),
                'spf' => $client->postAsync('https://mxtoolbox.com/Public/Lookup.aspx/DoLookup2', ['headers' => $header_mx, 'body' => "{\"inputText\":\"spf:$domain\",\"resultIndex\":1}"]),
            ];

            $results = GuzzlePromise\unwrap($promises);
            $results = GuzzlePromise\settle($promises)->wait();

            $asn_result = new Response((string) $results['asn']['value']->getBody(), $results['asn']['value']->getStatusCode(), $results['asn']['value']->getHeaders());
            $ssl_result = new Response((string) $results['ssl']['value']->getBody(), $results['ssl']['value']->getStatusCode(), $results['ssl']['value']->getHeaders());
            $heartbleed_result = new Response((string) $results['heartbleed']['value']->getBody(), $results['heartbleed']['value']->getStatusCode(), $results['heartbleed']['value']->getHeaders());
            $dns_result = new Response((string) $results['dns']['value']->getBody(), $results['dns']['value']->getStatusCode(), $results['dns']['value']->getHeaders());
            $cname_result = new Response((string) $results['cname']['value']->getBody(), $results['cname']['value']->getStatusCode(), $results['cname']['value']->getHeaders());
            $txt_result = new Response((string) $results['txt']['value']->getBody(), $results['txt']['value']->getStatusCode(), $results['txt']['value']->getHeaders());
            $whois_result = new Response((string) $results['whois']['value']->getBody(), $results['whois']['value']->getStatusCode(), $results['whois']['value']->getHeaders());
            $openresolver_result = new Response((string) $results['openresolver']['value']->getBody(), $results['openresolver']['value']->getStatusCode(), $results['openresolver']['value']->getHeaders());
            $mx_result = new Response((string) $results['mx']['value']->getBody(), $results['mx']['value']->getStatusCode(), $results['mx']['value']->getHeaders());
            $smtp_result = new Response((string) $results['smtp']['value']->getBody(), $results['smtp']['value']->getStatusCode(), $results['smtp']['value']->getHeaders());
            $dmarc_result = new Response((string) $results['dmarc']['value']->getBody(), $results['dmarc']['value']->getStatusCode(), $results['dmarc']['value']->getHeaders());
            $spf_result = new Response((string) $results['spf']['value']->getBody(), $results['spf']['value']->getStatusCode(), $results['spf']['value']->getHeaders());
            
        }
        else{
            // Squence Request
            $results = $client->get('https://www.ultratools.com/tools/asnInfoResult?domainName='.$hostip, ['headers' => $header_asn]);
            $asn_result = new Response((string) $results->getBody(), $results->getStatusCode(), $results->getHeaders());
            $results = $client->post('https://www.digicert.com/api/check-host.php', ['headers' => $header_digicert, 'body' => "r=".rand(0, 1000)."&host=$domain&order_id="]);
            $ssl_result = new Response((string) $results->getBody(), $results->getStatusCode(), $results->getHeaders());
            $results = $client->post('https://www.digicert.com/api/check-vuln.php', ['headers' => $header_digicert, 'body' => "r=".rand(0, 1000)."&host=$domain&order_id="]);
            $heartbleed_result = new Response((string) $results->getBody(), $results->getStatusCode(), $results->getHeaders());
            $results = $client->post('https://mxtoolbox.com/Public/Lookup.aspx/DoLookup2', ['headers' => $header_mx, 'body' => "{\"inputText\":\"dns:$domain\",\"resultIndex\":1}"]);
            $dns_result = new Response((string) $results->getBody(), $results->getStatusCode(), $results->getHeaders());
            $results = $client->post('https://mxtoolbox.com/Public/Lookup.aspx/DoLookup2', ['headers' => $header_mx, 'body' => "{\"inputText\":\"cname:$domain\",\"resultIndex\":1}"]);
            $cname_result = new Response((string) $results->getBody(), $results->getStatusCode(), $results->getHeaders());
            $results = $client->post('https://mxtoolbox.com/Public/Lookup.aspx/DoLookup2', ['headers' => $header_mx, 'body' => "{\"inputText\":\"txt:$domain\",\"resultIndex\":1}"]);
            $txt_result = new Response((string) $results->getBody(), $results->getStatusCode(), $results->getHeaders());
            $results = $client->post('https://mxtoolbox.com/Public/Lookup.aspx/DoLookup2', ['headers' => $header_mx, 'body' => "{\"inputText\":\"whois:$domain\",\"resultIndex\":1}"]);
            $whois_result = new Response((string) $results->getBody(), $results->getStatusCode(), $results->getHeaders());
            $results = $client->get('http://openresolver.com/?ip='.$domain, ['headers' => $header_openresolver]);
            $openresolver_result = new Response((string) $results->getBody(), $results->getStatusCode(), $results->getHeaders());
            $results = $client->post('https://mxtoolbox.com/Public/Lookup.aspx/DoLookup2', ['headers' => $header_mx, 'body' => "{\"inputText\":\"mx:$domain\",\"resultIndex\":1}"]);
            $mx_result = new Response((string) $results->getBody(), $results->getStatusCode(), $results->getHeaders());
            $results = $client->post('https://mxtoolbox.com/Public/Lookup.aspx/DoLookup2', ['headers' => $header_mx, 'body' => "{\"inputText\":\"smtp:$domain\",\"resultIndex\":1}"]);
            $smtp_result = new Response((string) $results->getBody(), $results->getStatusCode(), $results->getHeaders());
            $results = $client->post('https://mxtoolbox.com/Public/Lookup.aspx/DoLookup2', ['headers' => $header_mx, 'body' => "{\"inputText\":\"dmarc:$domain\",\"resultIndex\":1}"]);
            $dmarc_result = new Response((string) $results->getBody(), $results->getStatusCode(), $results->getHeaders());
            $results = $client->post('https://mxtoolbox.com/Public/Lookup.aspx/DoLookup2', ['headers' => $header_mx, 'body' => "{\"inputText\":\"spf:$domain\",\"resultIndex\":1}"]);
            $spf_result = new Response((string) $results->getBody(), $results->getStatusCode(), $results->getHeaders());
            
        }
        
        //Parsing data asn
        $asn_info = "<font size=\"3\"><b>ASN Information: No Results Found</b></font></br>";
        try {
            if($asn_result->getStatus() == '200'){
                $result = new Crawler($asn_result->getContent());
                $asn_name = $result->filter('.tool-results-heading')->text();
                $asn_country = $result->filter('.tool-results > div')->eq(0)->filter('.value')->text();
                $asn_regdate = $result->filter('.tool-results > div')->eq(1)->filter('.value')->text();
                $asn_registrar = $result->filter('.tool-results > div')->eq(2)->filter('.value')->text();
                $asn_owner = $result->filter('.tool-results > div')->eq(3)->filter('.value')->text();
                $asn_info = "<font size=\"3\"><b>ASN Information: $asn_name</b></font><br/>
                Country: $asn_country<br/>
                Registration Date: $asn_regdate<br/>
                Registrar: $asn_registrar<br/>
                Owner: $asn_owner<br/>";
            }
        } catch (Exception $e) {
            report($e);
            $asn_info = "<font size=\"3\"><b>ASN Information: No Results Found</b></font></br>";
        }
        $audit_data['asn_info'] = $asn_info;
        
        
        //Parsing data ssl
        $ssl_info = "";
        $has_ssl = false;
        $vuln_heartbleed = false;
        $ssl_expired = false;
        $ssl_response = false;
        $ssl_nottrusted = false;
        $audit_data['ssl_heartbleed'] = false;
        $audit_data['ssl_expired'] = false;
        try {
            if($ssl_result->getStatus() == '200'){
                $ssl_response = true;
                $ssl_info = "<div class=\"table-responsive\">
                <table class=\"table table-striped table-bordered table-condensed tool-result-table\">
                  <thead>
                     <tr>
                        <th>Status</th>
                        <th>Name</th>
                        <th>Details</th>
                     </tr>
                  </thead>
                  <tbody>
                     <tr>
                        <td><img src=\"https://mxtoolbox.com/public/images/statusicons/problem.png\" width=\"17\"></td>
                        <td>SSL Certificate</td>
                        <td>No SSL certificates were found on $domain</td>
                     </tr>
                  </tbody>
                </table>
                </div><br/>";
                $result = new Crawler($ssl_result->getContent());
                if($result->filter('h2')->eq(0)->text() != 'Unable to connect'){
                    // echo $crawler = $result->filter('h2')->eq(1)->nextAll()->html();
                    $has_ssl = true;
                    $statusdns = $result->filter('h2')->eq(0)->attr('class');
                    switch ($statusdns) {
                        case 'ok':
                            $statusdns = '<img src="https://mxtoolbox.com/public/images/statusicons/ok.png" width="17">';
                            break;
                        case 'warning':
                            $statusdns = '<img src="https://mxtoolbox.com/public/images/statusicons/warning.png" width="17">';
                            break;
                        case 'error':
                            $statusdns = '<img src="https://mxtoolbox.com/public/images/statusicons/problem.png" width="17">';
                            break;
                        default:
                            $statusdns = '';
                    }
                    $dnsname = $result->filter('h2')->eq(0)->text();
                    $dnsdetail = !$result->filter('h2')->eq(0)->nextAll()->attr('class') ? $result->filter('h2')->eq(0)->nextAll()->html() : '';

                    $statuscert = $result->filter('h2')->eq(1)->attr('class');
                    switch ($statuscert) {
                        case 'ok':
                            $statuscert = '<img src="https://mxtoolbox.com/public/images/statusicons/ok.png" width="17">';
                            break;
                        case 'warning':
                            $statuscert = '<img src="https://mxtoolbox.com/public/images/statusicons/warning.png" width="17">';
                            break;
                        case 'error':
                            $statuscert = '<img src="https://mxtoolbox.com/public/images/statusicons/problem.png" width="17">';
                            break;
                        default:
                            $statuscert = '';
                    }
                    $sslname = $result->filter('h2')->eq(1)->text();
                    $ssldetail = !$result->filter('h2')->eq(1)->nextAll()->attr('class') ? $result->filter('h2')->eq(1)->nextAll()->html() : '';

                    $statusrevoked = $result->filter('h2')->eq(2)->attr('class');
                    switch ($statusrevoked) {
                        case 'ok':
                            $statusrevoked = '<img src="https://mxtoolbox.com/public/images/statusicons/ok.png" width="17">';
                            break;
                        case 'warning':
                            $statusrevoked = '<img src="https://mxtoolbox.com/public/images/statusicons/warning.png" width="17">';
                            break;
                        case 'error':
                            $statusrevoked = '<img src="https://mxtoolbox.com/public/images/statusicons/problem.png" width="17">';
                            break;
                        default:
                            $statusrevoked = '';
                    }
                    $revokedname = $result->filter('h2')->eq(2)->text();
                    $revokeddetail = !$result->filter('h2')->eq(2)->nextAll()->attr('class') ? str_replace('td', 'span', str_replace('</tr>', '<br/>', str_replace('<tr>', '', $result->filter('h2')->eq(2)->nextAll()->html())))  : '';

                    $statusexpired = $result->filter('h2')->eq(3)->attr('class');
                    switch ($statusexpired) {
                        case 'ok':
                            $statusexpired = '<img src="https://mxtoolbox.com/public/images/statusicons/ok.png" width="17">';
                            break;
                        case 'warning':
                            $statusexpired = '<img src="https://mxtoolbox.com/public/images/statusicons/warning.png" width="17">';
                            break;
                        case 'error':
                            $ssl_expired = true;
                            $statusexpired = '<img src="https://mxtoolbox.com/public/images/statusicons/problem.png" width="17">';
                            break;
                        default:
                            $statusexpired = '';
                    }
                    $expiredname = $result->filter('h2')->eq(3)->text();
                    $expireddetail = !$result->filter('h2')->eq(3)->nextAll()->attr('class') ? $result->filter('h2')->eq(3)->nextAll()->html() : '';

                    $statustrust = '';
                    $trustname = '';
                    $trustdetail = '';
                    try {
                        $statustrust = $result->filter('h2')->eq(5)->attr('class');
                        switch ($statustrust) {
                            case 'ok':
                                $statustrust = '<img src="https://mxtoolbox.com/public/images/statusicons/ok.png" width="17">';
                                break;
                            case 'warning':
                                $statustrust = '<img src="https://mxtoolbox.com/public/images/statusicons/warning.png" width="17">';
                                break;
                            case 'error':
                                $ssl_nottrusted = true;
                                $statustrust = '<img src="https://mxtoolbox.com/public/images/statusicons/problem.png" width="17">';
                                break;
                            default:
                                $statustrust = '';
                        }
                        $trustname = $result->filter('h2')->last()->html();
                        $trustdetail = $result->filter('p')->last()->html();
                    }
                    catch(Exception $e){
                    }

                    $ssl_info = "<div class=\"tool-result-body\">
                    <div class=\"table-responsive\">
                    <table class=\"table table-striped table-bordered table-condensed tool-result-table\">
                    <thead>
                    <tr>
                    <th>Status</th>
                    <th>Name</th>
                    <th>Details</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr>
                    <td>$statusdns</td>
                    <td>$dnsname</td>
                    <td>$dnsdetail</td>
                    </tr>
                    <tr>
                    <td>$statuscert</td>
                    <td>$sslname</td>
                    <td>$ssldetail</td>
                    </tr>
                    <tr>
                    <td>$statusrevoked</td>
                    <td>$revokedname</td>
                    <td>$revokeddetail</td>
                    </tr>
                    <tr>
                    <td>$statusexpired</td>
                    <td>$expiredname</td>
                    <td>$expireddetail</td>
                    </tr>
                    <tr>
                    <td>$statustrust</td>
                    <td>$trustname</td>
                    <td>$trustdetail</td>
                    </tr>";

                    if($heartbleed_result->getStatus() == '200'){
                        $result = new Crawler($heartbleed_result->getContent());
                        try {
                            $statusheartbleed = $result->filter('h2')->eq(0)->attr('class');
                            switch ($statusheartbleed) {
                                case 'ok':
                                    $statusheartbleed = '<img src="https://mxtoolbox.com/public/images/statusicons/ok.png" width="17">';
                                    break;
                                case 'warning':
                                    $vuln_heartbleed = true;
                                    $statusheartbleed = '<img src="https://mxtoolbox.com/public/images/statusicons/warning.png" width="17">';
                                    break;
                                case 'error':
                                    $vuln_heartbleed = true;
                                    $statusheartbleed = '<img src="https://mxtoolbox.com/public/images/statusicons/problem.png" width="17">';
                                    break;
                                default:
                                    $statusheartbleed = '';
                            }
                            $heartbleedname = $result->filter('h2')->eq(0)->text();
                            $heartbleeddetail = !$result->filter('h2')->eq(0)->nextAll()->attr('class') ? $result->filter('h2')->eq(0)->nextAll()->html() : '';
                            $ssl_info .= "<tr>
                            <td>$statusheartbleed</td>
                            <td>$heartbleedname</td>
                            <td>$heartbleeddetail</td>
                            </tr>";
                        }
                        catch(Exception $e){
                        }
                    }

                    $ssl_info .= "</tbody>
                    </table>
                    </div>
                    </div><br/>";
                }
                
            }
            if($vuln_heartbleed){
                $risk_high += 1;
                $audit_data['ssl_heartbleed'] = true;
                $ssl_info = "<font size=\"3\"><b>SSL Certificate - </b></font><font size=\"3\" color=\"#F45C51\"><b>High</b></font></br>".$ssl_info;
                $ssl_info .= "<div style=\"margin-left: 20px; padding: 10px; border-left: 4px solid #F45C51\">
                    <b>Risk Level:</b> High<br/>
                    Information: SSL certificates that are vulnerable to heartbleed attacks allow attackers to get information such as user login credentials, sessions, private key, etc from memory leak.<br/>
                    More information: <a href=\"https://www.us-cert.gov/ncas/alerts/TA14-098A\" target=\"_blank\">https://www.us-cert.gov/ncas/alerts/TA14-098A</a><br/>
                    Retrieved from: <a href=\"https://www.digicert.com/help/\" target=\"_blank\">https://www.digicert.com/help/</a><br/>
                </div><br/>";
            }
            else if($ssl_expired || $ssl_nottrusted){
                $risk_medium += 1;
                $audit_data['ssl_expired'] = true;
                $ssl_info = "<font size=\"3\"><b>SSL Certificate - </b></font><font size=\"3\" color=\"#FFE04F\"><b>Medium</b></font></br>".$ssl_info;
                $ssl_info .= "<div style=\"margin-left: 20px; padding: 10px; border-left: 4px solid #FFE04F\">
                    <b>Risk Level:</b> Medium<br/>
                    Information: Not trusted or expired SSL certificate on a website will display an untrusted certificate warning in the browser. This can reduce the user's trust when accessing the website.<br/>
                    Retrieved from: <a href=\"https://www.digicert.com/help/\" target=\"_blank\">https://www.digicert.com/help/</a><br/>
                </div><br/>";
            }
            else if($has_ssl){
                $risk_info += 1;
                $ssl_info = "<font size=\"3\"><b>SSL Certificate - </b></font><font size=\"3\" color=\"#3B8AD5\"><b>Informational</b></font></br>".$ssl_info;
                $ssl_info .= "<div style=\"margin-left: 20px; padding: 10px; border-left: 4px solid #3B8AD5\">
                    <b>Risk Level:</b> Informational<br/>
                    Retrieved from: <a href=\"https://www.digicert.com/help/\" target=\"_blank\">https://www.digicert.com/help/</a><br/>
                </div><br/>";
            }
            else if($ssl_response){
                $risk_medium += 1;
                $ssl_info = "<font size=\"3\"><b>SSL Certificate - </b></font><font size=\"3\" color=\"#FFE04F\"><b>Medium</b></font></br>".$ssl_info;
                $ssl_info .= "<div style=\"margin-left: 20px; padding: 10px; border-left: 4px solid #FFE04F\">
                    <b>Risk Level:</b> Medium<br/>
                    Information: Websites that do not use SSL Certificates are easily attacked by sniffing. Information such as credential usernames and passwords and user sessions are obtained easily.<br/>
                    Retrieved from: <a href=\"https://www.digicert.com/help/\" target=\"_blank\">https://www.digicert.com/help/</a><br/>
                </div><br/>";
            }
            else{
                $ssl_info = "<font size=\"3\"><b>SSL Certificate: No Results Found</b></font></br>";
            }
        } catch (Exception $e) {
            report($e);
            $ssl_info = "<font size=\"3\"><b>SSL Certificate: No Results Found</b></font></br>";
        }
        $audit_data['ssl_info'] = $ssl_info;


        //Parsing data dns
        $dns_info = "";
        $has_problem = false;
        $has_ok = false;
        $dns_response = false;
        try {
            if($dns_result->getStatus() == '200'){
                $dns_response = true;
                $decode = json_decode($dns_result->getContent(), true);
                $decode = json_decode($decode["d"], true);
                $crawler = new Crawler($decode["HTML_Value"]);
                $crawler->filter('.ab-show-asn-link')->each(function (Crawler $crawler) {
                    foreach ($crawler as $node) {
                        $node->parentNode->removeChild($node);
                    }
                });
                $dns_table = $crawler->filter('.tool-result-body .tool-result-body')->html();
                $has_problem = str_contains($dns_table, 'problem.png');
                $has_ok = str_contains($dns_table, 'ok.png');
                $dns_info = "<div class=\"tool-result-body\">
                <div class=\"table-responsive\">
                $dns_table
                </div>
                </div><br/>";
            }
            if($has_problem && $has_ok){
                $risk_low += 1;
                $dns_info = "<font size=\"3\"><b>DNS Server - </b></font><font size=\"3\" color=\"#5ECA62\"><b>Low</b></font></br>".$dns_info;
                $dns_info .= "<div style=\"margin-left: 20px; padding: 10px; border-left: 4px solid #5ECA62\">
                    <b>Risk Level:</b> Low<br/>
                    Information: One of the problematic DNS found. The website address is still accessible but the DNS server is not in its optimal state.<br/>
                    Retrieved from: <a href=\"https://mxtoolbox.com/DNSCheck.aspx\" target=\"_blank\">https://mxtoolbox.com/DNSCheck.aspx</a><br/>
                </div><br/>";
            }
            else if($has_problem){
                $risk_medium += 1;
                $dns_info = "<font size=\"3\"><b>DNS Server - </b></font><font size=\"3\" color=\"#FFE04F\"><b>Medium</b></font></br>".$dns_info;
                $dns_info .= "<div style=\"margin-left: 20px; padding: 10px; border-left: 4px solid #FFE04F\">
                    <b>Risk Level:</b> Medium<br/>
                    Information: The problematic DNS status found on the website. This may cause the domain address of the domain not accessible.<br/>
                    Retrieved from: <a href=\"https://mxtoolbox.com/DNSCheck.aspx\" target=\"_blank\">https://mxtoolbox.com/DNSCheck.aspx</a><br/>
                </div><br/>";
            }
            else if($dns_response){
                $risk_info += 1;
                $dns_info = "<font size=\"3\"><b>DNS Server - </b></font><font size=\"3\" color=\"#3B8AD5\"><b>Informational</b></font></br>".$dns_info;
                $dns_info .= "<div style=\"margin-left: 20px; padding: 10px; border-left: 4px solid #3B8AD5\">
                    <b>Risk Level:</b> Informational<br/>
                    Retrieved from: <a href=\"https://mxtoolbox.com/DNSCheck.aspx\" target=\"_blank\">https://mxtoolbox.com/DNSCheck.aspx</a><br/>
                </div><br/>";
            }
            else{
                $dns_info = "<font size=\"3\"><b>DNS Server: No Results Found</b></font></br>";
            }
        } catch (Exception $e) {
            report($e);
            $dns_info = "<font size=\"3\"><b>DNS Server: No Results Found</b></font></br>";
        }
        $audit_data['dns_info'] = $dns_info;


        //Parsing data cname
        $cname_info = "";
        $cname_record = false;
        $cname_response = false;
        try {
            if($cname_result->getStatus() == '200'){
                $cname_response = true;
                $decode = json_decode($cname_result->getContent(), true);
                $decode = json_decode($decode["d"], true);
                $crawler = new Crawler($decode["HTML_Value"]);
                $cname_table = $crawler->filter('.tool-result-body .tool-result-body')->html();
                if($cname_table){
                    $cname_record = true;
                    $tb_status = $crawler->filter('.tool-result-body table')->last()->html();
                    $cname_info = "<div class=\"tool-result-body\">
                    <div class=\"table-responsive\">
                    $cname_table
                    </div>
                    <div class=\"table-responsive\">
                    <table class=\"table table-striped table-bordered table-condensed tool-result-table\">
                    $tb_status
                    </table>
                    </div>
                    </div><br/>";
                }
                else{
                    $crawler->filter('.tool-result-body > table')->last()->filter('thead > tr > th')->eq(3)->each(function (Crawler $crawler) {
                        foreach ($crawler as $node) {
                            $node->parentNode->removeChild($node);
                        }
                    });
                    $crawler->filter('.tool-result-body > table')->last()->filter('tbody > tr')->each(function ($node){
                        $node->filter('td')->eq(3)->each(function (Crawler $crawler) {
                            foreach ($crawler as $node) {
                                $node->parentNode->removeChild($node);
                            }
                        });
                    });
                    $tb_status = $crawler->filter('.tool-result-body table')->html();
                    $cname_info = "<div class=\"tool-result-body\">
                    <div class=\"table-responsive\"></div>
                    <div class=\"table-responsive\">
                    <table class=\"table table-striped table-bordered table-condensed tool-result-table\">
                    $tb_status
                    </table>
                    </div>
                    </div><br/>";
                }  
            }
            if($cname_record || $cname_response){
                $risk_info += 1;
                $cname_info = "<font size=\"3\"><b>CNAME Record - </b></font><font size=\"3\" color=\"#3B8AD5\"><b>Informational</b></font></br>".$cname_info;
                $cname_info .= "<div style=\"margin-left: 20px; padding: 10px; border-left: 4px solid #3B8AD5\">
                    <b>Risk Level:</b> Informational<br/>
                    Retrieved from: <a href=\"https://mxtoolbox.com/CnameLookup.aspx\" target=\"_blank\">https://mxtoolbox.com/CnameLookup.aspx</a><br/>
                </div><br/>";
            }
            else{
                $cname_info = "<font size=\"3\"><b>CNAME Record: No Results Found</b></font></br>";
            }
        } catch (Exception $e) {
            report($e);
            $cname_info = "<font size=\"3\"><b>CNAME Record: No Results Found</b></font></br>";
        }
        $audit_data['cname_info'] = $cname_info;


        //Parsing data txt
        $txt_info = "";
        $txt_record = false;
        $txt_response = false;
        try {
            if($txt_result->getStatus() == '200'){
                $txt_response = true;
                $decode = json_decode($txt_result->getContent(), true);
                $decode = json_decode($decode["d"], true);
                $crawler = new Crawler($decode["HTML_Value"]);
                $txt_table = $crawler->filter('.tool-result-body .tool-result-body')->html();
                if($txt_table){
                    $txt_record = true;
                    $tb_status = $crawler->filter('.tool-result-body table')->last()->html();
                    $txt_info = "<div class=\"tool-result-body\">
                    <div class=\"table-responsive\">
                    $txt_table
                    </div>
                    <div class=\"table-responsive\">
                    <table class=\"table table-striped table-bordered table-condensed tool-result-table\">
                    $tb_status
                    </table>
                    </div>
                    </div><br/>";
                }
                else{
                    $crawler->filter('.tool-result-body > table')->last()->filter('thead > tr > th')->eq(3)->each(function (Crawler $crawler) {
                        foreach ($crawler as $node) {
                            $node->parentNode->removeChild($node);
                        }
                    });
                    $crawler->filter('.tool-result-body > table')->last()->filter('tbody > tr')->each(function ($node){
                        $node->filter('td')->eq(3)->each(function (Crawler $crawler) {
                            foreach ($crawler as $node) {
                                $node->parentNode->removeChild($node);
                            }
                        });
                    });
                    $tb_status = $crawler->filter('.tool-result-body table')->html();
                    $txt_info = "<div class=\"tool-result-body\">
                    <div class=\"table-responsive\"></div>
                    <div class=\"table-responsive\">
                    <table class=\"table table-striped table-bordered table-condensed tool-result-table\">
                    $tb_status
                    </table>
                    </div>
                    </div><br/>";
                }
                
            }
            if($txt_response || $txt_record){
                $risk_info += 1;
                $txt_info = "<font size=\"3\"><b>TXT Record - </b></font><font size=\"3\" color=\"#3B8AD5\"><b>Informational</b></font></br>".$txt_info;
                $txt_info .= "<div style=\"margin-left: 20px; padding: 10px; border-left: 4px solid #3B8AD5\">
                    <b>Risk Level:</b> Informational<br/>
                    Retrieved from: <a href=\"https://mxtoolbox.com/TXTLookup.aspx\" target=\"_blank\">https://mxtoolbox.com/TXTLookup.aspx</a><br/>
                </div><br/>";
            }
            else{
                $txt_info = "<font size=\"3\"><b>TXT Record: No Results Found</b></font></br>";
            }
        } catch (Exception $e) {
            report($e);
            $txt_info = "<font size=\"3\"><b>TXT Record: No Results Found</b></font></br>";
        }
        $audit_data['txt_info'] = $txt_info;
        

        //Parsing data whois
        $whois_info = "";
        $domain_email = '';
        $domain_owner = '';
        $has_registrant = false;
        $whois_response = false;
        $domain_expired = false;
        $audit_data['whois_registrant'] = false;
        $audit_data['whois_domain_email'] = '';
        $audit_data['whois_domain_owner'] = '';
        try {
            if($whois_result->getStatus() == '200'){
                $whois_response = true;
                $decode = json_decode($whois_result->getContent(), true);
                $decode = json_decode($decode["d"], true);
                $crawler = new Crawler($decode["HTML_Value"]);
                $data = $crawler->filter('.tool-result-body > table')->eq(0)->filter('tbody > tr')->each(function ($node, $i) {
                    try {
                        if($node->filter('td')->text()=='Registrant Email'){
                            return $node->parents()->filter('tr')->eq($i)->filter('td')->eq(1)->text();
                        }
                    }
                    catch(Exception $e){
                    }
                });
                
                if(isset($data[4]) && filter_var(trim($data[4]), FILTER_VALIDATE_EMAIL)){
                    $domain_email = $data[4];
                    $has_registrant = true;
                }
                $data = $crawler->filter('.tool-result-body > table')->eq(0)->filter('tbody > tr')->each(function ($node, $i) {
                    try {
                        if($node->filter('td')->text()=='Registrant Name'){
                            return $node->parents()->filter('tr')->eq($i)->filter('td')->eq(1)->text();
                        }
                    }
                    catch(Exception $e){
                    }
                });
                if(isset($data[1])){
                    $domain_owner = $data[1];
                }
                $data = $crawler->filter('.tool-result-body > table')->eq(0)->filter('tbody > tr')->each(function ($node, $i) {
                    try {
                        if($node->filter('td')->text()=='Expiration Date'){
                            return $node->parents()->filter('tr')->eq($i)->filter('td')->eq(1)->text();
                        }
                    }
                    catch(Exception $e){
                    }
                });
                if(isset($data[0])){
                    $expiry_time = strtotime($data[0]);
                    $now_time = time();
                    if($expiry_time && $now_time > $expiry_time){
                        $domain_expired = true;
                    }
                }
                $crawler->filter('.tool-result-body .tool-result-body div')->each(function (Crawler $crawler) {
                    foreach ($crawler as $node) {
                        $node->parentNode->removeChild($node);
                    }
                });
                $whois_table = $crawler->filter('.tool-result-body .tool-result-body')->html();
                $whois_table = str_replace('<tr class="full-width"></tr>', '', $whois_table);
                $whois_table = preg_replace('/<tbody>(\r?\n){2,}<\/tbody>/', '', $whois_table);
                if(preg_match_all('/<tbody><\/tbody>/', $whois_table, $matches) >= 2){
                    $whois_response = false;
                }
                $whois_table = preg_replace('/<tbody><\/tbody>/', '', $whois_table);
                $whois_info = "<div class=\"tool-result-body\">
                <div class=\"table-responsive\">
                $whois_table
                </div>
                </div><br/>";
            }
            if($domain_expired){
                $risk_medium += 1;
                $whois_info = "<font size=\"3\"><b>WHOIS Record - </b></font><font size=\"3\" color=\"#FFE04F\"><b>Medium</b></font></br>".$whois_info;
                $whois_info .= "<div style=\"margin-left: 20px; padding: 10px; border-left: 4px solid #FFE04F\">
                    <b>Risk Level:</b> Medium<br/>
                    Information: Expired domain addresses may be re-ordered by other people. This causes all traffic from the domain to be directed to the website of the new domain owner.<br/>
                    Retrieved from: <a href=\"https://mxtoolbox.com/Whois.aspx\" target=\"_blank\">https://mxtoolbox.com/Whois.aspx</a><br/>
                </div><br/>";
            }
            else if($has_registrant){
                $risk_medium += 1;
                $audit_data['whois_registrant'] = $has_registrant;
                $audit_data['whois_domain_email'] = $domain_email;
                $audit_data['whois_domain_owner'] = $domain_owner;
                $whois_info = "<font size=\"3\"><b>WHOIS Record - </b></font><font size=\"3\" color=\"#FFE04F\"><b>Medium</b></font></br>".$whois_info;
                $whois_info .= "<div style=\"margin-left: 20px; padding: 10px; border-left: 4px solid #FFE04F\">
                    <b>Risk Level:</b> Medium<br/>
                    Information: Registrant domain information or domain owners may be misused by an attacker such as spam to email the domain owner, even with Social Enginering techniques the attacker can pretend to be the domain owner and ask the domain registrar to change the domain server's name.<br/>
                    Retrieved from: <a href=\"https://mxtoolbox.com/Whois.aspx\" target=\"_blank\">https://mxtoolbox.com/Whois.aspx</a><br/>
                </div><br/>";
            }
            else if($whois_response){
                $risk_info += 1;
                $whois_info = "<font size=\"3\"><b>WHOIS Record - </b></font><font size=\"3\" color=\"#3B8AD5\"><b>Informational</b></font></br>".$whois_info;
                $whois_info .= "<div style=\"margin-left: 20px; padding: 10px; border-left: 4px solid #3B8AD5\">
                    <b>Risk Level:</b> Informational<br/>
                    Retrieved from: <a href=\"https://mxtoolbox.com/Whois.aspx\" target=\"_blank\">https://mxtoolbox.com/Whois.aspx</a><br/>
                </div><br/>";
            }
            else{
                $whois_info = "<font size=\"3\"><b>WHOIS Record: No Results Found</b></font></br>";
            }
        } catch (Exception $e) {
            report($e);
            $whois_info = "<font size=\"3\"><b>WHOIS Record: No Results Found</b></font></br>";
        }
        $audit_data['whois_info'] = $whois_info;
        
        
        //Parsing data openresolver
        $openresolver_info = "";
        $has_openresolver = false;
        $openresolver_response = false;
        $audit_data['openresolver_vuln'] = false;
        try {
            if($openresolver_result->getStatus() == '200'){
                $openresolver_response = true;
                $data = explode('<h2 style=', $openresolver_result->getContent());
                $data = isset($data[1]) ? '<h2 style='.$data[1] : '';
                $data = !str_contains($data, '</p>') ? $data.'</p>' : $data;
                $data = str_replace('h2', 'span', $data);
                $has_openresolver = !str_contains($data, 'not vulnerable');
                $statusopenresolver = $has_openresolver ? '<img src="https://mxtoolbox.com/public/images/statusicons/problem.png" width="17">' : '<img src="https://mxtoolbox.com/public/images/statusicons/ok.png" width="17">';
                $data = explode('<p>', $data);
                $openresolvername = isset($data[0]) ? $data[0] : '';
                $openresolvername = str_replace(" style='color:green'", '', str_replace(" style='color:red'", '', $openresolvername));
                $openresolverdetail = isset($data[1]) ? '<p>'.$data[1] : '';
                $openresolver_info = "<div class=\"table-responsive\">
                <table class=\"table table-striped table-bordered table-condensed tool-result-table\">
                  <thead>
                     <tr>
                        <th>Status</th>
                        <th>Name</th>
                        <th>Details</th>
                     </tr>
                  </thead>
                  <tbody>
                     <tr>
                        <td>$statusopenresolver</td>
                        <td>$openresolvername</td>
                        <td>$openresolverdetail</td>
                     </tr>
                  </tbody>
                </table>
                </div><br/>";
            }
            if($has_openresolver){
                $risk_high += 1;
                $audit_data['openresolver_vuln'] = true;
                $openresolver_info = "<font size=\"3\"><b>Open DNS Resolver - </b></font><font size=\"3\" color=\"#F45C51\"><b>High</b></font></br>".$openresolver_info;
                $openresolver_info .= "<div style=\"margin-left: 20px; padding: 10px; border-left: 4px solid #F45C51\">
                    <b>Risk Level:</b> High<br/>
                    Information: Open DNS Resolver detected on the website indicates the website is vulnerable to DDoS attacks or DNS Aplifikaction Attack.<br/>
                    More Information: <a target='_blank' href='https://www.us-cert.gov/ncas/alerts/TA13-088A'>https://www.us-cert.gov/ncas/alerts/TA13-088A</a><br/>
                    Retrieved from: <a href=\"http://openresolver.com/\" target=\"_blank\">http://openresolver.com/</a><br/>
                </div><br/>";
            }
            else if($openresolver_response){
                $risk_info += 1;
                $openresolver_info = "<font size=\"3\"><b>Open DNS Resolver - </b></font><font size=\"3\" color=\"#3B8AD5\"><b>Informational</b></font></br>".$openresolver_info;
                $openresolver_info .= "<div style=\"margin-left: 20px; padding: 10px; border-left: 4px solid #3B8AD5\">
                    <b>Risk Level:</b> Informational<br/>
                    Retrieved from: <a href=\"http://openresolver.com/\" target=\"_blank\">http://openresolver.com/</a><br/>
                </div><br/>";
            }
            else{
                $openresolver_info = "<font size=\"3\"><b>Open DNS Resolver: No Results Found</b></font></br>";
            }
        } catch (Exception $e) {
            report($e);
            $openresolver_info = "<font size=\"3\"><b>Open DNS Resolver: No Results Found</b></font></br>";
        }
        $audit_data['openresolver_info'] = $openresolver_info;


        //Parsing data mx
        $mx_info = "";
        $mx_record = false;
        $mx_response = false;
        try {
            if($mx_result->getStatus() == '200'){
                $mx_response = true;
                $decode = json_decode($mx_result->getContent(), true);
                $decode = json_decode($decode["d"], true);
                $crawler = new Crawler($decode["HTML_Value"]);
                $crawler->filter('.ab-show-asn-link')->each(function (Crawler $crawler) {
                    foreach ($crawler as $node) {
                        $node->parentNode->removeChild($node);
                    }
                });
                $mx_table = $crawler->filter('.tool-result-body .tool-result-body')->html();
                if($mx_table){
                    $mx_record = true;
                    $crawler->filter('.tool-result-body > table')->first()->filter('thead > tr > th')->eq(4)->each(function (Crawler $crawler) {
                        foreach ($crawler as $node) {
                            $node->parentNode->removeChild($node);
                        }
                    });
                    $crawler->filter('.tool-result-body > table')->first()->filter('tbody > tr')->each(function ($node){
                        $node->filter('td')->eq(4)->each(function (Crawler $crawler) {
                            foreach ($crawler as $node) {
                                $node->parentNode->removeChild($node);
                            }
                        });
                    });
                    $crawler->filter('.tool-result-body > table')->last()->filter('thead > tr > th')->eq(3)->each(function (Crawler $crawler) {
                        foreach ($crawler as $node) {
                            $node->parentNode->removeChild($node);
                        }
                    });
                    $crawler->filter('.tool-result-body > table')->last()->filter('tbody > tr')->each(function ($node){
                        $node->filter('td')->eq(3)->each(function (Crawler $crawler) {
                            foreach ($crawler as $node) {
                                $node->parentNode->removeChild($node);
                            }
                        });
                    });
                    $mx_table = $crawler->filter('.tool-result-body > .tool-result-body')->html();
                    $tb_status = $crawler->filter('.tool-result-body > table')->last()->html();
                    $mx_info = "<div class=\"tool-result-body\">
                    <div class=\"table-responsive\">
                    $mx_table
                    </div>
                    <div class=\"table-responsive\">
                    <table class=\"table table-striped table-bordered table-condensed tool-result-table\">
                    $tb_status
                    </table>
                    </div>
                    </div><br/>";
                }
                else{
                    $crawler->filter('.tool-result-body > table')->last()->filter('thead > tr > th')->eq(3)->each(function (Crawler $crawler) {
                        foreach ($crawler as $node) {
                            $node->parentNode->removeChild($node);
                        }
                    });
                    $crawler->filter('.tool-result-body > table')->last()->filter('tbody > tr')->each(function ($node){
                        $node->filter('td')->eq(3)->each(function (Crawler $crawler) {
                            foreach ($crawler as $node) {
                                $node->parentNode->removeChild($node);
                            }
                        });
                    });
                    $tb_status = $crawler->filter('.tool-result-body table')->html();
                    $mx_info = "<div class=\"tool-result-body\">
                    <div class=\"table-responsive\"></div>
                    <div class=\"table-responsive\">
                    <table class=\"table table-striped table-bordered table-condensed tool-result-table\">
                    $tb_status
                    </table>
                    </div>
                    </div><br/>";
                }
                
            }
            if($mx_response || $mx_record){
                $risk_info += 1;
                $mx_info = "<font size=\"3\"><b>MX Record - </b></font><font size=\"3\" color=\"#3B8AD5\"><b>Informational</b></font></br>".$mx_info;
                $mx_info .= "<div style=\"margin-left: 20px; padding: 10px; border-left: 4px solid #3B8AD5\">
                    <b>Risk Level:</b> Informational<br/>
                    Retrieved from: <a href=\"https://mxtoolbox.com/\" target=\"_blank\">https://mxtoolbox.com/</a><br/>
                </div><br/>";
            }
            else{
                $mx_info = "<font size=\"3\"><b>MX Record: No Results Found</b></font></br>";
            }
        } catch (Exception $e) {
            report($e);
            $mx_info = "<font size=\"3\"><b>MX Record: No Results Found</b></font></br>";
        }
        $audit_data['mx_info'] = $mx_info;


        //Parsing data smtp
        $smtp_info = "";
        $smtp_record = false;
        $has_openrelay = false;
        $smtp_warning = false;
        $smtp_response = false;
        $audit_data['smtp_openrelay'] = false;
        try {
            if($smtp_result->getStatus() == '200'){
                $smtp_response = true;
                $decode = json_decode($smtp_result->getContent(), true);
                $decode = json_decode($decode["d"], true);
                $crawler = new Crawler($decode["HTML_Value"]);
                $crawler->filter('.tool-result-body > .tool-result-body > h3')->each(function (Crawler $crawler) {
                    foreach ($crawler as $node) {
                        $node->parentNode->removeChild($node);
                    }
                });
                $crawler->filter('.tool-result-body > .tool-result-body > table > thead > tr > th')->eq(3)->each(function (Crawler $crawler) {
                    foreach ($crawler as $node) {
                        $node->parentNode->removeChild($node);
                    }
                });

                $crawler->filter('.tool-result-body > .tool-result-body > table > tbody > tr')->each(function (Crawler $crawler) {
                    $crawler->filter('td')->eq(3)->each(function (Crawler $crawler) {
                        foreach ($crawler as $node) {
                            $node->parentNode->removeChild($node);
                        }
                    });
                });
                $smtp_table = $crawler->filter('.tool-result-body .tool-result-body')->html();
                $smtp_table = str_replace('<td colspan="4">', '<td colspan="3">', $smtp_table);
                $smtp_record = !str_contains($smtp_table, 'Failed To Connect');
                $has_openrelay = !str_contains($smtp_table, 'Not an open relay');
                $smtp_warning = str_contains($smtp_table, 'Not good! on Transaction Time');
                $smtp_info = "<div class=\"tool-result-body\">
                <div class=\"table-responsive\">
                $smtp_table
                </div>
                </div><br/>";
            }
            if($smtp_record && $has_openrelay){
                $risk_high += 1;
                $audit_data['smtp_openrelay'] = true;
                $smtp_info = "<font size=\"3\"><b>SMTP Server Test - </b></font><font size=\"3\" color=\"#F45C51\"><b>High</b></font></br>".$smtp_info;
                $smtp_info .= "<div style=\"margin-left: 20px; padding: 10px; border-left: 4px solid #F45C51\">
                    <b>Risk Level:</b> High<br/>
                    Information: SMTP Open Relay indicates SMTP server can be used by anyone without needing credential login. It can be misused by an attacker to do email spoofing or spam email.<br/>
                    Retrieved from: <a href=\"https://mxtoolbox.com/diagnostic.aspx\" target=\"_blank\">https://mxtoolbox.com/diagnostic.aspx</a><br/>
                </div><br/>";
            }
            else if($smtp_warning){
                $risk_low += 1;
                $smtp_info = "<font size=\"3\"><b>SMTP Server Test - </b></font><font size=\"3\" color=\"#5ECA62\"><b>Low</b></font></br>".$smtp_info;
                $smtp_info .= "<div style=\"margin-left: 20px; padding: 10px; border-left: 4px solid #5ECA62\">
                    <b>Risk Level:</b> Low<br/>
                    Information: Slow SMTP Transaction Time indicates SMTP Server is not working optimally but in operation it is still working fine.<br/>
                    Retrieved from: <a href=\"https://mxtoolbox.com/diagnostic.aspx\" target=\"_blank\">https://mxtoolbox.com/diagnostic.aspx</a><br/>
                </div><br/>";
            }
            else if($smtp_response){
                $risk_info += 1;
                $smtp_info = "<font size=\"3\"><b>SMTP Server Test - </b></font><font size=\"3\" color=\"#3B8AD5\"><b>Informational</b></font></br>".$smtp_info;
                $smtp_info .= "<div style=\"margin-left: 20px; padding: 10px; border-left: 4px solid #3B8AD5\">
                    <b>Risk Level:</b> Informational<br/>
                    Retrieved from: <a href=\"https://mxtoolbox.com/diagnostic.aspx\" target=\"_blank\">https://mxtoolbox.com/diagnostic.aspx</a><br/>
                </div><br/>";
            }
            else{
                $smtp_info = "<font size=\"3\"><b>SMTP Server Test: No Results Found</b></font></br>";
            }
        } catch (Exception $e) {
            report($e);
            $smtp_info = "<font size=\"3\"><b>SMTP Server Test: No Results Found</b></font></br>";
        }
        $audit_data['smtp_info'] = $smtp_info;


        //Parsing data dmarc
        $dmarc_info = "";
        $dmarc_record = false;
        $dmarc_response = false;
        $audit_data['dmarc_needed'] = false;
        try {
            if($dmarc_result->getStatus() == '200'){
                $dmarc_response = true;
                $decode = json_decode($dmarc_result->getContent(), true);
                $decode = json_decode($decode["d"], true);
                $crawler = new Crawler($decode["HTML_Value"]);
                $dmarc_table = $crawler->filter('.tool-result-body .tool-result-body')->html();
                if($dmarc_table){
                    $dmarc_record = true;
                    $tb_status = $crawler->filter('.tool-result-body table')->last()->html();
                    $alert_info = $crawler->filter('.tool-result-body > div > .alert.alert-success')->html();
                    $dmarc_info = "<div>
                    <pre class=\"alert\">$alert_info</pre>
                    </div>
                    <div class=\"tool-result-body\">
                    <div class=\"table-responsive\">
                    $dmarc_table
                    </div>
                    <div class=\"table-responsive\">
                    <table class=\"table table-striped table-bordered table-condensed tool-result-table\">
                    $tb_status
                    </table>
                    </div>
                    </div><br/>";
                }
                else{
                    $crawler->filter('.tool-result-body > table')->last()->filter('thead > tr > th')->eq(3)->each(function (Crawler $crawler) {
                        foreach ($crawler as $node) {
                            $node->parentNode->removeChild($node);
                        }
                    });
                    $crawler->filter('.tool-result-body > table')->last()->filter('tbody > tr')->each(function ($node){
                        $node->filter('td')->eq(3)->each(function (Crawler $crawler) {
                            foreach ($crawler as $node) {
                                $node->parentNode->removeChild($node);
                            }
                        });
                    });
                    $tb_status = $crawler->filter('.tool-result-body table')->html();
                    $dmarc_info = "<div class=\"tool-result-body\">
                    <div class=\"table-responsive\"></div>
                    <div class=\"table-responsive\">
                    <table class=\"table table-striped table-bordered table-condensed tool-result-table\">
                    $tb_status
                    </table>
                    </div>
                    </div><br/>";
                }
                
            }
        } catch (Exception $e) {
            report($e);
            $dmarc_info = "<font size=\"3\"><b>DMARC Record: No Results Found</b></font></br>";
        }
        $audit_data['dmarc_info'] = $dmarc_info;


        //Parsing data spf
        $spf_info = "<font size=\"3\"><b>SPF Record: No Results Found</b></font></br>";
        $spf_record = false;
        $spf_response = false;
        $audit_data['spf_needed'] = false;
        try {
            if($spf_result->getStatus() == '200'){
                $spf_response = true;
                $decode = json_decode($spf_result->getContent(), true);
                $decode = json_decode($decode["d"], true);
                $crawler = new Crawler($decode["HTML_Value"]);
                $spf_table = $crawler->filter('.tool-result-body .tool-result-body')->html();
                if($spf_table){
                    $spf_record = true;
                    $tb_status = $crawler->filter('.tool-result-body table')->last()->html();
                    $alert_info = $crawler->filter('.tool-result-body > div > .alert.alert-success')->html();
                    $spf_info = "<div>
                    <pre class=\"alert\">$alert_info</pre>
                    </div>
                    <div class=\"tool-result-body\">
                    <div class=\"table-responsive\">
                    $spf_table
                    </div>
                    <div class=\"table-responsive\">
                    <table class=\"table table-striped table-bordered table-condensed tool-result-table\">
                    $tb_status
                    </table>
                    </div>
                    </div><br/>";
                }
                else{
                    $crawler->filter('.tool-result-body > table')->last()->filter('thead > tr > th')->eq(3)->each(function (Crawler $crawler) {
                        foreach ($crawler as $node) {
                            $node->parentNode->removeChild($node);
                        }
                    });
                    $crawler->filter('.tool-result-body > table')->last()->filter('tbody > tr')->each(function ($node){
                        $node->filter('td')->eq(3)->each(function (Crawler $crawler) {
                            foreach ($crawler as $node) {
                                $node->parentNode->removeChild($node);
                            }
                        });
                    });
                    $tb_status = $crawler->filter('.tool-result-body table')->html();
                    $spf_info = "<div class=\"tool-result-body\">
                    <div class=\"table-responsive\"></div>
                    <div class=\"table-responsive\">
                    <table class=\"table table-striped table-bordered table-condensed tool-result-table\">
                    $tb_status
                    </table>
                    </div>
                    </div><br/>";
                }
                
            }
            if($mx_record && (!$spf_record)){
                $risk_medium += 1;
                $audit_data['spf_needed'] = true;
                $spf_info = "<font size=\"3\"><b>SPF Record - </b></font><font size=\"3\" color=\"#FFE04F\"><b>Medium</b></font></br>".$spf_info;
                $spf_info .= "<div style=\"margin-left: 20px; padding: 10px; border-left: 4px solid #FFE04F\">
                    <b>Risk Level:</b> Medium<br/>
                    Information: The undetectable SPF Record shows the website does not have email sender checking of incoming email. This can cause email services to be vulnerable to spam email.<br/>
                    Retrieved from: <a href=\"https://mxtoolbox.com/spf.aspx\" target=\"_blank\">https://mxtoolbox.com/spf.aspx</a><br/>
                </div><br/>";
                if($mx_record && (!$dmarc_record)){
                    $risk_medium += 1;
                    $dmarc_response = false;
                    $audit_data['dmarc_needed'] = true;
                    $dmarc_info = "<font size=\"3\"><b>DMARC Record - </b></font><font size=\"3\" color=\"#FFE04F\"><b>Medium</b></font></br>".$dmarc_info;
                    $dmarc_info .= "<div style=\"margin-left: 20px; padding: 10px; border-left: 4px solid #FFE04F\">
                        <b>Risk Level:</b> Medium<br/>
                        Information: The undetectable DMARC Record shows the website has no email handling that is indicated as spam.<br/>
                        Retrieved from: <a href=\"https://mxtoolbox.com/dmarc.aspx\" target=\"_blank\">https://mxtoolbox.com/dmarc.aspx</a><br/>
                    </div><br/>";
                    $audit_data['dmarc_info'] = $dmarc_info;
                }
            }
            else if($spf_response){
                $risk_info += 1;
                $spf_info = "<font size=\"3\"><b>SPF Record - </b></font><font size=\"3\" color=\"#3B8AD5\"><b>Informational</b></font></br>".$spf_info;
                $spf_info .= "<div style=\"margin-left: 20px; padding: 10px; border-left: 4px solid #3B8AD5\">
                    <b>Risk Level:</b> Informational<br/>
                    Retrieved from: <a href=\"https://mxtoolbox.com/spf.aspx\" target=\"_blank\">https://mxtoolbox.com/spf.aspx</a><br/>
                </div><br/>";
            }
            else{
                $spf_info = "<font size=\"3\"><b>SPF Record: No Results Found</b></font></br>";
            }

            if($dmarc_response){
                $risk_info += 1;
                $dmarc_info = "<font size=\"3\"><b>DMARC Record - </b></font><font size=\"3\" color=\"#3B8AD5\"><b>Informational</b></font></br>".$dmarc_info;
                $dmarc_info .= "<div style=\"margin-left: 20px; padding: 10px; border-left: 4px solid #3B8AD5\">
                    <b>Risk Level:</b> Informational<br/>
                    Retrieved from: <a href=\"https://mxtoolbox.com/dmarc.aspx\" target=\"_blank\">https://mxtoolbox.com/dmarc.aspx</a><br/>
                </div><br/>";
            }
            else if(!$audit_data['dmarc_needed']){
                $dmarc_info = "<font size=\"3\"><b>DMARC Record: No Results Found</b></font></br>";
            }
        } catch (Exception $e) {
            report($e);
            $spf_info = "<font size=\"3\"><b>SPF Record: No Results Found</b></font></br>";
        }
        $audit_data['spf_info'] = $spf_info;

        $audit_data['risk_info'] = $risk_info;
        $audit_data['risk_low'] = $risk_low;
        $audit_data['risk_medium'] = $risk_medium;
        $audit_data['risk_high'] = $risk_high;
        $audit_data['created_at'] = now();

        //save to database
        $audit_results = AuditResult::create([
            'user_id' => Auth::user()->id,
            'web_domain' => $audit_data['web_domain'],
            'host_ip' => $audit_data['host_ip'],
            'asn_info' => $audit_data['asn_info'],
            'ssl_info' => $audit_data['ssl_info'],
            'ssl_expired' => $audit_data['ssl_expired'],
            'ssl_heartbleed' => $audit_data['ssl_heartbleed'],
            'dns_info' => $audit_data['dns_info'],
            'cname_info' => $audit_data['cname_info'],
            'txt_info' => $audit_data['txt_info'],
            'whois_info' => $audit_data['whois_info'],
            'whois_registrant' => $audit_data['whois_registrant'],
            'whois_domain_owner' => $audit_data['whois_domain_owner'],
            'whois_domain_email' => $audit_data['whois_domain_email'],
            'openresolver_info' => $audit_data['openresolver_info'],
            'openresolver_vuln' => $audit_data['openresolver_vuln'],
            'mx_info' => $audit_data['mx_info'],
            'smtp_info' => $audit_data['smtp_info'],
            'smtp_openrelay' => $audit_data['smtp_openrelay'],
            'dmarc_info' => $audit_data['dmarc_info'],
            'dmarc_needed' => $audit_data['dmarc_needed'],
            'spf_info' => $audit_data['spf_info'],
            'spf_needed' => $audit_data['spf_needed'],
            'risk_info' => $audit_data['risk_info'],
            'risk_low' => $audit_data['risk_low'],
            'risk_medium' => $audit_data['risk_medium'],
            'risk_high' => $audit_data['risk_high'],
            'created_at' => $audit_data['created_at']
        ]);

        $token_generated = (new DownloadController)->generate_token($audit_results);
        $sendEmail = Auth::user()->setting->sendmail;
        $isSendOwner = (!empty($audit_data['whois_domain_email']) && $audit_data['risk_high'] > 0) ? true : false;
        if($token_generated && $sendEmail){
            event(new AuditResultCreated($audit_results, $isSendOwner));
        }

        //show result
        return view('result', compact('audit_results'));
    }
}
