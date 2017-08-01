@extends('admin.layout.base')

@section('title', 'Push Notification ')

@section('content')

    <div class="content-area py-1">
        <div class="container-fluid">
            <div class="box box-block bg-white">
            	<h5 style="margin-bottom: 2em;">Push Notification</h5>

	            <form class="form-horizontal" action="{{route('admin.send.push')}}" method="POST" role="form">
	            
	            	{{csrf_field()}}

	            	<div class="form-group row">
						<label for="content" class="col-xs-2 col-form-label">Target Segment</label>
						<div class="col-xs-10">
							<select class="form-control" name="segment">
								<option value="users">All Users</option>
								<option value="providers">All Providers</option>
							</select>
						</div>
					</div>

					<div class="form-group row">
						<label for="message" class="col-xs-2 col-form-label">Message</label>
						<div class="col-xs-10">
							<textarea class="form-control" cols="10" rows="10" name="message" required id="message" placeholder="Enter Message"></textarea>
						</div>
					</div>

					<div class="form-group row">
						<label for="zipcode" class="col-xs-2 col-form-label"></label>
						<div class="col-xs-10">
							<button type="submit" class="btn btn-primary">Send Now</button>
						</div>
					</div>

				</form>

            </div>
        </div>
    </div>

@endsection
