@extends('admin.layout.base')

@section('title', 'Site Settings ')

@section('content')

<div class="content-area py-1">
    <div class="container-fluid">
    	<div class="box box-block bg-white">

			<h5 style="margin-bottom: 2em;">Site Settings</h5>

            <form class="form-horizontal" action="{{route('admin.setting.store')}}" method="POST" enctype="multipart/form-data" role="form">
            
            	{{csrf_field()}}
				<div class="form-group row">
					<label for="site_title" class="col-xs-2 col-form-label">Site Name</label>
					<div class="col-xs-10">
						<input class="form-control" type="text" value="{{ Setting::get('site_title', 'Tranxit')  }}" name="site_title" required id="site_title" placeholder="Site Name">
					</div>
				</div>

				<div class="form-group row">
					<label for="site_logo" class="col-xs-2 col-form-label">Site Logo</label>
					<div class="col-xs-10">
						@if(Setting::get('site_logo')!='')
	                    <img style="height: 90px; margin-bottom: 15px; border-radius:2em;" src="{{Setting::get('site_logo')}}">
	                    @endif
						<input type="file" accept="image/*" name="site_logo" class="dropify form-control-file" id="site_logo" aria-describedby="fileHelp">
					</div>
				</div>


				<div class="form-group row">
					<label for="site_icon" class="col-xs-2 col-form-label">Site Icon</label>
					<div class="col-xs-10">
						@if(Setting::get('site_icon')!='')
	                    <img style="height: 90px; margin-bottom: 15px; border-radius:2em;" src="{{Setting::get('site_icon')}}">
	                    @endif
						<input type="file" accept="image/*" name="site_icon" class="dropify form-control-file" id="site_icon" aria-describedby="fileHelp">
					</div>
				</div>

                <div class="form-group row">
                    <label for="tax_percentage" class="col-xs-2 col-form-label">Copyright Content</label>
                    <div class="col-xs-10">
                        <input class="form-control" type="number" value="{{ Setting::get('site_copyright', '&copy; 2017 Appoets') }}" name="site_copyright" id="site_copyright" placeholder="Site Copyright">
                    </div>
                </div>

				<div class="form-group row">
					<label for="play_store_link" class="col-xs-2 col-form-label">Playstore link</label>
					<div class="col-xs-10">
						<input class="form-control" type="text" value="{{ Setting::get('play_store_link', '')  }}" name="play_store_link"  id="play_store_link" placeholder="Playstore link">
					</div>
				</div>

				<div class="form-group row">
					<label for="app_store_link" class="col-xs-2 col-form-label">Appstore Link</label>
					<div class="col-xs-10">
						<input class="form-control" type="text" value="{{ Setting::get('app_store_link', '')  }}" name="app_store_link"  id="app_store_link" placeholder="Appstore link">
					</div>
				</div>

				<div class="form-group row">
					<label for="provider_select_timeout" class="col-xs-2 col-form-label">Provider Timout</label>
					<div class="col-xs-10">
						<input class="form-control" type="number" value="{{ Setting::get('provider_select_timeout', '')  }}" name="provider_select_timeout" required id="provider_select_timeout" placeholder="Provider Timout">
					</div>
				</div>

				<div class="form-group row">
					<label for="search_radius" class="col-xs-2 col-form-label">Search Radius</label>
					<div class="col-xs-10">
						<input class="form-control" type="number" value="{{ Setting::get('search_radius', '')  }}" name="search_radius" required id="search_radius" placeholder="Search Radius">
					</div>
				</div>

				<div class="form-group row">
					<label for="tax_percentage" class="col-xs-2 col-form-label">Tax percentage(%)</label>
					<div class="col-xs-10">
						<input class="form-control" type="number" value="{{ Setting::get('tax_percentage', '')  }}" name="tax_percentage"  id="tax_percentage" max="100" min="0" placeholder="Tax percentage">
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
