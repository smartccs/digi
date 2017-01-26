@extends('admin.layout.base')

@section('title', 'Update Promocode ')

@section('content')

<div class="content-area py-1">
    <div class="container-fluid">
    	<div class="box box-block bg-white">
    	    <a href="{{ route('admin.promocode.index') }}" class="btn btn-default pull-right"><i class="fa fa-angle-left"></i> Back</a>

			<h5 style="margin-bottom: 2em;">Update Promocode</h5>

            <form class="form-horizontal" action="{{route('admin.promocode.update', $promocode->id )}}" method="POST" enctype="multipart/form-data" role="form">
            	{{csrf_field()}}
            	<input type="hidden" name="_method" value="PATCH">
				<div class="form-group row">
					<label for="promo_code" class="col-xs-2 col-form-label">Promocode</label>
					<div class="col-xs-10">
						<input class="form-control" type="text" value="{{ $promocode->promo_code }}" name="promo_code" required id="promo_code" placeholder="Promocode">
					</div>
				</div>

				<div class="form-group row">
					<label for="offer" class="col-xs-2 col-form-label">Offer Amount</label>
					<div class="col-xs-10">
						<input class="form-control" type="number" value="{{ $promocode->offer }}" name="offer" required id="offer" placeholder="Offer Amount">
					</div>
				</div>

				<div class="form-group row">
					<label class="col-sm-2"></label>
					<div class="col-sm-10">
						<div class="form-check">
							<label class="form-check-label">
								<input class="form-check-input" value="1" name="is_valid" @if($promocode->is_valid ==1) checked  @else  @endif  value="1"  type="checkbox"> Set Default
							</label>
						</div>
					</div>
				</div>

				<div class="form-group row">
					<label for="zipcode" class="col-xs-2 col-form-label"></label>
					<div class="col-xs-10">
						<button type="submit" class="btn btn-primary">Update Promocode</button>
					</div>
				</div>
			</form>
		</div>
    </div>
</div>

@endsection
