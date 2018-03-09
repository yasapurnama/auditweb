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
        $this->middleware('auth');
        $this->guzzleConfig = [
            'timeout' => 60,
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
     * Show the selected history.
     *
     * @return \Illuminate\Http\Response
     */
    public function show($result)
    {
        $audit_results = Auth::user()->audit_result()->findOrFail($result);
        return view('result', compact('audit_results'));
    }

    /**
     * Show the application history.
     *
     * @return \Illuminate\Http\Response
     */
    public function history()
    {
        $auditResults = Auth::user()->audit_result()->latest()->paginate(6);
        return view('history', compact('auditResults'));
    }

    /**
     * Delete history data.
     *
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request)
    {
        $validatedData = $request->validate([
            'data_id' => 'required|numeric'
        ]);

        $data_id = request('data_id');
        $result = Auth::user()->audit_result()->find($data_id);
        $result->delete();

        return redirect()->route('history');
    }

    /**
     * Start web scraping.
     *
     * @return \Illuminate\Http\Response
     */
    public function scan(Request $request)
    {
        $validatedData = $request->validate([
            'domain' => 'required|regex:/^([a-z\d](-*[a-z\d])*)(\.([a-z\d](-*[a-z\d])*))*$/i|regex:/^.{1,253}$/|regex:/^[^\.]{1,63}(\.[^\.]{1,63})*$/'
        ]);
        
        $domain = request('domain');
        $hostip = gethostbyname($domain);
        $saverity_info = 1;
        $saverity_low = 0;
        $saverity_medium = 0;
        $saverity_high = 0;
        $audit_data = [];
        $audit_data['web_domain'] = $domain;
        $audit_data['host_ip'] = $hostip;


        //asyncRequest
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

        $promises = [
            'asn' => $client->getAsync('https://www.ultratools.com/tools/asnInfoResult?domainName='.$hostip, ['headers' => $header_asn]),
            'ssl' => $client->postAsync('https://www.digicert.com/api/check-host.php', ['headers' => $header_digicert, 'body' => "r=".rand(0, 1000)."&host=$domain&order_id="]),
            'heartbleed' => $client->postAsync('https://www.digicert.com/api/check-vuln.php', ['headers' => $header_digicert, 'body' => "r=".rand(0, 1000)."&host=$domain&order_id="]),
            'port' => $client->postAsync('https://mxtoolbox.com/Public/Lookup.aspx/DoLookup2', ['headers' => $header_mx, 'body' => "{\"inputText\":\"scan:$domain\",\"resultIndex\":1}"]),
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
        $port_result = new Response((string) $results['port']['value']->getBody(), $results['port']['value']->getStatusCode(), $results['port']['value']->getHeaders());
        $dns_result = new Response((string) $results['dns']['value']->getBody(), $results['dns']['value']->getStatusCode(), $results['dns']['value']->getHeaders());
        $cname_result = new Response((string) $results['cname']['value']->getBody(), $results['cname']['value']->getStatusCode(), $results['cname']['value']->getHeaders());
        $txt_result = new Response((string) $results['txt']['value']->getBody(), $results['txt']['value']->getStatusCode(), $results['txt']['value']->getHeaders());
        $whois_result = new Response((string) $results['whois']['value']->getBody(), $results['whois']['value']->getStatusCode(), $results['whois']['value']->getHeaders());
        $openresolver_result = new Response((string) $results['openresolver']['value']->getBody(), $results['openresolver']['value']->getStatusCode(), $results['openresolver']['value']->getHeaders());
        $mx_result = new Response((string) $results['mx']['value']->getBody(), $results['mx']['value']->getStatusCode(), $results['mx']['value']->getHeaders());
        $smtp_result = new Response((string) $results['smtp']['value']->getBody(), $results['smtp']['value']->getStatusCode(), $results['smtp']['value']->getHeaders());
        $dmarc_result = new Response((string) $results['dmarc']['value']->getBody(), $results['dmarc']['value']->getStatusCode(), $results['dmarc']['value']->getHeaders());
        $spf_result = new Response((string) $results['spf']['value']->getBody(), $results['spf']['value']->getStatusCode(), $results['spf']['value']->getHeaders());

        
        //Parsing data asn
        $asn_info = "<font size=\"3\"><b>ASN Information: Undefined</b></font></br>";
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
            $asn_info = "<font size=\"3\"><b>ASN Information: Undefined</b></font></br>";
        }
        $audit_data['asn_info'] = $asn_info;
        
        
        //Parsing data ssl
        $ssl_info = "";
        $has_ssl = false;
        $vuln_heartbleed = false;
        $ssl_expired = false;
        $ssl_response = false;
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

                    $statustrust = $result->filter('h2')->eq(5)->attr('class');
                    switch ($statustrust) {
                        case 'ok':
                            $statustrust = '<img src="https://mxtoolbox.com/public/images/statusicons/ok.png" width="17">';
                            break;
                        case 'warning':
                            $statustrust = '<img src="https://mxtoolbox.com/public/images/statusicons/warning.png" width="17">';
                            break;
                        case 'error':
                            $statustrust = '<img src="https://mxtoolbox.com/public/images/statusicons/problem.png" width="17">';
                            break;
                        default:
                            $statustrust = '';
                    }
                    $trustname = $result->filter('h2')->last()->html();
                    $trustdetail = $result->filter('p')->last()->html();

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

                    $ssl_info .= "</tbody>
                    </table>
                    </div>
                    </div><br/>";
                }
                
            }
            $audit_data['ssl_heartbleed'] = false;
            $audit_data['ssl_expired'] = false;
            if($vuln_heartbleed){
                $saverity_high += 1;
                $audit_data['ssl_heartbleed'] = true;
                $ssl_info = "<font size=\"3\"><b>SSL Certificate - </b></font><font size=\"3\" color=\"#F45C51\"><b>High</b></font></br>".$ssl_info;
            }
            else if($ssl_expired){
                $saverity_medium += 1;
                $audit_data['ssl_expired'] = true;
                $ssl_info = "<font size=\"3\"><b>SSL Certificate - </b></font><font size=\"3\" color=\"#FFE04F\"><b>Medium</b></font></br>".$ssl_info;
            }
            else if($has_ssl){
                $saverity_info += 1;
                $ssl_info = "<font size=\"3\"><b>SSL Certificate - </b></font><font size=\"3\" color=\"#3B8AD5\"><b>Information</b></font></br>".$ssl_info;
            }
            else if($ssl_response){
                $saverity_medium += 1;
                $ssl_info = "<font size=\"3\"><b>SSL Certificate - </b></font><font size=\"3\" color=\"#FFE04F\"><b>Medium</b></font></br>".$ssl_info;
            }
            else{
                $ssl_info = "<font size=\"3\"><b>SSL Certificate: Undefined</b></font></br>";
            }
        } catch (Exception $e) {
            report($e);
            $ssl_info = "<font size=\"3\"><b>SSL Certificate: Undefined</b></font></br>";
        }
        $audit_data['ssl_info'] = $ssl_info;


        //Parsing data port
        $port_info = "<font size=\"3\"><b>Open Ports and Services: Undefined</b></font></br>";
        try {
            if($port_result->getStatus() == '200'){
                $saverity_info += 1;
                $decode = json_decode($port_result->getContent(), true);
                $decode = json_decode($decode["d"], true);
                $crawler = new Crawler($decode["HTML_Value"]);
                $port_table = $crawler->filter('.tool-result-body .tool-result-body')->html();
                $port_info = "<font size=\"3\"><b>Open Ports and Services - </b></font><font size=\"3\" color=\"#3B8AD5\"><b>Information</b></font></br>
                <div class=\"tool-result-body\">
                <div class=\"table-responsive\">
                $port_table
                </div>
                </div><br/>";
            }
        } catch (Exception $e) {
            report($e);
            $port_info = "<font size=\"3\"><b>Open Ports and Services: Undefined</b></font></br>";
        }
        $audit_data['port_info'] = $port_info;


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
                $saverity_low += 1;
                $dns_info = "<font size=\"3\"><b>DNS Server - </b></font><font size=\"3\" color=\"#5ECA62\"><b>Low</b></font></br>".$dns_info;
            }
            else if($has_problem){
                $saverity_high += 1;
                $dns_info = "<font size=\"3\"><b>DNS Server - </b></font><font size=\"3\" color=\"#F45C51\"><b>High</b></font></br>".$dns_info;
            }
            else if($dns_response){
                $saverity_info += 1;
                $dns_info = "<font size=\"3\"><b>DNS Server - </b></font><font size=\"3\" color=\"#3B8AD5\"><b>Information</b></font></br>".$dns_info;
            }
            else{
                $dns_info = "<font size=\"3\"><b>DNS Server: Undefined</b></font></br>";
            }
        } catch (Exception $e) {
            report($e);
            $dns_info = "<font size=\"3\"><b>DNS Server: Undefined</b></font></br>";
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
                $saverity_info += 1;
                $cname_info = "<font size=\"3\"><b>CNAME Record - </b></font><font size=\"3\" color=\"#3B8AD5\"><b>Information</b></font></br>".$cname_info;
            }
            else{
                $cname_info = "<font size=\"3\"><b>CNAME Record: Undefined</b></font></br>";
            }
        } catch (Exception $e) {
            report($e);
            $cname_info = "<font size=\"3\"><b>CNAME Record: Undefined</b></font></br>";
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
            if($txt_record){
                $saverity_info += 1;
                $txt_info = "<font size=\"3\"><b>TXT Record - </b></font><font size=\"3\" color=\"#3B8AD5\"><b>Information</b></font></br>".$txt_info;
            }
            else if($txt_response){
                $saverity_low += 1;
                $txt_info = "<font size=\"3\"><b>TXT Record - </b></font><font size=\"3\" color=\"#5ECA62\"><b>Low</b></font></br>".$txt_info;
            }
            else{
                $txt_info = "<font size=\"3\"><b>TXT Record: Undefined</b></font></br>";
            }
        } catch (Exception $e) {
            report($e);
            $txt_info = "<font size=\"3\"><b>TXT Record: Undefined</b></font></br>";
        }
        $audit_data['txt_info'] = $txt_info;
        

        //Parsing data whois
        $whois_info = "";
        $domain_email = '';
        $domain_owner = '';
        $has_registrant = false;
        $whois_response = false;
        try {
            if($whois_result->getStatus() == '200'){
                $whois_response = true;
                $decode = json_decode($whois_result->getContent(), true);
                $decode = json_decode($decode["d"], true);
                $crawler = new Crawler($decode["HTML_Value"]);
                $data = $crawler->filter('.tool-result-body > table')->eq(0)->filter('tbody > tr')->each(function ($node, $i) {
                    if($node->filter('td')->text()=='Registrant Email'){
                        return $node->parents()->filter('tr')->eq($i)->filter('td')->eq(1)->text();
                    }
                });
                
                if(isset($data[4]) && filter_var(trim($data[4]), FILTER_VALIDATE_EMAIL)){
                    $domain_email = $data[4];
                    $has_registrant = true;
                }
                $data = $crawler->filter('.tool-result-body > table')->eq(0)->filter('tbody > tr')->each(function ($node, $i) {
                    if($node->filter('td')->text()=='Registrant Name'){
                        return $node->parents()->filter('tr')->eq($i)->filter('td')->eq(1)->text();
                    }
                });
                if(isset($data[1])){
                    $domain_owner = $data[1];
                }
                $crawler->filter('.tool-result-body .tool-result-body div')->each(function (Crawler $crawler) {
                    foreach ($crawler as $node) {
                        $node->parentNode->removeChild($node);
                    }
                });
                $whois_table = $crawler->filter('.tool-result-body .tool-result-body')->html();
                $whois_info = "<div class=\"tool-result-body\">
                <div class=\"table-responsive\">
                $whois_table
                </div>
                </div><br/>";
            }
            $audit_data['whois_registrant'] = false;
            $audit_data['whois_domain_email'] = '';
            $audit_data['whois_domain_owner'] = '';
            if($has_registrant){
                $saverity_medium += 1;
                $audit_data['whois_registrant'] = $has_registrant;
                $audit_data['whois_domain_email'] = $domain_email;
                $audit_data['whois_domain_owner'] = $domain_owner;
                $whois_info = "<font size=\"3\"><b>WHOIS Record - </b></font><font size=\"3\" color=\"#FFE04F\"><b>Medium</b></font></br>".$whois_info;
            }
            else if($whois_response){
                $saverity_info += 1;
                $whois_info = "<font size=\"3\"><b>WHOIS Record - </b></font><font size=\"3\" color=\"#3B8AD5\"><b>Information</b></font></br>".$whois_info;
            }
            else{
                $whois_info = "<font size=\"3\"><b>WHOIS Record: Undefined</b></font></br>";
            }
        } catch (Exception $e) {
            report($e);
            $whois_info = "<font size=\"3\"><b>WHOIS Record: Undefined</b></font></br>";
        }
        $audit_data['whois_info'] = $whois_info;
        
        
        //Parsing data openresolver
        $openresolver_info = "";
        $has_openresolver = false;
        $openresolver_response = false;
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
                if($has_openresolver){
                    $openresolver_info .= "More Info: <a target='_blank' href='https://www.us-cert.gov/ncas/alerts/TA13-088A'>https://www.us-cert.gov/ncas/alerts/TA13-088A</a><br/><br/>";
                }
            }
            $audit_data['openresolver_vuln'] = false;
            if($has_openresolver){
                $saverity_high += 1;
                $audit_data['openresolver_vuln'] = true;
                $openresolver_info = "<font size=\"3\"><b>Open DNS Resolver - </b></font><font size=\"3\" color=\"#F45C51\"><b>High</b></font></br>".$openresolver_info;
            }
            else if($openresolver_response){
                $saverity_info += 1;
                $openresolver_info = "<font size=\"3\"><b>Open DNS Resolver - </b></font><font size=\"3\" color=\"#3B8AD5\"><b>Information</b></font></br>".$openresolver_info;
            }
            else{
                $openresolver_info = "<font size=\"3\"><b>Open DNS Resolver: Undefined</b></font></br>";
            }
        } catch (Exception $e) {
            report($e);
            $openresolver_info = "<font size=\"3\"><b>Open DNS Resolver: Undefined</b></font></br>";
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
            if($mx_record){
                $saverity_info += 1;
                $mx_info = "<font size=\"3\"><b>MX Record - </b></font><font size=\"3\" color=\"#3B8AD5\"><b>Information</b></font></br>".$mx_info;
            }
            else if($mx_response){
                $saverity_low += 1;
                $mx_info = "<font size=\"3\"><b>MX Record - </b></font><font size=\"3\" color=\"#5ECA62\"><b>Low</b></font></br>".$mx_info;
            }
            else{
                $mx_info = "<font size=\"3\"><b>MX Record: Undefined</b></font></br>";
            }
        } catch (Exception $e) {
            report($e);
            $mx_info = "<font size=\"3\"><b>MX Record: Undefined</b></font></br>";
        }
        $audit_data['mx_info'] = $mx_info;


        //Parsing data smtp
        $smtp_info = "";
        $smtp_record = false;
        $has_openrelay = false;
        $smtp_warning = false;
        $smtp_response = false;
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
                $smtp_warning = str_contains($smtp_table, 'warning.png');
                $smtp_info = "<div class=\"tool-result-body\">
                <div class=\"table-responsive\">
                $smtp_table
                </div>
                </div><br/>";
                if($smtp_record && $has_openrelay){
                    $smtp_info .= "More Info: <a target='_blank' href='https://www.cvedetails.com/cve/cve-1999-0512'>https://www.cvedetails.com/cve/cve-1999-0512</a><br/><br/>";
                }
                
            }
            $audit_data['smtp_openrelay'] = false;
            if($smtp_record && $has_openrelay){
                $saverity_high += 1;
                $audit_data['smtp_openrelay'] = true;
                $smtp_info = "<font size=\"3\"><b>SMTP Server Test - </b></font><font size=\"3\" color=\"#F45C51\"><b>High</b></font></br>".$smtp_info;
            }
            else if($smtp_warning){
                $saverity_low += 1;
                $smtp_info = "<font size=\"3\"><b>SMTP Server Test - </b></font><font size=\"3\" color=\"#5ECA62\"><b>Low</b></font></br>".$smtp_info;
            }
            else if($smtp_response){
                $saverity_info += 1;
                $smtp_info = "<font size=\"3\"><b>SMTP Server Test - </b></font><font size=\"3\" color=\"#3B8AD5\"><b>Information</b></font></br>".$smtp_info;
            }
            else{
                $smtp_info = "<font size=\"3\"><b>SMTP Server Test: Undefined</b></font></br>";
            }
        } catch (Exception $e) {
            report($e);
            $smtp_info = "<font size=\"3\"><b>SMTP Server Test: Undefined</b></font></br>";
        }
        $audit_data['smtp_info'] = $smtp_info;


        //Parsing data dmarc
        $dmarc_info = "";
        $dmarc_record = false;
        $dmarc_response = false;
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
                    </div><br/>
                    More Info: <a target='_blank' href='https://dmarc.org/overview/'>https://dmarc.org/overview/</a><br/><br/>";
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
                    </div><br/>
                    More Info: <a target='_blank' href='https://dmarc.org/overview/'>https://dmarc.org/overview/</a><br/><br/>";
                }
                
            }
            $audit_data['dmarc_needed'] = false;
            if($mx_record && (!$dmarc_record)){
                $saverity_medium += 1;
                $audit_data['dmarc_needed'] = true;
                $dmarc_info = "<font size=\"3\"><b>DMARC Record - </b></font><font size=\"3\" color=\"#FFE04F\"><b>Medium</b></font></br>".$dmarc_info;
            }
            else if($dmarc_response){
                $saverity_info += 1;
                $dmarc_info = "<font size=\"3\"><b>DMARC Record - </b></font><font size=\"3\" color=\"#3B8AD5\"><b>Information</b></font></br>".$dmarc_info;
            }
            else{
                $dmarc_info = "<font size=\"3\"><b>DMARC Record: Undefined</b></font></br>";
            }
        } catch (Exception $e) {
            report($e);
            $dmarc_info = "<font size=\"3\"><b>DMARC Record: Undefined</b></font></br>";
        }
        $audit_data['dmarc_info'] = $dmarc_info;


        //Parsing data spf
        $spf_info = "<font size=\"3\"><b>SPF Record: Undefined</b></font></br>";
        $spf_record = false;
        $spf_response = false;
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
                    </div><br/>
                    More Info: <a target='_blank' href='http://www.openspf.org/Introduction'>http://www.openspf.org/Introduction</a><br/><br/>";
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
                    </div><br/>
                    More Info: <a target='_blank' href='http://www.openspf.org/Introduction'>http://www.openspf.org/Introduction</a><br/><br/>";
                }
                
            }
            $audit_data['spf_needed'] = false;
            if($mx_record && (!$spf_record)){
                $saverity_medium += 1;
                $audit_data['spf_needed'] = true;
                $spf_info = "<font size=\"3\"><b>SPF Record - </b></font><font size=\"3\" color=\"#FFE04F\"><b>Medium</b></font></br>".$spf_info;
            }
            else if($spf_response){
                $saverity_info += 1;
                $spf_info = "<font size=\"3\"><b>SPF Record - </b></font><font size=\"3\" color=\"#3B8AD5\"><b>Information</b></font></br>".$spf_info;
            }
            else{
                $spf_info = "<font size=\"3\"><b>SPF Record: Undefined</b></font></br>";
            }
        } catch (Exception $e) {
            report($e);
            $spf_info = "<font size=\"3\"><b>SPF Record: Undefined</b></font></br>";
        }
        $audit_data['spf_info'] = $spf_info;


        //$saverity_high += 1;
        //save saverity
        $audit_data['saverity_info'] = $saverity_info;
        $audit_data['saverity_low'] = $saverity_low;
        $audit_data['saverity_medium'] = $saverity_medium;
        $audit_data['saverity_high'] = $saverity_high;
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
            'port_info' => $audit_data['port_info'],
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
            'saverity_info' => $audit_data['saverity_info'],
            'saverity_low' => $audit_data['saverity_low'],
            'saverity_medium' => $audit_data['saverity_medium'],
            'saverity_high' => $audit_data['saverity_high'],
            'created_at' => $audit_data['created_at']
        ]);

        $token_generated = (new DownloadController)->generate_token($audit_results);
        $sendEmail = Auth::user()->setting->sendmail;
        $isSendOwner = (!empty($audit_data['whois_domain_email']) && $audit_data['saverity_high'] > 0) ? true : false;
        if($token_generated && $sendEmail){
            event(new AuditResultCreated($audit_results, $isSendOwner));
        }

        //send result
        return view('result', compact('audit_results'));
    }
}
