@extends('user.layout.base')

@section('title', 'Profile ')

@section('content')

<div class="col-md-9">
    <div class="dash-content">
        <div class="row no-margin">
            <div class="col-md-12">
                <h4 class="page-title">General Information</h4>
            </div>
        </div>

        <div class="row no-margin">
            <form>
                <div class="col-md-6 pro-form">
                    <h5 class="col-md-6 no-padding"><strong>First Name</strong></h5>
                    <p class="col-md-6 no-padding">{{Auth::user()->first_name}}</p>                       
                </div>
                <div class="col-md-6 pro-form">
                    <h5 class="col-md-6 no-padding"><strong>Last Name</strong></h5>
                    <p class="col-md-6 no-padding">{{Auth::user()->last_name}}</p>                       
                </div>
                <div class="col-md-6 pro-form">
                    <h5 class="col-md-6 no-padding"><strong>Email</strong></h5>
                    <p class="col-md-6 no-padding">{{Auth::user()->email}}</p>
                </div>

                <div class="col-md-6 pro-form">
                    <h5 class="col-md-6 no-padding"><strong>Profile Photo</strong></h5>
                    <?php $profile_img = Auth::user()->picture; ?>
                    <p class="col-md-6 no-padding pro-img-section"><span class="user-pro-img" style="background-image: url({{$profile_img}});"></span></p>
                </div>

                <div class="col-md-6 pro-form">
                    <h5 class="col-md-6 no-padding"><strong>Phone</strong></h5>
                    <p class="col-md-6 no-padding">{{Auth::user()->mobile}}</p>
                </div>

                <div class="col-md-6 pro-form">
                    <h5 class="col-md-6 no-padding"><strong>Wallet Balance</strong></h5>
                    <p class="col-md-6 no-padding">{{Auth::user()->wallet_balance}}</p>
                </div>

                <div class="col-md-6 pro-form">
                    <a class="form-sub-btn" href="{{url('edit/profile')}}">Edit</a>
                </div>


            </form>
        </div>

    </div>
</div>

@endsection