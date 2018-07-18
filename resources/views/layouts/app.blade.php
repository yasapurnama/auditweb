<!doctype html>
<html class="no-js" lang="{{ app()->getLocale() }}">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="x-ua-compatible" content="ie=edge">
        @if (Route::currentRouteName() == 'scan' || Route::currentRouteName() == 'history' || Route::currentRouteName() == 'result')
            <title> Website Auditor | Website Audit with Webscraping Technique </title>
        @elseif (Route::currentRouteName() == 'manage.history' || Route::currentRouteName() == 'manage.result')
            <title> Management Audit History | Website Audit with Webscraping Technique </title>
        @elseif (Route::currentRouteName() == 'manage.users' || Route::currentRouteName()=='manage.userview' || Route::currentRouteName()=='manage.useredit')
            <title> Management Users | Website Audit with Webscraping Technique </title>
        @else
            <title> {{ ucfirst(Route::currentRouteName()) }} | Website Audit with Webscraping Technique </title>
        @endif
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
                    {{-- <li class="breadcrumb-item"> Home</li> --}}
                    <li class="breadcrumb-item{{ Route::currentRouteName() == "dashboard" ? " active" : "" }}"><em class="fa fa-home"></em> Dashboard</li>
                    @if (Route::currentRouteName() == 'scan' || Route::currentRouteName() == 'history' || Route::currentRouteName() == 'result')
                        <li class="breadcrumb-item">Website Auditor</li>
                    @elseif (Route::currentRouteName() == 'manage.history' || Route::currentRouteName() == 'manage.users' || Route::currentRouteName()=='manage.userview' || Route::currentRouteName()=='manage.useredit' || Route::currentRouteName() == 'manage.result')
                        <li class="breadcrumb-item">Management</li>
                    @endif
                    @if (Route::currentRouteName() == 'manage.history' || Route::currentRouteName() == 'manage.result')
                        <li class="breadcrumb-item active">Audit History</li>
                    @elseif (Route::currentRouteName() == 'manage.users' || Route::currentRouteName()=='manage.userview' || Route::currentRouteName()=='manage.useredit')
                        <li class="breadcrumb-item active">Users</li>
                    @elseif (Route::currentRouteName() != 'dashboard')
                        <li class="breadcrumb-item active">{{ ucfirst(Route::currentRouteName()) }}</li>
                    @endif
                </ol>
                <article class="content dashboard-page">
                    
                    @yield('content')

                </article>
                <footer class="footer">
                    <div class="footer-block author">
                        <span class="text-center"><a href="http://themes.3rdwavemedia.com/" target="_blank">Purnama Yasa</a> &copy; 2018 | Powered by Modular Admin Theme</span>
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
<script type="text/javascript">
$(function() {

    $('#notifications-show').click(function(){
        $.getJSON("{{ route('notification.update') }}", function(data) {
            $.each(data, function(key, notif) {
                $('#notiftime-'+notif.id).html(notif.created_at);
            });
        });
    });

});
</script>
@yield('script')
    </body>
</html>
