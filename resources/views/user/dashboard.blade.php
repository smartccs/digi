@extends('user.layout.base')

@section('title', 'Dashboard ')

@section('content')

<div class="col-md-9">
    <div class="dash-content">
        <div class="row no-margin">
            <div class="col-md-12">
                <h4 class="page-title">Ride Now</h4>
            </div>
        </div>
        @include('common.notify')
        <div class="row no-margin">
            <div class="col-md-6">
                <form action="{{url('confirm/ride')}}" method="GET">
                <div class="input-group dash-form">
                    <input type="text" class="form-control" id="origin-input" name="s_address"  placeholder="Enter pickup location">
                </div>

                <div class="input-group dash-form">
                    <input type="text" class="form-control" id="destination-input" name="d_address"  placeholder="Enter drop location" > 
                </div>  

                <input type="hidden" name="s_latitude" id="origin_latitude">
                <input type="hidden" name="s_longitude" id="origin_longitude">
                <input type="hidden" name="d_latitude" id="destination_latitude">
                <input type="hidden" name="d_longitude" id="destination_longitude">

                <div class="car-detail">

                    @foreach($services as $service)

                    <div class="car-radio">
                        <input type="radio" name="service_type" value="{{$service->id}}" id="service_{{$service->id}}" @if ($loop->first) checked="checked" @endif>
                        <label for="service_{{$service->id}}">
                            <div class="car-radio-inner">
                                <div class="img"><img src="{{img($service->image)}}"></div>
                                <div class="name"><span>{{$service->name}}</span></div>
                            </div>
                        </label>
                    </div>

                    @endforeach


                </div>

                <button type="submit" class="full-primary-btn fare-btn">RIDE NOW</button>

                </form>
            </div>
                

            <div class="col-md-6">
                <div class="map-responsive">
                    <div id="map" style="width: 600px;height: 450px;"></div>
                </div> 
            </div>
        </div>

    </div>
</div>

@endsection

@section('scripts')
    
    <script type="text/javascript" src="{{asset('asset/js/map.js')}}"></script>
    <script src="https://maps.googleapis.com/maps/api/js?key={{env('GOOGLE_API_KEY')}}&libraries=places&callback=initMap"
        async defer></script>
@endsection