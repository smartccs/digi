@extends('provider.layout.app')

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
                    <h5 class="col-md-6 no-padding"><strong>Name</strong></h5>
                    <p class="col-md-6 no-padding">{{ Auth::guard('provider')->user()->first_name }} {{ Auth::guard('provider')->user()->last_name }}</p>
                </div>
                <div class="col-md-6 pro-form">
                    <h5 class="col-md-6 no-padding"><strong>Email</strong></h5>
                    <p class="col-md-6 no-padding">{{ Auth::guard('provider')->user()->email }}</p>
                </div>

                <div class="col-md-6 pro-form">
                    <h5 class="col-md-6 no-padding"><strong>Profile Photo</strong></h5>
                    <p class="col-md-6 no-padding pro-img-section"><span class="user-pro-img" style="background-image: url({{ Auth::guard('provider')->user()->avatar ? : asset('asset/img/provider.jpg') }});"></span></p>
                </div>

                <div class="col-md-6 pro-form">
                    <h5 class="col-md-6 no-padding"><strong>Phone</strong></h5>
                    <p class="col-md-6 no-padding">{{ Auth::guard('provider')->user()->mobile }}</p>
                </div>

                <div class="col-md-12 pro-form">
                    <a class="form-sub-btn" href="{{ route('provider.profile.edit') }}">Edit</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection