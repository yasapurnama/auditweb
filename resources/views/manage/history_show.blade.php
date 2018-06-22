@extends('layouts.app')

@section('content')
                    <section class="section">
                        <div class="row sameheight-container">
                            <div class="col-md-12">
                                <div class="card card-default">
                                    <div class="card-header">
                                        <div class="header-block">
                                            <p class="title"> Website Audit Result </p><span class="pull-right">{{ isset($audit_results['created_at']) ? $audit_results['created_at']->format('d M Y - H:i') : '' }}</span>
                                        </div>
                                    </div>
                                    <div class="card-block" style="padding: 10px 30px 30px 30px">
<table width="100%" border="0">
   <tr>
      <td class="align-top">
         Website: {{ isset($audit_results['web_domain']) ? $audit_results['web_domain'] : '' }}</br>
         Host IP: {{ isset($audit_results['host_ip']) ? $audit_results['host_ip'] : '' }}
         <br>
      </td>
      <td rowspan="2" style="padding-bottom: 20px">
         <div class="flot-chart">
            <font size="3"><b>Risk Chart</b></font>
            <div class="flot-chart-pie-content" id="flot-pie-chart"></div>
         </div>
      </td>
   </tr>
   <tr>
      <td class="align-top"><font size="3">
         {!! isset($audit_results['asn_info']) ? $audit_results['asn_info'] : '' !!}
      </td>
   </tr>
</table>

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

                                    </div>
                                    <div class="card-footer"> 
                                        <div class="pull-left">
                                            <a href="{{ route('manage.history') }}"><i class="fa fa-history"></i> History</a> 
                                        </div>
                                        <div class="pull-right">
                                            <a class="btn btn-primary" href="{{ Request::root().'/download/'.$audit_results['id'].'?token='.$token }}">
                                                <i class="fa fa-download"></i> Download </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </section>
@endsection

@section('script')
<script type="text/javascript">
$(function() {

    function drawFlotCharts() {
        console.log("drawing flot chart");
        //Flot Pie Chart
        var data = [{
            label: "Informational",
            data: {{ isset($audit_results['risk_info']) ? $audit_results['risk_info'] : 0 }},
            color: tinycolor('rgb(59, 138, 213)'),
        }, {
            label: "Low",
            data: {{ isset($audit_results['risk_low']) ? $audit_results['risk_low'] : 0 }},
            color: tinycolor('rgb(94, 202, 98)'),
        }, {
            label: "Medium",
            data: {{ isset($audit_results['risk_medium']) ? $audit_results['risk_medium'] : 0 }},
            color: tinycolor('rgb(255, 224, 79)'),
        }, {
            label: "High",
            data: {{ isset($audit_results['risk_high']) ? $audit_results['risk_high'] : 0 }},
            color: tinycolor('rgb(244, 92, 81)'),
        }];

        var plotObj = $.plot($("#flot-pie-chart"), data, {
            series: {
                pie: {
                    show: true,
                    radius: 1,
                     label: {
                        show: true,
                        radius: 5/9,
                        formatter: labelFormatter,
                     }
                }
            },
            grid: {
                hoverable: true
            },
            tooltip: true,
            tooltipOpts: {
                content: "%p.0%, %s", // show percentages, rounding to 2 decimal places
                shifts: {
                    x: 20,
                    y: 0
                },
                defaultTheme: false
            }
        });

    }

   function labelFormatter(label, series) {
      return "<div style='font-size:8pt; text-align:center; padding:2px; color:white; text-shadow: 1px 1px #000000'>" + label + "<br/>" + Math.round(series.percent) + "%</div>";
   }

    drawFlotCharts();

    $(document).on("themechange", function(){
        drawFlotCharts();
    });

});
</script>
@endsection
