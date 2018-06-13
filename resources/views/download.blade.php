@extends('layouts.pdf')

@section('content')
<div style="padding-left: 8px"><a href="{{ Request::root() }}"><font size="4"><b>Website Audit Result </b></font></a><small style="float:right;">{{ isset($audit_results['created_at']) ? $audit_results['created_at']->format('d M Y - H:i') : '' }}</small></div>
<div style="display:block; padding: 10px 10px 10px 10px">
Website: {{ isset($audit_results['web_domain']) ? $audit_results['web_domain'] : '' }}<br/>
Host IP: {{ isset($audit_results['host_ip']) ? $audit_results['host_ip'] : '' }}<br/><br/>
{!! isset($audit_results['asn_info']) ? $audit_results['asn_info'] : '' !!}<br/>
{!! isset($audit_results['ssl_info']) ? $audit_results['ssl_info'] : '' !!}<br/>
{!! isset($audit_results['dns_info']) ? $audit_results['dns_info'] : '' !!}<br/>
{!! isset($audit_results['cname_info']) ? $audit_results['cname_info'] : '' !!}<br/>
{!! isset($audit_results['txt_info']) ? $audit_results['txt_info'] : '' !!}<br/>
{!! isset($audit_results['whois_info']) ? $audit_results['whois_info'] : '' !!}<br/>
{!! isset($audit_results['openresolver_info']) ? $audit_results['openresolver_info'] : '' !!}<br/>
{!! isset($audit_results['mx_info']) ? $audit_results['mx_info'] : '' !!}<br/>
{!! isset($audit_results['smtp_info']) ? $audit_results['smtp_info'] : '' !!}<br/>
{!! isset($audit_results['dmarc_info']) ? $audit_results['dmarc_info'] : '' !!}<br/>
{!! isset($audit_results['spf_info']) ? $audit_results['spf_info'] : '' !!}<br/>
@if (isset($audit_results['saverity_info'], $audit_results['saverity_low'], $audit_results['saverity_medium'], $audit_results['saverity_high']))
    @php
    $severity_all = $audit_results['saverity_info']+$audit_results['saverity_low']+$audit_results['saverity_medium']+$audit_results['saverity_high'];
    @endphp

    <b>Risk Calculation:</b><br/>
    <table>
        <tr>
            <td>
                <ul>
                    <li>Informational</li>
                    <li>Low</li>
                    <li>Medium</li>
                    <li>High</li>
                </ul>
            </td>
            <td>
                : {{ $audit_results['saverity_info']." / ".$severity_all." x 100%" }}<br />
                : {{ $audit_results['saverity_low']." / ".$severity_all." x 100%" }}<br />
                : {{ $audit_results['saverity_medium']." / ".$severity_all." x 100%" }}<br />
                : {{ $audit_results['saverity_high']." / ".$severity_all." x 100%" }}<br />
            </td>
            <td>
                {{ "= ".round(($audit_results['saverity_info']/$severity_all*100))."%" }}<br />
                {{ "= ".round(($audit_results['saverity_low']/$severity_all*100))."%" }}<br />
                {{ "= ".round(($audit_results['saverity_medium']/$severity_all*100))."%" }}<br />
                {{ "= ".round(($audit_results['saverity_high']/$severity_all*100))."%" }}<br />
            </td>
        </tr>
    </table> 
@endif

<br/>
<br/>
<hr>
<p style="text-align: center;">{{ Request::root() }}</p>
</div>
@endsection
