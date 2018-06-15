                        <div class="sidebar-header">
                            <div class="brand">
                                <span class="new-logo">
                                    <img src="{{ asset('assets/logo.png') }}" width="30px">
                                </span> Website Audit </div>
                        </div>
                        <nav class="menu">
                            <ul class="sidebar-menu metismenu" id="sidebar-menu">
                                <li class="{{ Route::currentRouteName()=='dashboard' ? 'active' : '' }}">
                                    <a href="{{ route('dashboard') }}" {!! Route::currentRouteName()!='dashboard' ? 'style="background-color: #2d363f;"' : '' !!}>
                                        <i class="fa fa-home"></i> Dashboard </a>
                                </li>
                                @if (Auth::user()->role == 2)
                                <li class="{{ (Route::currentRouteName()=='manage.history' || Route::currentRouteName()=='manage.users' || Route::currentRouteName()=='manage.usersedit' || Route::currentRouteName() == 'manage.result') ? 'active open' : '' }}">
                                    <a href="">
                                        <i class="fa fa-th-large"></i> Management
                                        <i class="fa arrow"></i>
                                    </a>
                                    <ul class="sidebar-nav">
                                        <li class="{{ (Route::currentRouteName()=='manage.users' || Route::currentRouteName()=='manage.usersedit') ? 'active' : '' }}">
                                            <a href="{{ route('manage.users') }}"><i class="fa fa-users"></i> Users </a>
                                        </li>
                                        <li class="{{ (Route::currentRouteName()=='manage.history' || Route::currentRouteName() == 'manage.result') ? 'active' : '' }}">
                                            <a href="{{ route('manage.history') }}"><i class="fa fa-clock-o"></i> History </a>
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
                                            <a href="{{ route('history') }}"><i class="fa fa-clock-o"></i> History </a>
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
                                
                                {{-- <li>
                                    <a href="">
                                        <i class="fa fa-th-large"></i> Items Manager
                                        <i class="fa arrow"></i>
                                    </a>
                                    <ul class="sidebar-nav">
                                        <li>
                                            <a href="items-list.html"> Items List </a>
                                        </li>
                                        <li>
                                            <a href="item-editor.html"> Item Editor </a>
                                        </li>
                                    </ul>
                                </li>
                                <li>
                                    <a href="">
                                        <i class="fa fa-bar-chart"></i> Charts
                                        <i class="fa arrow"></i>
                                    </a>
                                    <ul class="sidebar-nav">
                                        <li>
                                            <a href="charts-flot.html"> Flot Charts </a>
                                        </li>
                                        <li>
                                            <a href="charts-morris.html"> Morris Charts </a>
                                        </li>
                                    </ul>
                                </li>
                                <li>
                                    <a href="">
                                        <i class="fa fa-table"></i> Tables
                                        <i class="fa arrow"></i>
                                    </a>
                                    <ul class="sidebar-nav">
                                        <li>
                                            <a href="static-tables.html"> Static Tables </a>
                                        </li>
                                        <li>
                                            <a href="responsive-tables.html"> Responsive Tables </a>
                                        </li>
                                    </ul>
                                </li>
                                <li>
                                    <a href="forms.html">
                                        <i class="fa fa-pencil-square-o"></i> Forms </a>
                                </li>
                                <li>
                                    <a href="">
                                        <i class="fa fa-desktop"></i> UI Elements
                                        <i class="fa arrow"></i>
                                    </a>
                                    <ul class="sidebar-nav">
                                        <li>
                                            <a href="buttons.html"> Buttons </a>
                                        </li>
                                        <li>
                                            <a href="cards.html"> Cards </a>
                                        </li>
                                        <li>
                                            <a href="typography.html"> Typography </a>
                                        </li>
                                        <li>
                                            <a href="icons.html"> Icons </a>
                                        </li>
                                        <li>
                                            <a href="grid.html"> Grid </a>
                                        </li>
                                    </ul>
                                </li>
                                <li>
                                    <a href="">
                                        <i class="fa fa-file-text-o"></i> Pages
                                        <i class="fa arrow"></i>
                                    </a>
                                    <ul class="sidebar-nav">
                                        <li>
                                            <a href="login.html"> Login </a>
                                        </li>
                                        <li>
                                            <a href="signup.html"> Sign Up </a>
                                        </li>
                                        <li>
                                            <a href="reset.html"> Reset </a>
                                        </li>
                                        <li>
                                            <a href="error-404.html"> Error 404 App </a>
                                        </li>
                                        <li>
                                            <a href="error-404-alt.html"> Error 404 Global </a>
                                        </li>
                                        <li>
                                            <a href="error-500.html"> Error 500 App </a>
                                        </li>
                                        <li>
                                            <a href="error-500-alt.html"> Error 500 Global </a>
                                        </li>
                                    </ul>
                                </li>
                                <li>
                                    <a href="">
                                        <i class="fa fa-sitemap"></i> Menu Levels
                                        <i class="fa arrow"></i>
                                    </a>
                                    <ul class="sidebar-nav">
                                        <li>
                                            <a href="#"> Second Level Item
                                                <i class="fa arrow"></i>
                                            </a>
                                            <ul class="sidebar-nav">
                                                <li>
                                                    <a href="#"> Third Level Item </a>
                                                </li>
                                                <li>
                                                    <a href="#"> Third Level Item </a>
                                                </li>
                                            </ul>
                                        </li>
                                        <li>
                                            <a href="#"> Second Level Item </a>
                                        </li>
                                        <li>
                                            <a href="#"> Second Level Item
                                                <i class="fa arrow"></i>
                                            </a>
                                            <ul class="sidebar-nav">
                                                <li>
                                                    <a href="#"> Third Level Item </a>
                                                </li>
                                                <li>
                                                    <a href="#"> Third Level Item </a>
                                                </li>
                                                <li>
                                                    <a href="#"> Third Level Item
                                                        <i class="fa arrow"></i>
                                                    </a>
                                                    <ul class="sidebar-nav">
                                                        <li>
                                                            <a href="#"> Fourth Level Item </a>
                                                        </li>
                                                        <li>
                                                            <a href="#"> Fourth Level Item </a>
                                                        </li>
                                                    </ul>
                                                </li>
                                            </ul>
                                        </li>
                                    </ul>
                                </li>
                                <li>
                                    <a href="https://github.com/modularcode/modular-admin-html">
                                        <i class="fa fa-github-alt"></i> Theme Docs </a>
                                </li> --}}
                            </ul>
                        </nav>