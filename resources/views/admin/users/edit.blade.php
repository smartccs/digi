@extends('admin.layout.base')

@section('title', 'Update Users ')

@section('content')

<div class="content-area py-1">
    <div class="container-fluid">
    	<div class="box box-block bg-white">
    	    <a href="{{ route('admin.user.index') }}" class="btn btn-default pull-right"><i class="fa fa-angle-left"></i> Back</a>

			<h5 style="margin-bottom: 2em;">Update User</h5>

            <form class="form-horizontal" action="{{route('admin.user.update', $user->id )}}" method="POST" enctype="multipart/form-data" role="form">
            	{{csrf_field()}}
            	<input type="hidden" name="_method" value="PATCH">
				<div class="form-group row">
					<label for="first_name" class="col-xs-2 col-form-label">First Name</label>
					<div class="col-xs-10">
						<input class="form-control" type="text" value="{{ $user->first_name }}" name="first_name" required id="first_name" placeholder="First Name">
					</div>
				</div>

				<div class="form-group row">
					<label for="last_name" class="col-xs-2 col-form-label">Last Name</label>
					<div class="col-xs-10">
						<input class="form-control" type="text" value="{{ $user->last_name }}" name="last_name" required id="last_name" placeholder="Last Name">
					</div>
				</div>

				<fieldset class="form-group row">
					<legend class="col-form-legend col-sm-2">Gender</legend>
					<div class="col-sm-10">
						<div class="form-check">
							<label class="form-check-label">
								<input class="form-check-input" type="radio" name="gender" value="male" @if($user->gender == 'male') checked @endif >
								Male
							</label>
						</div>
						<div class="form-check">
							<label class="form-check-label">
								<input class="form-check-input" type="radio" name="gender" value="female" @if($user->gender == 'female') checked @endif >
								Female
							</label>
						</div>
						<div class="form-check">
							<label class="form-check-label">
								<input class="form-check-input" type="radio" name="gender" value="others" @if($user->gender == 'others') checked @endif >
								Others
							</label>
						</div>
					</div>
				</fieldset>


				<div class="form-group row">
					
					<label for="picture" class="col-xs-2 col-form-label">Picture</label>
					<div class="col-xs-10">
					@if(isset($user->picture))
                    	<img style="height: 90px; margin-bottom: 15px; border-radius:2em;" src="{{$user->picture}}">
                    @endif
						<input type="file" accept="image/*" name="picture" class="form-control-file" id="picture" aria-describedby="fileHelp">
					</div>
				</div>

				<div class="form-group row">
					<label for="mobile" class="col-xs-2 col-form-label">Mobile</label>
					<div class="col-xs-10">
						<input class="form-control" type="number" value="{{ $user->mobile }}" name="mobile" required id="mobile" placeholder="Mobile">
					</div>
				</div>

				<div class="form-group row">
					<label for="address" class="col-xs-2 col-form-label">Address</label>
					<div class="col-xs-10">
					 	<textarea name="address" required id="address" class="form-control" rows="3">{{$user->address}}</textarea>
					</div>
				</div>

				<div class="form-group row">
					<label for="zipcode" class="col-xs-2 col-form-label">Zipcode</label>
					<div class="col-xs-10">
						<input class="form-control" type="number" value="{{ $user->zipcode }}" name="zipcode" required id="zipcode" placeholder="Zipcode">
					</div>
				</div>
				<div class="form-group row">
					<label for="zipcode" class="col-xs-2 col-form-label"></label>
					<div class="col-xs-10">
						<button type="submit" class="btn btn-primary">Update User</button>
					</div>
				</div>
			</form>
		</div>
    </div>
</div>

@endsection
