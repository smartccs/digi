<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;


define('USER', 0);
define('PROVIDER',1);
define('NONE', 0);
define('DEFAULT_FALSE', 0);
define('DEFAULT_TRUE', 1);

// Payment Constants
define('COD',   'cod');
define('PAYPAL', 'paypal');
define('CARD',  'card');

define('REQUEST_NEW',        0);
define('REQUEST_WAITING',      1);
define('REQUEST_INPROGRESS',    2);
define('REQUEST_COMPLETE_PENDING',  3);
define('REQUEST_RATING',      4);   
define('REQUEST_COMPLETED',      5);
define('REQUEST_CANCELLED',      6);
define('REQUEST_NO_PROVIDER_AVAILABLE',7);
define('WAITING_FOR_PROVIDER_CONFRIMATION_COD',  8);


// Only when manual request
define('REQUEST_REJECTED_BY_PROVIDER', 9);

define('PROVIDER_NOT_AVAILABLE', 0);
define('PROVIDER_AVAILABLE', 1);

define('PROVIDER_NONE', 0);
define('PROVIDER_ACCEPTED', 1);
define('PROVIDER_STARTED', 2);
define('PROVIDER_ARRIVED', 3);
define('PROVIDER_SERVICE_STARTED', 4);
define('PROVIDER_SERVICE_COMPLETED', 5);
define('PROVIDER_RATED', 6);

define('REQUEST_META_NONE',   0);
define('REQUEST_META_OFFERED',   1);
define('REQUEST_META_TIMEDOUT', 2);
define('REQUEST_META_DECLINED', 3);

define('RATINGS', '0,1,2,3,4,5');

define('DEVICE_ANDROID', 'android');
define('DEVICE_IOS', 'ios');

define('WAITING_TO_RESPOND', 1);
define('WAITING_TO_RESPOND_NORMAL',0);

define('PROVIDER_AVAILABILITY_FREE' , 0);
define('PROVIDER_AVAILABILITY_SET' , 1);
define('PROVIDER_AVAILABILITY_BOOKED' , 2);



use App\Helpers\Helper;
use Log;
use Hash;
use Validator;
use DB;
use App\Admin;
use App\User;
use App\Provider;
use App\ProviderService;
use App\ServiceType;
use App\UserRequests;
use App\RequestsFilter;
use App\UserPayment;
use App\Settings;
use App\ProviderRating;
use App\Cards;
use App\ChatMessage;
use App\UserRating;
use App\ProviderAvailability;

class ProviderApiController extends Controller
{
    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */

    public function signup(Request $request)
    {
        $this->validate($request, [
                'social_unique_id' => ['required_if:login_by,facebook,google','unique:providers'],
                'device_type' => 'required|in:android,ios',
                'device_token' => 'required',
                'login_by' => 'required|in:manual,facebook,google',
                'first_name' => 'required|max:255',
                'last_name' => 'required|max:255',
                'email' => 'required|email|max:255|unique:providers',
                'mobile' => 'required|digits_between:6,13',
                'password' => 'required|min:6',
                'picture' => 'required|mimes:jpeg,jpg,bmp,png',
            ]);

        try{

	        $Provider = $request->all();

	        $Provider['is_available'] = 1;
	        $Provider['is_activated'] = 1;
	        $Provider['is_email_activated'] = 1;
	        $Provider['email_activation_code'] = uniqid();

	        $Provider['password'] = bcrypt($request->password);
	        if($request->hasFile('picture')) {
	            $Provider['picture'] = Helper::upload_picture($request->avatar);
	        }

	        $Provider = Provider::create($Provider);

	            if($Provider) {

	                if($request->has('service_type')) {

	                    $provider_services = ProviderService::where('provider_id' , $Provider->id)->get();

	                    ProviderService::where('provider_id' , $Provider->id)->update(['is_available' => 0]);

	                    $services =  array($request->service_type);

	                    if(!is_array($request->service_type)) {
	                        $services = explode(',',$request->service_type );
	                    }

	                    if($services) {
	                        foreach ($services as $key => $service) {
	                            $check_provider_service = ProviderService::where('provider_id' , $Provider->id)->where('service_type_id' , $service)->count();

	                            if($check_provider_service) {
	                                Helper::save_provider_service($Provider->id,$service , 1);    
	                            } else {
	                                Helper::save_provider_service($Provider->id,$service);
	                            }
	                        }    
	                    
	                    }
	                }
	            }

	        return $Provider;

    	}

    	catch (ModelNotFoundException $e) {
             return response()->json(['error' => 'Something Went Wrong!']);
        }
        
    }


}
