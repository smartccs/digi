@extends('admin.layout.base')

@section('title', 'Add Service Type ')

@section('content')

<div class="content-area py-1">
    <div class="container-fluid">
    	<div class="box box-block bg-white">
            <a href="{{ route('admin.service.index') }}" class="btn btn-default pull-right"><i class="fa fa-angle-left"></i> Back</a>

			<h5 style="margin-bottom: 2em;">Add Service Type</h5>

            <form class="form-horizontal" action="{{route('admin.service.store')}}" method="POST" enctype="multipart/form-data" role="form">
            	{{csrf_field()}}
				<div class="form-group row">
					<label for="name" class="col-xs-12 col-form-label">Service Name</label>
					<div class="col-xs-10">
						<input class="form-control" type="text" value="{{ old('name') }}" name="name" required id="name" placeholder="Service Name">
					</div>
				</div>

				<div class="form-group row">
					<label for="provider_name" class="col-xs-12 col-form-label">Provider Name</label>
					<div class="col-xs-10">
						<input class="form-control" type="text" value="{{ old('provider_name') }}" name="provider_name" required id="provider_name" placeholder="Provider Name">
					</div>
				</div>

				<div class="form-group row">
					<label for="picture" class="col-xs-12 col-form-label">Service Image</label>
					<div class="col-xs-10">
						<input type="file" accept="image/*" name="image" class="dropify form-control-file" id="picture" aria-describedby="fileHelp">
					</div>
				</div>

				<div class="form-group row">
					<label for="fixed" class="col-xs-12 col-form-label">Fixed</label>
					<div class="col-xs-10">
						<input class="form-control" type="text" value="{{ old('fixed') }}" name="fixed" required id="fixed" placeholder="Fixed">
					</div>
				</div>

				<div class="form-group row">
					<label for="price" class="col-xs-12 col-form-label">Price</label>
					<div class="col-xs-10">
						<input class="form-control" type="text" value="{{ old('price') }}" name="price" required id="price" placeholder="Price">
					</div>
				</div>

				<div class="form-group row">
					<label class="col-sm-2"></label>
					<div class="col-sm-10">
						<div class="form-check">
							<label class="form-check-label">
								<input class="form-check-input" name="is_default" type="checkbox"> Set Default
							</label>
						</div>
					</div>
				</div>

				<div class="form-group row">
					<label for="zipcode" class="col-xs-2 col-form-label"></label>
					<div class="col-xs-10">
						<button type="submit" class="btn btn-primary">Add Service Type</button>
						<a href="{{route('admin.service.index')}}" class="btn btn-default">Cancel</a>
					</div>
				</div>
			</form>
		</div>
    </div>
</div>

@endsection
