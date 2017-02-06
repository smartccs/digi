<header>
    <nav class="navbar navbar-fixed-top">
        <div class="container">
            <div class="col-md-12">
                <div class="navbar-header">
                    <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#main-nav" aria-expanded="false">
                        <span class="sr-only">Toggle navigation</span>
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                    </button>
                    <a class="navbar-brand" href="{{ route('provider.index') }}">
                        <img src="{{ Setting::get('site_logo', asset('asset/img/logo.png')) }}">
                    </a>
                </div>
                <div class="collapse navbar-collapse" id="main-nav">
                    <ul class="nav navbar-nav navbar-right">
                        <li class="menu-drop">
                            <div class="dropdown">
                                <button class="btn btn-primary dropdown-toggle" type="button" data-toggle="dropdown">
                                    {{ Auth::guard('provider')->user()->first_name }} {{ Auth::guard('provider')->user()->last_name }}
                                    <span class="caret"></span>
                                </button>
                                <ul class="dropdown-menu">
                                    <li><a href="{{ route('provider.trips') }}">My Trips</a></li>
                                    <li><a href="{{ route('provider.profile.show') }}">Profile</a></li>
                                    <li><a href="{{ route('provider.password') }}">Change Password</a></li>
                                    <li>
                                        <a href="{{ url('/provider/logout') }}"
                                            onclick="event.preventDefault();
                                                     document.getElementById('logout-form').submit();">
                                            Logout
                                        </a>
                                        <form id="logout-form" action="{{ url('/provider/logout') }}" method="POST" style="display: none;">
                                            {{ csrf_field() }}
                                        </form>
                                    </li>
                                </ul>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </nav>
</header>