    <header>
        <nav class="navbar navbar-fixed-top">
          <div class="container">
            <div class="col-md-12">
                <div class="navbar-header">
                <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1" aria-expanded="false">
                    <span class="sr-only">Toggle navigation</span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                  </button>             

                  <a class="navbar-brand" href="{{url('dashboard')}}"><img src="{{Setting::get('site_logo')}}"></a>
                </div>
                <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">            
                    <ul class="nav navbar-nav navbar-right">                  
                      <li class="menu-drop">
                          <div class="dropdown">
                              <button class="btn btn-primary dropdown-toggle" type="button" data-toggle="dropdown">{{Auth::user()->first_name}} {{Auth::user()->last_name}}
                              <span class="caret"></span></button>
                              <ul class="dropdown-menu">
                                <li><a href="{{url('dashboard')}}">Home</a></li>
                                <li><a href="user-mytrips.html">My Trips</a></li>
                                <li><a href="user-profile.html">Profile</a></li>
                                <li><a href="user-change-password.html">Change Password</a></li>
                                <li><a href="user-payment.html">Payment</a></li>
                                <li><a href="{{ url('/logout') }}"
                                    onclick="event.preventDefault();
                                    document.getElementById('logout-form').submit();">Logout</a></li>
                                    <form id="logout-form" action="{{ url('/logout') }}" method="POST" style="display: none;">
                                        {{ csrf_field() }}
                                    </form>
                              </ul>
                            </div>
                      </li>
                    </ul>
                </div>
            </div>
          </div>
        </nav>
    </header>