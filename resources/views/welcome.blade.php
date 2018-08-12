<!DOCTYPE html>
<html class="no-js" lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <title> Implementation of Website Security Tools with Webscraping Technique </title>
    <meta name="description" content="">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}" type="image/x-icon" />
    <!-- Place favicon.ico in the root directory -->
    <link rel="stylesheet" href="{{ asset('css/vendor.css') }}">
    <!-- Theme initialization -->
    <script>
        var themeSettings = (localStorage.getItem('themeSettings')) ? JSON.parse(localStorage.getItem('themeSettings')) :
        {};
        var themeName = themeSettings.themeName || '';
        var csspath = "{{ asset('css/') }}";
        if (themeName)
        {
            document.write('<link rel="stylesheet" id="theme-style" href="{{ asset('css/') }}/app-' + themeName + '.css">');
        }
        else
        {
            document.write('<link rel="stylesheet" id="theme-style" href="{{ asset('css/') }}/app.css">');
        }
    </script>
    <style type="text/css">
        .bg-primary{background-color:#4f5f6f!important;color:#fff!important}.homepage-banner{padding:60px 20px 50px}.landing-footer{background:#26282c;color:rgba(255,255,255,.6);padding:10px 0}.landing-app{position:relative;width:100%;min-height:80vh;margin:0 auto;left:0;background-color:#f0f3f6;-webkit-box-shadow:0 0 3px #ccc;box-shadow:0 0 3px #ccc;-webkit-transition:left .3s ease,padding-left .3s ease;transition:left .3s ease,padding-left .3s ease;overflow:hidden}#content{padding:30px 280px 0}.textwidget{padding:5px 0 15px}.cards-section2{padding:60px 0}.cards-section2 .title{margin-top:0;margin-bottom:15px;font-size:24px;font-weight:600}.cards-section2 .intro{margin:0 auto 60px;max-width:800px;color:#616670}.cards-section2 .cards-wrapper{max-width:860px;margin-left:auto;margin-right:auto}.cards-section2 .item{margin-bottom:30px}.cards-section2 .item .icon-holder,.cards-section2 .item .intro{margin-bottom:15px}.cards-section2 .item .icon{font-size:36px}.cards-section2 .item .title{font-size:16px;font-weight:600}.cards-section2 .item-inner{padding:45px 30px;background:#fff;position:relative;border:1px solid #f0f0f0;-webkit-border-radius:4px;-moz-border-radius:4px;-ms-border-radius:4px;-o-border-radius:4px;border-radius:4px;-moz-background-clip:padding;-webkit-background-clip:padding-box;background-clip:padding-box}.cards-section2 .item-inner .link{position:absolute;width:100%;height:100%;top:0;left:0;z-index:1;background-image:url(../images/empty.gif)}.cards-section2 .item-inner:hover{background:#f5f5f5}.cards-section2 .item-primary .item-inner{border-top:3px solid #40babd}.cards-section2 .item-primary .item-inner:hover .title{color:#2d8284}.cards-section2 .item-primary .icon{color:#40babd}.cards-section2 .item-green .item-inner{border-top:3px solid #75c181}.cards-section2 .item-green .item-inner:hover .title{color:#48a156}.cards-section2 .item-green .icon{color:#75c181}.cards-section2 .item-blue .item-inner{border-top:3px solid #58bbee}.cards-section2 .item-blue .item-inner:hover .title{color:#179de2}.cards-section2 .item-blue .icon{color:#58bbee}.cards-section2 .item-orange .item-inner{border-top:3px solid #F88C30}.cards-section2 .item-orange .item-inner:hover .title{color:#d46607}.cards-section2 .item-orange .icon{color:#F88C30}.cards-section2 .item-red .item-inner{border-top:3px solid #f77b6b}.cards-section2 .item-red .item-inner:hover .title{color:#f33a22}.cards-section2 .item-red .icon{color:#f77b6b}.cards-section2 .item-pink .item-inner{border-top:3px solid #EA5395}.cards-section2 .item-pink .item-inner:hover .title{color:#d61a6c}.cards-section2 .item-pink .icon{color:#EA5395}.cards-section2 .item-purple .item-inner{border-top:3px solid #8A40A7}.cards-section2 .item-purple .item-inner:hover .title{color:#5c2b70}.cards-section .item-purple .icon{color:#8A40A7}
    </style>
</head>
<body>
<!-- example 1 - using absolute position for center -->
<nav class="navbar navbar-expand-md navbar-dark bg-primary">
    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#collapsingNavbar">
        <span class="navbar-toggler-icon"></span>
    </button>
    <div class="navbar-collapse collapse" id="collapsingNavbar">
        <ul class="navbar-nav">
        </ul>
        <ul class="navbar-nav ml-auto">
            <li class="nav-item">
                <a class="nav-link" href="{{ url('/') }}">Home</a>
            </li>
            @if (Route::has('login'))
                @auth
                    <li class="nav-item">
                        <a class="nav-link" href="{{ url('/dashboard') }}">Dashboard</a>
                    </li>
                @else
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('register') }}">Register</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('login') }}">Login</a>
                    </li>
                @endauth
            @endif
            
        </ul>
    </div>
</nav>

<div class="homepage-banner bg-primary" role="">
    <div class="container text-center">
        <div id="text-2" class="widget widget_text"><h1 class="homepage-title">Website Security</h1>
        <div class="textwidget"><h5 class="thin">Computer Aid Auditing Tools Implemented with Web Scraping Technique</h5>
        </div>
        <a href="{{ route('scan') }}" class="btn btn-primary btn-lg">Get Started <em class="fa fa-play-circle"></em></a>
        </div>  
    </div>
</div>

<div id="content" class="landing-app text-center">
  <p style="font-size: 15px">Welcome to auditweb. This website have features to audit website in automated way.<br/>
Gaining website information made easy with Web Scraping Technique.</p>
  <div id="cards-wrapper" class="cards-section2 row">
                    <div class="item item-green col-md-4 col-sm-6 col-xs-6">
                        <div class="item-inner" style="height: 237px;">
                            <div class="icon-holder">
                                <i class="icon fa fa-pie-chart"></i>
                            </div><!--//icon-holder-->
                            <h3 class="title">Audit Website</h3>
                            <p class="intro">Audit website basic meta data and informations</p>
                        </div><!--//item-inner-->
                    </div><!--//item-->
                    <div class="item item-pink item-2 col-md-4 col-sm-6 col-xs-6">
                        <div class="item-inner" style="height: 237px;">
                            <div class="icon-holder">
                                <span aria-hidden="true" class="icon fa fa-paper-plane"></span>
                            </div><!--//icon-holder-->
                            <h3 class="title">Quick Result</h3>
                            <p class="intro">Website audit is made automatically with web scraping technique</p>
                        </div><!--//item-inner-->
                    </div><!--//item-->
                    <div class="item item-blue col-md-4 col-sm-6 col-xs-6">
                        <div class="item-inner" style="height: 237px;">
                            <div class="icon-holder">
                                <span aria-hidden="true" class="icon fa fa-inbox"></span>
                            </div><!--//icon-holder-->
                            <h3 class="title">Inbox</h3>
                            <p class="intro">Website audit report is sent to your inbox</p>
                        </div><!--//item-inner-->
                    </div><!--//item-->
                </div>
</div>

<footer class="landing-footer">
  <div class="text-center">
    <small><a href="mailto:yasapurnama@gmail.com" target="_blank">Purnama Yasa</a> &copy; 2018 | Powered by Modular Admin Theme</small>
  </div><!--//container-->
</footer><!--//footer-->

<!-- jQuery library -->
    <script src="{{ asset('js/vendor.js') }}"></script>
    <script src="{{ asset('js/app.js') }}"></script>
</body>
</html>
