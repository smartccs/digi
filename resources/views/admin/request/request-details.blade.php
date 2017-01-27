@extends('admin.layout.base')

@section('title', 'Request details ')

@section('content')

    <div class="content-area py-1">
        <div class="container-fluid">
            <div class="box box-block bg-white">
            	<h4>Request details</h4>
    	    <a href="{{ route('admin.request.history') }}" class="btn btn-default pull-right"><i class="fa fa-angle-left"></i> Back</a>

            	<br>
            	<br>

            		<div class="row">

		              <div class="col-md-6">

		                <dl class="row">

		                    <dt class="col-sm-4">User Name :</dt>
		                    <dd class="col-sm-8">{{$request->user_first_name}}</dd>

		                    <dt class="col-sm-4">Provider Name :</dt>
		                    <dd class="col-sm-8">{{$request->provider_first_name}}</dd>

		                    <dt class="col-sm-4">Total Time :</dt>
		                    <dd class="col-sm-8">{{$request->total_time ? $request->total_time : '-'}}</dd>

		                    <dt class="col-sm-4">Request Start Time :</dt>
		                    <dd class="col-sm-8">
		                    	@if($request->start_time != "0000-00-00 00:00:00")
		                     		{{date('jS \of F Y h:i:s A', strtotime($request->start_time)) }} 
		                     	@else
		                     		- 
		                     	@endif
		                     </dd>

		                    <dt class="col-sm-4">Request End Time :</dt>
		                    <dd class="col-sm-8">
		                    	@if($request->end_time != "0000-00-00 00:00:00") 
		                    		{{date('jS \of F Y h:i:s A', strtotime($request->end_time)) }}
		                    	@else
		                    		- 
		                    	@endif
		                    </dd>
		                    @if($request->later == 1) 
		                        <dt class="col-sm-4">Requested Time :</dt>
		                        <dd class="col-sm-8">
		                        	@if($request->requested_time != "0000-00-00 00:00:00")
		                        		{{date('jS \of F Y h:i:s A', strtotime($request->requested_time)) }}
		                        	@endif
		                        </dd>
		                    @endif

		                </dl>

		              </div>

		            <div class="col-md-6">

		                <dl class="row">

		                    <dt class="col-sm-4">Address :</dt>
		                    <dd class="col-sm-8">{{$request->request_address ? $request->request_address : '-' }}</dd>

		                    <dt class="col-sm-4">Base Price :</dt>
		                    <dd class="col-sm-8">{{$request->base_price ? currency($request->base_price) : currency(' 0.00')}}</dd>

		                    <dt class="col-sm-4">Time Price :</dt>
		                    <dd class="col-sm-8">{{$request->time_price ? currency($request->time_price) : currency(' 0.00')}}</dd>

		                    <dt class="col-sm-4">Tax Price :</dt>
		                    <dd class="col-sm-8">{{$request->tax ? currency($request->tax) : currency(' 0.00')}}</dd>

		                    <dt class="col-sm-4">Total Amount :</dt>
		                    <dd class="col-sm-8">
		                    	{{$request->total_amount ? currency($request->total_amount) : currency(' 0.00')}}
		                    </dd>

		                    <dt class="col-sm-4">Request Status : </dt>
		                    <dd class="col-sm-8">
		                        @if($request->status == 0) 
		                            New
		                        @elseif($request->status == 1)
		                            Waiting
		                        @elseif($request->status == 2)

		                            @if($request->provider_status == 0)
		                                Provider Not Found
		                            @elseif($request->provider_status == 1)
		                                Provider Accepted
		                            @elseif($request->provider_status == 2)
		                                Provider Started
		                            @elseif($request->provider_status == 3)
		                                Provider Arrived
		                            @elseif($request->provider_status == 4)
		                                Service Started
		                            @elseif($request->provider_status == 5)
		                                Service Completed
		                            @elseif($request->provider_status == 6)
		                                Provider Rated
		                            @endif

		                        @elseif($request->status == 3)
		                              Payment Pending
		                        @elseif($request->status == 4)

		                              Request Rating
		                        @elseif($request->status == 5)

		                              Request Completed
		                        @elseif($request->status == 6)

		                              Request Cancelled
		                        @elseif($request->status == 7)
		                              Provider Not Available
		                        @elseif($request->status == 8)
		                              COD - Waiting for payment Confirmation
		                        @endif
		                    </dd>

		                </dl>
		            </div>

		            @if($request->before_image !='')
			        <div class="col-md-6">
			            <div class="row">
			              <div class="col-md-12">
			                <section class="widget bg-white post-comments">
			                    <div class="widget bg-success mb0 text-center no-radius"><strong>Before Image</strong></div>
			                        <div class="media">
			                            <img style="width:100%;" src="{{$request->before_image}}" alt="">
			                        </div>
			                </section>
			              </div>
			            </div>
			        </div>
			        <div class="col-md-6">
			            <div class="row">
			              <div class="col-md-12">
			                <section class="widget bg-white post-comments">
			                    <div class="widget bg-success mb0 text-center no-radius"><strong>After Image</strong></div>
			                        <div class="media">
			                            <img style="width:100%;" src="{{$request->after_image}}" alt="">
			                        </div>
			                </section>
			              </div>
			            </div>
			        </div>
			        @endif
			        <div class="row">
			            <div class="col-xs-12">
			                <div id="map"></div>
			            </div>
			        </div>
		        </div>
            </div>
        </div>
    </div>

@endsection

@section('styles')
<style type="text/css">
    html, body {
        height: 100%;
        margin: 0;
        padding: 0;
    }

    #map {
        height: 100%;
        min-height: 400px; 
    }
    
    .controls {
        /*margin-top: 10px;*/
        border: 1px solid transparent;
        border-radius: 2px 0 0 2px;
        box-sizing: border-box;
        -moz-box-sizing: border-box;
        height: 32px;
        outline: none;
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.3);
        margin-bottom: 10px;
    }

    #pac-input {
        background-color: #fff;
        font-family: Roboto;
        font-size: 15px;
        font-weight: 300;
        padding: 0 11px 0 13px;
        text-overflow: ellipsis;
        width: 100%;
    }

    #pac-input:focus {
        border-color: #4d90fe;
    }

    #location-search {
        width: 100%;
    }

</style>
@endsection

@section('scripts')
<script>
    var map;
    var serviceLocation = {lat: {{ $request->s_latitude }}, lng: {{ $request->s_longitude }}};
    
    function initMap() {
        map = new google.maps.Map(document.getElementById('map'), {
            center: serviceLocation,
            zoom: 15
        });

        var marker = new google.maps.Marker({
            map: map,
            position: serviceLocation,
            visible: true,
            animation: google.maps.Animation.DROP,
        });
    }
</script>
<script src="https://maps.googleapis.com/maps/api/js?key={{ env('GOOGLE_API_KEY') }}&libraries=places&callback=initMap" async defer></script>
@endsection
