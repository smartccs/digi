@extends('admin.layout.base')

@section('title', 'Payment Settings ')

@section('content')

    <div class="content-area py-1">
        <div class="container-fluid">
            <div class="box box-block bg-white">
				<h5>Payment Settings</h5>
				<form action="{{route('admin.setting.store')}}" method="POST">
					{{csrf_field()}}
					<div class="card card-block card-inverse card-primary text-xs-center">
						<blockquote class="card-blockquote">
							<br>
							<div class="form-group row">
								<label for="stripe_secret_key" class="col-xs-4 col-form-label"><i style="font-size: 24px;" class="fa fa-cc-stripe"></i> ON/OFF</label>
								<div class="col-xs-8">
									<div class="float-xs-left mr-1"><input @if(Setting::get('CARD') == 1) checked  @endif  name="CARD" id="stripe_check" onchange="cardselect()" type="checkbox" class="js-switch" data-color="#43b968"></div>
								</div>
							</div>
							<div id="card_field" @if(Setting::get('CARD') == 0) style="display: none;" @endif>
								<div class="form-group row">
									<label for="stripe_secret_key" class="col-xs-4 col-form-label">Stripe Secret key</label>
									<div class="col-xs-8">
										<input class="form-control" type="text" value="{{Setting::get('stripe_secret_key', '') }}" name="stripe_secret_key" id="stripe_secret_key"  placeholder="Stripe Secret key">
									</div>
								</div>
								<div class="form-group row">
									<label for="stripe_publishable_key" class="col-xs-4 col-form-label">Stripe Publishable key</label>
									<div class="col-xs-8">
										<input class="form-control" type="text" value="{{Setting::get('stripe_publishable_key', '') }}" name="stripe_publishable_key" id="stripe_publishable_key"  placeholder="Stripe Publishable key">
									</div>
								</div>
							</div>

						</blockquote>
						<br>
						<br>
						<div class="row">
							<div class="col-md-5">
			                	<button class="pull-right btn btn-default mr10" type="submit">Submit</button>
							</div>
						</div>

					</div>

				</form>

			</div>
        </div>
    </div>
@endsection

@section('scripts')
<script type="text/javascript">
function cardselect()
{
    if($('#stripe_check').is(":checked"))   
        $("#card_field").fadeIn(700);
    else
        $("#card_field").fadeOut(700);
}
</script>
<script type="text/javascript">
function paypalselect()
{
    if($('#paypal_check').is(":checked"))   
        $("#paypal_field").fadeIn(700);
    else
        $("#paypal_field").fadeOut(700);
}
</script>

@endsection