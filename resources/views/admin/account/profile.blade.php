@extends('admin.layout.base')

@section('title', 'Update Profile ')

@section('content')

<div class="content-area py-1">
    <div class="container-fluid">
    	<div class="box box-block bg-white">

			<h5 style="margin-bottom: 2em;">Update Profile</h5>

            <form class="form-horizontal" action="{{route('admin.profile.update')}}" method="POST" enctype="multipart/form-data" role="form">
            	{{csrf_field()}}

				<div class="form-group row">
					<label for="name" class="col-xs-2 col-form-label">Name</label>
					<div class="col-xs-10">
						<input class="form-control" type="text" value="{{ Auth::guard('admin')->user()->name }}" name="name" required id="name" placeholder=" Name">
					</div>
				</div>


				<fieldset class="form-group row">
					<legend class="col-form-legend col-sm-2">Gender</legend>
					<div class="col-sm-10">
						<div class="form-check">
							<label class="form-check-label">
								<input class="form-check-input" type="radio" name="gender" value="male" 
									@if(Auth::guard('admin')->user()->gender == 'male') 
										checked 
									@endif>
								Male
							</label>
						</div>
						<div class="form-check">
							<label class="form-check-label">
								<input class="form-check-input" type="radio" name="gender" value="female" 
									@if(Auth::guard('admin')->user()->gender == 'female')
										checked 
									@endif>
								Female
							</label>
						</div>
						<div class="form-check">
							<label class="form-check-label">
								<input class="form-check-input" type="radio" name="gender" value="others" 
									@if(Auth::guard('admin')->user()->gender == 'others') 
										checked 
									@endif>
								Others
							</label>
						</div>
					</div>
				</fieldset>

				<div class="form-group row">
					<label for="email" class="col-xs-2 col-form-label">Email</label>
					<div class="col-xs-10">
						<input class="form-control" type="email" required name="email" value="{{ isset(Auth::guard('admin')->user()->email) ? Auth::guard('admin')->user()->email : '' }}" id="email" placeholder="Email">
					</div>
				</div>

				<div class="form-group row">
					<label for="picture" class="col-xs-2 col-form-label">Picture</label>
					<div class="col-xs-10">
						@if(isset(Auth::guard('admin')->user()->picture))
	                    	<img style="height: 90px; margin-bottom: 15px; border-radius:2em;" src="{{Auth::guard('admin')->user()->picture}}">
	                    @endif
						<input type="file" accept="image/*" name="picture" class=" dropify form-control-file" aria-describedby="fileHelp">
					</div>
				</div>

				<div class="form-group row">
					<label for="mobile" class="col-xs-2 col-form-label">Mobile</label>
					<div class="col-xs-10">
						<input class="form-control" type="number" value="{{ isset(Auth::guard('admin')->user()->mobile) ? Auth::guard('admin')->user()->mobile : '' }}" name="mobile" required id="mobile" placeholder="Mobile">
					</div>
				</div>

				<div class="form-group row">
					<label for="zipcode" class="col-xs-2 col-form-label"></label>
					<div class="col-xs-10">
						<button type="submit" class="btn btn-primary">Update Profile</button>
					</div>
				</div>
			</form>
		</div>
    </div>
</div>

@endsection
