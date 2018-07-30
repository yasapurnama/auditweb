                        <div class="sidebar-header">
                            <div class="brand">
                                <a href="{{ url('/') }}"> 
                                <span class="new-logo">
                                    <img src="{{ asset('assets/logo.png') }}" width="30px">
                                </span> Website Audit </a></div>
                        </div>
                        <nav class="menu">
                            <ul class="sidebar-menu metismenu" id="sidebar-menu">
                                <li class="{{ Route::currentRouteName()=='dashboard' ? 'active' : '' }}">
                                    <a href="{{ route('dashboard') }}" {!! Route::currentRouteName()!='dashboard' ? 'style="background-color: #2d363f;"' : '' !!}>
                                        <i class="fa fa-home"></i> Dashboard </a>
                                </li>
                                @if (Auth::user()->role == 2)
                                <li class="{{ (Route::currentRouteName()=='manage.history' || Route::currentRouteName()=='manage.users' || Route::currentRouteName()=='manage.userview' || Route::currentRouteName()=='manage.useredit' || Route::currentRouteName() == 'manage.result') ? 'active open' : '' }}">
                                    <a href="">
                                        <i class="fa fa-th-large"></i> Management
                                        <i class="fa arrow"></i>
                                    </a>
                                    <ul class="sidebar-nav">
                                        <li class="{{ Route::currentRouteName()=='manage.users' ? 'active' : '' }}">
                                            <a href="{{ route('manage.users') }}"><i class="fa fa-users"></i> Users </a>
                                        </li>
                                        <li class="{{ Route::currentRouteName()=='manage.history' ? 'active' : '' }}">
                                            <a href="{{ route('manage.history') }}"><i class="fa fa-bar-chart"></i> Audit History </a>
                                        </li>
                                    </ul>
                                </li>
                                <li class="{{ (Route::currentRouteName()=='profile' || Route::currentRouteName()=='editprofile') ? 'active' : '' }}">
                                    <a href="{{ route('profile') }}">
                                        <i class="fa fa-user"></i> Admin Profile </a>
                                </li>
                                @else
                                <li class="{{ (Route::currentRouteName()=='scan' || Route::currentRouteName()=='history' || Route::currentRouteName()=='result') ? 'active open' : '' }}">
                                    <a href="">
                                        <i class="fa fa-bar-chart"></i> Website Auditor
                                        <i class="fa arrow"></i>
                                    </a>
                                    <ul class="sidebar-nav">
                                        <li class="{{ Route::currentRouteName()=='scan' ? 'active' : '' }}">
                                            <a href="{{ route('scan') }}"><i class="fa fa-search"></i> Scan </a>
                                        </li>
                                        <li class="{{ Route::currentRouteName()=='history' ? 'active' : '' }}">
                                            <a href="{{ route('history') }}"><i class="fa fa-history"></i> History </a>
                                        </li>
                                    </ul>
                                </li>
                                <li class="{{ (Route::currentRouteName()=='profile' || Route::currentRouteName()=='editprofile') ? 'active' : '' }}">
                                    <a href="{{ route('profile') }}">
                                        <i class="fa fa-user"></i> User Profile </a>
                                </li>
                                <li class="{{ Route::currentRouteName()=='notification' ? 'active' : '' }}">
                                    <a href="{{ route('notification') }}">
                                        <i class="fa fa-bell icon"></i> Notifications </a>
                                </li>
                                <li class="{{ Route::currentRouteName()=='setting' ? 'active' : '' }}">
                                    <a href="{{ route('setting') }}">
                                        <i class="fa fa-gear icon"></i> Settings </a>
                                </li>
                                @endif
                            </ul>
                        </nav>