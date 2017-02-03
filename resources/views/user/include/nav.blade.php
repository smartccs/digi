<div class="col-md-3">
    <div class="dash-left">
        <div class="user-img">
            <?php $profile_image = img(Auth::user()->picture); ?>
            <div class="pro-img" style="background-image: url({{$profile_image}});"></div>
            <h4>{{Auth::user()->first_name}} {{Auth::user()->last_name}}</h4>
        </div>
        <div class="side-menu">
             <ul>
                <li><a href="{{url('dashboard')}}">@lang('messages.dashboard')</a></li>
                <li><a href="user-mytrips.html">@lang('messages.my_trips')</a></li>
                <li><a href="{{url('profile')}}">@lang('messages.profile')</a></li>
                <li><a href="{{url('change/password')}}">@lang('messages.change_password')</a></li>
                <li><a href="user-payment.html">@lang('messages.payment')</a></li>
                <li><a href="user-payment.html">@lang('messages.wallet')</a></li>
                <li><a href="{{ url('/logout') }}"
                        onclick="event.preventDefault();
                        document.getElementById('logout-form').submit();">@lang('messages.logout')</a></li>
                        <form id="logout-form" action="{{ url('/logout') }}" method="POST" style="display: none;">
                            {{ csrf_field() }}
                        </form>
            </ul>
        </div>
    </div>
</div>