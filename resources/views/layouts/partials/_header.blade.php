                    <div class="header-block header-block-collapse d-lg-none d-xl-none">
                        <button class="collapse-btn" id="sidebar-collapse-btn">
                            <i class="fa fa-bars"></i>
                        </button>
                    </div>
                    <div class="header-block header-block-search">
                        <strong>
                            @if (Route::currentRouteName() == "scan" || Route::currentRouteName() == "history" || Route::currentRouteName() == "result")
                                Website Auditor
                            @elseif (Route::currentRouteName() == "manage.history" || Route::currentRouteName() == "manage.result")
                                Management History
                            @elseif (Route::currentRouteName() == "manage.users" || Route::currentRouteName() == "manage.usersedit")
                                Management Users
                            @else
                                {{ ucfirst(Route::currentRouteName()) }}
                            @endif
                        </strong>
                    </div>
                    {{-- <div class="header-block header-block-search">
                        <form role="search">
                            <div class="input-container">
                                <i class="fa fa-search"></i>
                                <input type="search" placeholder="Search">
                                <div class="underline"></div>
                            </div>
                        </form>
                    </div>
                    <div class="header-block header-block-buttons">
                        <a href="https://github.com/modularcode/modular-admin-html" class="btn btn-sm header-btn">
                            <i class="fa fa-github-alt"></i>
                            <span>View on GitHub</span>
                        </a>
                        <a href="https://github.com/modularcode/modular-admin-html/stargazers" class="btn btn-sm header-btn">
                            <i class="fa fa-star"></i>
                            <span>Star Us</span>
                        </a>
                        <a href="https://github.com/modularcode/modular-admin-html/releases" class="btn btn-sm header-btn">
                            <i class="fa fa-cloud-download"></i>
                            <span>Download .zip</span>
                        </a>
                    </div> --}}

                    @inject('notifications', 'App\Http\Controllers\NotificationController')
                    @php
                        $notifications = $notifications->show();
                        $count_notification = 0;
                        foreach ($notifications as $notification) {
                            if(!$notification->readed){
                                $count_notification += 1;
                            }
                        }
                    @endphp
                    <div class="header-block header-block-nav">
                        <ul class="nav-profile">
                            <li class="notifications new">
                                <a href="" data-toggle="dropdown" id="notifications-show">
                                    <i class="fa fa-bell-o"></i>
                                    <sup>
                                        <span class="counter">{{ $count_notification }}</span>
                                    </sup>
                                </a>
                                <div class="dropdown-menu notifications-dropdown-menu">
                                    <ul class="notifications-container">
                                        
                                        @foreach ($notifications as $key=>$notification)
                                            @if (++$key <= 4)
                                                <li>
                                                <a href="" class="notification-item">
                                                    <div class="img-col">
                                                        <div class="img" style="background-image: url('{{ $notification->owner_report ? asset('assets/email.png') : asset('assets/email_dark.png') }}')"></div>
                                                    </div>
                                                    <div class="body-col">
                                                        <p>
                                                            <span class="accent">System</span> {{ $notification->notif_message }} <br/>
                                                            <i class="{{ $notification->readed ? 'fa fa-envelope-o icon' : 'fa fa-envelope icon' }}"></i> <span id="notiftime-{{ $notification->id }}">{{ $notification->created_at->diffForHumans() }}</span>
                                                        </p>
                                                    </div>
                                                </a>
                                            </li>
                                            @endif
                                        @endforeach
                                    </ul>
                                    <footer>
                                        <ul>
                                            <li>
                                                <a href="{{ route('notification') }}"> View All </a>
                                            </li>
                                        </ul>
                                    </footer>
                                </div>
                            </li>
                            <li class="profile dropdown">
                                <a class="nav-link dropdown-toggle" data-toggle="dropdown" href="#" role="button" aria-haspopup="true" aria-expanded="false">
                                    <div class="img" style="background-image: url('{{ asset('assets/profile.png') }}')"> </div>
                                    <span class="name">  {{ Auth::user()->name }} </span>
                                </a>
                                <div class="dropdown-menu profile-dropdown-menu" aria-labelledby="dropdownMenu1">
                                    <a class="dropdown-item" href="{{ route('profile') }}">
                                        <i class="fa fa-user icon"></i> Profile </a>
                                    @if (Auth::user()->role == 1)
                                    <a class="dropdown-item" href="{{ route('notification') }}">
                                        <i class="fa fa-bell icon"></i> Notifications </a>
                                    <a class="dropdown-item" href="{{ route('setting') }}">
                                        <i class="fa fa-gear icon"></i> Settings </a>
                                    @endif
                                    <div class="dropdown-divider"></div>
                                    <a class="dropdown-item" href="{{ route('logout') }}" onclick="event.preventDefault();document.getElementById('logout-form').submit();">
                                        <i class="fa fa-power-off icon"></i> Logout </a>
                                    <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                                        {{ csrf_field() }}
                                    </form>
                                </div>
                            </li>
                        </ul>
                    </div>