<!DOCTYPE html>
<html class="no-js" lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <title> Implementation of Website Audit with Webscraping Technique </title>
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
        .bg-primary { background-color: #4f5f6f !important; color: #ffffff !important; }
        .homepage-banner { padding: 60px 20px 50px 20px; }
        .landing-footer { background: #26282c; color: rgba(255,255,255,0.6); padding: 10px 0; }
        .landing-app {position: relative; width: 100%; min-height: 100vh; margin: 0 auto; left: 0; background-color: #f0f3f6; -webkit-box-shadow: 0 0 3px #ccc; box-shadow: 0 0 3px #ccc; -webkit-transition: left 0.3s ease, padding-left 0.3s ease; transition: left 0.3s ease, padding-left 0.3s ease; overflow: hidden; }
        #content { padding-top: 25px; }
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
          <li class="nav-item">
              <a class="nav-link" href="{{ url('/') }}">Home</a>
          </li>
        </ul>
        <ul class="navbar-nav ml-auto">
            @if (Route::has('login'))
                @auth
                    <li class="nav-item">
                        <a class="nav-link" href="{{ url('/dashboard') }}">Dashboard</a>
                    </li>
                @else
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('login') }}">Login</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('register') }}">Register</a>
                    </li>
                @endauth
            @endif
            
        </ul>
    </div>
</nav>

<div class="homepage-banner bg-primary" role="">
    <div class="container text-center">
        <div id="text-2" class="widget widget_text"><h1 class="homepage-title">Website Audit</h1>
        <div class="textwidget"><h5 class="thin">Implemented with Webscraping Technique Using PHP Programming Language</h5>
            {{-- <a class="btn btn-primary btn-cta" href="#" target="_blank"><i class="fa fa-cloud-download"></i> Download Now</a> --}}
        </div>
        </div>  
    </div>
</div>

<div id="content" class="landing-app text-center">
  <h3>Welcome to Home Page!</h3>
</div>

<footer class="landing-footer">
  <div class="text-center">
    <small><a href="http://themes.3rdwavemedia.com/" target="_blank">Purnama Yasa</a> &copy; 2017 | Powered by Modular Admin Theme</small>
  </div><!--//container-->
</footer><!--//footer-->

<!-- jQuery library -->
    <script src="{{ asset('js/vendor.js') }}"></script>
    <script src="{{ asset('js/app.js') }}"></script>
</body>
</html>
