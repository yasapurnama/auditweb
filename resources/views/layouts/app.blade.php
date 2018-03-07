<!doctype html>
<html class="no-js" lang="{{ app()->getLocale() }}">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="x-ua-compatible" content="ie=edge">
        <title> {{ ucfirst(Route::currentRouteName()) }} | Website Audit with Webscraping Technique </title>
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
            if (themeName)
            {
                document.write('<link rel="stylesheet" id="theme-style" href="{{ asset('css/') }}/app-' + themeName + '.css">');
            }
            else
            {
                document.write('<link rel="stylesheet" id="theme-style" href="{{ asset('css/') }}/app.css">');
            }
        </script>
    </head>
    <body>
        <div class="main-wrapper">
            <div class="app" id="app">
                <header class="header">
                    
                    @include('layouts.partials._header')

                </header>
                <aside class="sidebar">
                    <div class="sidebar-container">

                        @include('layouts.partials._sidebar_container')

                    </div>
                    <footer class="sidebar-footer">
                        @include('layouts.partials._sidebar_footer')
                    </footer>
                </aside>
                <div class="sidebar-overlay" id="sidebar-overlay"></div>
                <div class="sidebar-mobile-menu-handle" id="sidebar-mobile-menu-handle"></div>
                <div class="mobile-menu-handle"></div>
                <ol class="new-breadcrumb">
                    <li class="breadcrumb-item"><em class="fa fa-home"></em> Home</li>
                    <li class="breadcrumb-item active">Dashboard</li>
                </ol>
                <article class="content dashboard-page">
                    
                    @yield('content')

                </article>
                <footer class="footer">
                    <div class="footer-block author">
                        <span class="text-center"><a href="http://themes.3rdwavemedia.com/" target="_blank">Purnama Yasa</a> &copy; 2017 | Powered by Modular Admin Theme</span>
                    </div>
                </footer>

                @include('layouts.partials._modals')
                
            </div>
        </div>
        <!-- Reference block for JS -->
        <div class="ref" id="ref">
            <div class="color-primary"></div>
            <div class="chart">
                <div class="color-primary"></div>
                <div class="color-secondary"></div>
            </div>
        </div>
        <script src="{{ asset('js/vendor.js') }}"></script>
        <script src="{{ asset('js/app.js') }}"></script>
@yield('script')
    </body>
</html>
