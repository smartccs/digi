@extends('user.layout.base')

@section('title', 'Ride Confirmation ')

@section('content')

<div class="col-md-9">
    <div class="dash-content">
		<div class="row no-margin">
		    <div class="col-md-12">
		        <h4 class="page-title">@lang('messages.finding_driver')</h4>
		    </div>
		</div>

		<div class="row no-margin">
		    <form action="user-provider-after-accept.html">
		        <div class="col-md-6">
		            <div class="status">
		                <h6>Status</h6>
		                <p>Waiting for Provider to accept...</p>
		            </div>

		            <button type="submit" class="full-primary-btn fare-btn">Cancel Request</button>                                
		        </div>
		        <div class="col-md-6">
		            <dl class="dl-horizontal left-right">
		                <dt>Request ID</dt>
		                <dd>2596</dd>
		                <dt>Time</dt>
		                <dd>10-5-17 08:26:55 PM</dd>                                    
		            </dl> 
		            <div class="user-request-map">
		                <div class="from-to row no-margin">
		                    <div class="from">
		                        <h5>FROM</h5>
		                        <p>620 Alice St, Mountain Home, AR, 72653</p>
		                    </div>
		                    <div class="to">
		                        <h5>TO</h5>
		                        <p>2290 N Koolridge Way, Chino Valley, AZ, 86323</p>
		                    </div>
		                    <div class="type">
		                        <h5>Type : XUV</h5>
		                    </div>
		                </div>
		                <div class="map-responsive-trip">
		                    <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d386950.6511603643!2d-73.70231446529533!3d40.738882125234106!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x89c24fa5d33f083b%3A0xc80b8f06e177fe62!2sNueva+York!5e0!3m2!1ses-419!2sus!4v1445032011908" width="600" height="450" frameborder="0" style="border:0" allowfullscreen></iframe>
		                </div>                                
		            </div>                          
		        </div>
		    </form>
		</div>
	</div>
</div>

@endsection