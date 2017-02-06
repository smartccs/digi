@extends('user.layout.base')

@section('title', 'Ride Confirmation ')

@section('content')
<style type="text/css">

</style>
<div class="col-md-9">
    <div class="dash-content">
        <div class="row no-margin">
            <div class="col-md-12">
                <h4 class="page-title">Ride Now</h4>
            </div>
        </div>

        <div class="row no-margin">
            <div class="col-md-6">
                <form action="user-waiting-provider.html">
                    <dl class="dl-horizontal left-right">
                        <dt>Type</dt>
                        <dd>XUV</dd>
                        <dt>Total Distance</dt>
                        <dd>{{$fare->distance}} Kms</dd>
                        <dt>ETA</dt>
                        <dd>{{$fare->time}}</dd>
                        <dt>Estimate Amount</dt>
                        <dd>{{$fare->estimated_fare}}</dd>
                    </dl>

                    <button type="submit" class="full-primary-btn fare-btn">RIDE NOW</button>

                </form>
            </div>

            <div class="col-md-6">
                <div class="user-request-map">
                    <?php 
                    $map_icon = asset('asset/marker.png');
                    $static_map = "https://maps.googleapis.com/maps/api/staticmap?autoscale=1&size=600x450&maptype=roadmap&format=png&visual_refresh=true&markers=icon:".$map_icon."%7C".$request->s_latitude.",".$request->s_longitude."&markers=icon:".$map_icon."%7C".$request->d_latitude.",".$request->d_longitude."&path=color:0x191919|weight:8|".$request->s_latitude.",".$request->s_longitude."|".$request->d_latitude.",".$request->d_longitude."&key=".env('GOOGLE_API_KEY'); ?>
                    <div class="map-static" style="background-image: url({{$static_map}});">
                    </div>
                    <div class="from-to row no-margin">
                        <div class="from">
                            <h5>FROM</h5>
                            <p>{{$request->s_address}}</p>
                        </div>
                        <div class="to">
                            <h5>TO</h5>
                            <p>{{$request->d_address}}</p>
                        </div>
                    </div>
                </div> 
            </div>
        </div>

    </div>
</div>
@endsection