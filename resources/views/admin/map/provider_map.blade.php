@extends('admin.layout.base')

@section('title', 'Provider Map View ')

@section('content')

    <div class="content-area py-1">
        <div class="container-fluid">
            <div class="box box-block bg-white">
                <h5 class="mb-1">Provider Map View</h5>
                <div class="row">
		            <div class="col-xs-12">
		                <div id="map"></div>
                        <div id="legend"><h3>Note: </h3></div>
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
        /*margin-left: 12px;*/
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

    #legend {
        font-family: Arial, sans-serif;
        background: rgba(255,255,255,0.8);
        padding: 10px;
        margin: 10px;
        border: 2px solid #f3f3f3;
    }
    #legend h3 {
        margin-top: 0;
        font-size: 16px;
        font-weight: bold;
        text-align: center;
    }
    #legend img {
        vertical-align: middle;
        margin-bottom: 5px;
    }
</style>
@endsection

@section('scripts')
<script>
    var map;
    var markers = [
        @foreach($Providers as $Provider)
        {
            provider_id: "{{ $Provider->id }}",
            name: "{{ $Provider->first_name }} {{ $Provider->last_name }}",
            lat: {{ $Provider->latitude }},
            lng: {{ $Provider->longitude }},
            available: "{{ $Provider->service ? $Provider->service->status : 'unactivated' }}"
        },
        @endforeach
    ];

    var mapIcons = {
        active: '{{ asset("asset/img/marker-car.png") }}',
        offline: '{{ asset("asset/img/marker-home.png") }}',
        unactivated: '{{ asset("asset/img/marker-plus.png") }}'
    }
    
    var mapMarkers = [];
    function initMap() {
        map = new google.maps.Map(document.getElementById('map'), {
            center: {lat: -34.397, lng: 150.644},
            zoom: 2,
            minZoom: 1
        });


        markers.forEach( function(element, index) {

            var url = "/admin/provider/"

            marker = new google.maps.Marker({
                position: {lat: element.lat, lng: element.lng},
                map: map,
                title: element.name,
                icon: mapIcons[element.available],
            });

            mapMarkers.push(marker);

            google.maps.event.addListener(marker, 'click', function() {
                window.location.href = url + element.provider_id;
            });

        });

        var legend = document.getElementById('legend');
        var div = document.createElement('div');
        div.innerHTML = '<img src="' + mapIcons['offline'] + '"> ' + 'Unavailable Provider';
        legend.appendChild(div);
        var div = document.createElement('div');
        div.innerHTML = '<img src="' + mapIcons['active'] + '"> ' + 'Available Provider';
        legend.appendChild(div);
        var div = document.createElement('div');
        div.innerHTML = '<img src="' + mapIcons['unactivated'] + '"> ' + 'Unactivated Provider';
        legend.appendChild(div);
        map.controls[google.maps.ControlPosition.RIGHT_BOTTOM].push(legend);

    }
</script>
<script src="https://maps.googleapis.com/maps/api/js?key={{ env('GOOGLE_MAP_KEY') }}&libraries=places&callback=initMap" async defer></script>
@endsection