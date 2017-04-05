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

			<div class="box box-block bg-white">

			<h5 style="margin-bottom: 2em;">Site Settings</h5>

            <form class="form-horizontal" action="{{route('admin.setting.store')}}" method="POST" enctype="multipart/form-data" role="form">
            
            	{{csrf_field()}}


				<div class="form-group row">
					<label for="daily_target" class="col-xs-2 col-form-label">Daily Target</label>
					<div class="col-xs-10">
						<input class="form-control" type="number" value="{{ Setting::get('daily_target', '')  }}" name="daily_target" required id="daily_target" placeholder="Daily Target">
					</div>
				</div>

				<div class="form-group row">
					<label for="tax_percentage" class="col-xs-2 col-form-label">Tax percentage(%)</label>
					<div class="col-xs-10">
						<input class="form-control" type="number" value="{{ Setting::get('tax_percentage', '')  }}" name="tax_percentage"  id="tax_percentage" max="100" min="0" placeholder="Tax percentage">
					</div>
				</div>

				<div class="form-group row">
					<label for="surge_trigger" class="col-xs-2 col-form-label">Surge Trigger Point</label>
					<div class="col-xs-10">
						<input class="form-control" type="number" value="{{ Setting::get('surge_trigger', '')  }}" name="surge_trigger" required id="surge_trigger" placeholder="Surge Trigger Point">
					</div>
				</div>


				<div class="form-group row">
					<label for="surge_percentage" class="col-xs-2 col-form-label">Surge percentage(%)</label>
					<div class="col-xs-10">
						<input class="form-control" type="number" value="{{ Setting::get('surge_percentage', '')  }}" name="surge_percentage"  id="surge_percentage" max="100" min="0" placeholder="Surge percentage">
					</div>
				</div>


				<div class="form-group row">
					<label for="commission_percentage" class="col-xs-2 col-form-label">Commission percentage(%)</label>
					<div class="col-xs-10">
						<input class="form-control" type="number" value="{{ Setting::get('commission_percentage', '')  }}" name="commission_percentage" max="100" min="0"  id="commission_percentage" placeholder="Commission percentage">
					</div>
				</div>

				<div class="form-group row">
					<label for="base_price" class="col-xs-2 col-form-label">
						Currency ( <strong>{{ Setting::get('currency', '')  }} </strong>)
					</label>
					<div class="col-xs-10">
						<select name="currency" value="" required class="form-control">
	                      @if(Setting::get('currency')=='')
	                      <option value="">Select Currency</option>
	                      @endif
	                      <option @if(Setting::get('currency') == "$") selected @endif value="$">US Dollar (USD)</option>
	                      <option @if(Setting::get('currency') == "₹") selected @endif value="₹"> Indian Rupee (INR)</option>
	                      <option @if(Setting::get('currency') == "د.ك") selected @endif value="د.ك">Kuwaiti Dinar (KWD)</option>
	                      <option @if(Setting::get('currency') == "د.ب") selected @endif value="د.ب">Bahraini Dinar (BHD)</option>
	                      <option @if(Setting::get('currency') == "﷼") selected @endif value="﷼">Omani Rial (OMR)</option>
	                      <option @if(Setting::get('currency') == "£") selected @endif value="£">British Pound (GBP)</option>
	                      <option @if(Setting::get('currency') == "€") selected @endif value="€">Euro (EUR)</option>
	                      <option @if(Setting::get('currency') == "CHF") selected @endif value="CHF">Swiss Franc (CHF)</option>
	                      <option @if(Setting::get('currency') == "ل.د") selected @endif value="ل.د">Libyan Dinar (LYD)</option>
	                      <option @if(Setting::get('currency') == "B$") selected @endif value="B$">Bruneian Dollar (BND)</option>
	                      <option @if(Setting::get('currency') == "S$") selected @endif value="S$">Singapore Dollar (SGD)</option>
	                      <option @if(Setting::get('currency') == "AU$") selected @endif value="AU$"> Australian Dollar (AUD)</option>
                      </select>
					</div>
				</div>


				<div class="form-group row">
					<label for="zipcode" class="col-xs-2 col-form-label"></label>
					<div class="col-xs-10">
						<button type="submit" class="btn btn-primary">Update Site Settings</button>
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