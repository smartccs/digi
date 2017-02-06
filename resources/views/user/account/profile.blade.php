@extends('user.layout.base')

@section('title', 'Profile ')

@section('content')

<div class="col-md-9">
    <div class="dash-content">
        <div class="row no-margin">
            <div class="col-md-12">
                <h4 class="page-title">@lang('messages.general_information')</h4>
            </div>
        </div>
            @include('common.notify')
        <div class="row no-margin">
            <form>
                <div class="col-md-6 pro-form">
                    <h5 class="col-md-6 no-padding"><strong>@lang('messages.first_name')</strong></h5>
                    <p class="col-md-6 no-padding">{{Auth::user()->first_name}}</p>                       
                </div>
                <div class="col-md-6 pro-form">
                    <h5 class="col-md-6 no-padding"><strong>@lang('messages.last_name')</strong></h5>
                    <p class="col-md-6 no-padding">{{Auth::user()->last_name}}</p>                       
                </div>
                <div class="col-md-6 pro-form">
                    <h5 class="col-md-6 no-padding"><strong>@lang('messages.email')</strong></h5>
                    <p class="col-md-6 no-padding">{{Auth::user()->email}}</p>
                </div>

                <div class="col-md-6 pro-form">
                    <h5 class="col-md-6 no-padding"><strong>@lang('messages.mobile')</strong></h5>
                    <p class="col-md-6 no-padding">{{Auth::user()->mobile}}</p>
                </div>

                <div class="col-md-6 pro-form">
                    <h5 class="col-md-6 no-padding"><strong>@lang('messages.wallet_balance')</strong></h5>
                    <p class="col-md-6 no-padding">{{Auth::user()->wallet_balance}}</p>
                </div>

                <div class="col-md-6 pro-form">
                    <a class="form-sub-btn" href="{{url('edit/profile')}}">@lang('messages.edit')</a>
                </div>


            </form>
        </div>

    </div>
</div>

@endsection