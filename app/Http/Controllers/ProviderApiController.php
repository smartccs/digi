<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use JWTAuth;
use Auth;
use Config;
use Tymon\JWTAuth\Exceptions\JWTException;

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

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */



    public function authenticate(Request $request)
    {
    	$this->validate($request, [
                'email' => 'required|email',
                'password' => 'required|min:6',
            ]);

        Config::set('auth.providers.users.model','App\Provider');

        // grab credentials from the request
        $credentials = $request->only('email', 'password');

        try {
            // attempt to verify the credentials and create a token for the user
            if (! $token = JWTAuth::attempt($credentials)) {
                return response()->json(['error' => 'invalid_credentials'], 401);
            }
        } catch (JWTException $e) {
            // something went wrong whilst attempting to encode the token
            return response()->json(['error' => 'could_not_create_token'], 500);
        }

        // all good so return the token
        return response()->json(compact('token'));
    }


    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */

    public function change_password(Request $request){

        $this->validate($request, [
                'password' => 'required|confirmed',
                'old_password' => 'required',
            ]);

        $Provider = \Auth::user();

        if(\Hash::check($request->old_password, $Provider->password))
        {
            $Provider->password = bcrypt($request->password);
            $Provider->save();

            return response()->json(['message' => 'Password changed successfully!']);
        } else {
            return response()->json(['error' => 'Please enter correct password']);
        }

    }


    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */

    public function details(){

        try{

        	$provider = Provider::where('providers.id' ,Auth::user()->id)
                        ->leftJoin('provider_services' , 'providers.id' , '=' , 'provider_services.provider_id')
                        ->leftJoin('service_types' , 'provider_services.service_type_id' , '=' , 'service_types.id')
                        ->select('providers.*' , 'service_types.id as service_type' , 'service_types.provider_name' , 'service_types.name as service_name')
                        ->first();

            return $provider;

        }

        catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Provider Not Found!']);
        }

    }


    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */

    public function update_profile(Request $request)
    {

        $this->validate($request, [
                'first_name' => 'required|max:255',
                'last_name' => 'max:255',
                'email' => 'email|unique:users,email,'.Auth::user()->id,
                'mobile' => 'required|digits_between:6,13',
                'picture' => 'mimes:jpeg,bmp,png',
                'gender' => 'in:male,female,others',
                'device_token' => 'required',
            ]);

         try {

            $provider = Provider::findOrFail(Auth::user()->id);

            if($request->has('first_name')) 
                $provider->first_name = $request->first_name;
            
            if($request->has('last_name')) 
                $provider->last_name = $request->last_name;
            
            if($request->has('email')) 
                $provider->email = $email;
            
            if ($request->has('mobile'))
                $provider->mobile = $mobile;

            if ($request->has('address')) 
                $provider->address = $request->address;
            
            if ($request->has('city')) 
                $provider->city = $request->city;
            
            if ($request->has('state')) 
                $provider->state = $request->state;
            
            if ($request->has('pincode')) 
                $provider->pincode = $request->pincode;
            
            if ($request->has('about')) 
                $provider->description = $request->about;
            

            if ($picture != "") {
                Helper::delete_picture($provider->picture);
                $provider->picture = Helper::upload_picture($picture);
            }

            if($request->has('gender')) 
                $provider->gender = $request->gender;
            
            $provider->save();


            if($request->has('service_type')) {

                ProviderService::where('provider_id' , Auth::user()->id)->update(['is_available' => 0]);

                if(!is_array($request->service_type)) {
                    $services = explode(',',$request->service_type );
                }


                foreach ($services as $key => $service) {

                    $check_provider_service = ProviderService::CheckService(Auth::user()->id,$service)->count();

                    if($check_provider_service > 0) {
                    	// update service type
                        save_provider_service(Auth::user()->id,$service , 1);    
                    } else {
                    	// create new service type
                        save_provider_service(Auth::user()->id,$service);
                    }
                }    
            
            }


            return response()->json(['message' => 'Profile Updated successfully!']);
        }

        catch (ModelNotFoundException $e) {
             return response()->json(['error' => 'Provider Not Found!']);
        }

    }


    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */

    public function update_location(Request $request){

        $this->validate($request, [
                'latitude' => 'required|numeric',
                'longitude' => 'required|numeric',
                'address' => 'required',
            ]);

        if($provider = Provider::find(\Auth::user()->id)){

            $provider->latitude = $request->latitude;
            $provider->longitude = $request->longitude;
            $provider->address = $request->address;
            $provider->save();

            return response()->json(['message' => 'Location Updated successfully!']);

        }else{

            return response()->json(['error' => 'Provider Not Found!']);

        }

    }



    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */

    public function available(){

    	try{
    		return Auth::user();
    	}

    	catch (ModelNotFoundException $e) {
             return response()->json(['error' => 'Provider Not Found!']);
        }

    }


    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */

    public function update_available(Request $request){

        $this->validate($request, [
                'status' => 'required|in:1,0'
            ]);

        if(Provider::where('id',Auth::user()->id)->update(['is_available' => $request->status])){

            return response()->json(['message' => 'Availability Updated successfully!']);

        }else{

            return response()->json(['error' => 'Provider Not Found!']);

        }

    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */

    public function accept(Request $request){

        $this->validate($request, [
              'request_id' => 'required|integer|exists:user_requests,id'
          ]);

        $provider = Provider::find(Auth::user()->id);
        $requests = UserRequests::find($request->request_id);

        if($requests->status == REQUEST_CANCELLED) {
            return response()->json(['error' => 'Request has not been offered to this provider. Abort.']);
        }


        $request_filter = RequestsFilter::CheckWaitingFilter($request->request_id,$provider->id)->first();

        if (!$request_filter) {
        	return response()->json(['error' => 'Request has not been offered to this provider. Abort.']);
        } 


        try{

            $requests->confirmed_provider = $provider->id;
            $requests->status = REQUEST_INPROGRESS;
            $requests->provider_status = PROVIDER_ACCEPTED;
            $requests->save();

            if($requests->later == '1')
            {
                $provider->waiting_to_respond = WAITING_TO_RESPOND_NORMAL;
                $provider->is_available = PROVIDER_AVAILABLE;
                $provider->save();
            }
            else
            {
                $provider->waiting_to_respond = WAITING_TO_RESPOND_NORMAL;
                $provider->is_available = PROVIDER_NOT_AVAILABLE;
                $provider->save();
            }
            
            // Send Push Notification to User
            // $title = Helper::tr('request_accepted_title');
            // $message = Helper::tr('request_accepted_message');

            // $this->dispatch( new sendPushNotification($requests->user_id, USER,$requests->id,$title, $message));     


            // No longer need request specific rows from RequestMeta
            RequestsFilter::where('request_id', '=', $request->request_id)->delete();

            $user = User::find($requests->user_id);
            $services = ServiceType::find($requests->request_type);

            if($requests->later == 1)
            {
                $message = "Request is Scheduled on time";
            }
            else
            {
                $message = Helper::get_message(111);
            }

			return response()->json([
					'message' => $message,
				 	'user' => $user,
				 	'request' => $requests,
				 	'service' => $services
				]);                
 
        }

        catch (ModelNotFoundException $e) {
             return response()->json(['error' => 'Unable to make the request, Please try again later']);
        }
        
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */


    public function reject(Request $request){

        $this->validate($request, [
              'request_id' => 'required|integer|exists:user_requests,id'
          ]);
            
        $provider = Provider::find($request->id);
        $requests = Requests::find($request->request_id);
        $user = User::find($requests->user_id);

        if($requests->status == REQUEST_CANCELLED) {
        	return response()->json(['error' => 'Request has not been offered to this provider. Abort.']);
    	}


        $request_filter = RequestsFilter::CheckOfferedFilter($request->request_id, $provider->id)->first();

        if (!$request_filter) {
    		return response()->json(['error' => 'Request has not been offered to this provider. Abort.']);
    	}else{
    		 $request_filter->status = REQUEST_CANCELLED;
             $request_filter->save();
    	} 

        try{

            $provider->waiting_to_respond = WAITING_TO_RESPOND_NORMAL;
            $provider->save();

            $manual_request = Settings::where('key','manual_request')->first();

            if($manual_request->manual_request == 1){
                 UserRequests::where('id', '=', $requests->id)->update(['status' => REQUEST_REJECTED_BY_PROVIDER]);
            }

            $FindNextProvider = RequestsFilter::FindNextProvider($request->request_id)->first();

            if($FindNextProvider){

            	//assigning to next provider
                Provider::where('id',$FindNextProvider->provider_id)
                ->update(['waiting_to_respond', WAITING_TO_RESPOND_NORMAL]);

                UserRequests::where('id', '=', $request->id)->update(['request_start_time' => date("Y-m-d H:i:s")]);

            } else {
                
                // Change status as no providers available in request table
                UserRequests::where('id', '=', $requests->id)->update( ['status' => REQUEST_CANCELLED] );
                RequestsFilter::where('request_id', '=', $requests->id)->delete();

            }

            return response()->json(['error' => 'Request has been Rejected.']);

        }
        
        catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Unable to make the request, Please try again later']);
        }

    }



    public function started(Request $request)
    {

        $this->validate($request, [
              'request_id' => 'required|integer|exists:user_requests,id,confirmed_provider,'.Auth::user()->id,
           ]);


        $requests = UserRequests::CheckRequestProvider($request->request_id, Auth::user()->id,PROVIDER_ACCEPTED)->first();

        if (!$requests && intval($requests->provider_status) == PROVIDER_STARTED) 
        {
        	return response()->json(['error' => 'Status is Mismatched']);
        }
                
        try{

	        $requests->status = REQUEST_INPROGRESS;
	        $requests->provider_status = PROVIDER_STARTED;
	        $requests->save();

	        // Send Push Notification to User
	        // $title = Helper::tr('provider_started_title');
	        // $message = Helper::tr('provider_started_message');

	        // $this->dispatch( new sendPushNotification($requests->user_id, USER,$requests->id,$title, $message));     
	   
	    	return response()->json(['message' => 'Provider Started', 'current_status' => PROVIDER_STARTED ]);

    	}
            
        catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Unable to make the request, Please try again later']);
        }
    }


    public function arrived(Request $request)
    {
        $this->validate($request, [
                'request_id' => 'required|integer|exists:user_requests,id,confirmed_provider,'.Auth::user()->id,
            ]);

        $requests = Requests::CheckRequestProvider($request->request_id, Auth::user()->id,PROVIDER_STARTED)
                        ->first();

    	if (!$requests && intval($requests->provider_status) == PROVIDER_ARRIVED) 
        {
        	return response()->json(['error' => 'Status is Mismatched']);
        }

	    try{

            $requests->status = REQUEST_INPROGRESS;
            $requests->user_later_status = DEFAULT_TRUE;
            $requests->provider_status = PROVIDER_ARRIVED;
            $requests->save();

            // Send Push Notification to User
            // $title = Helper::tr('provider_arrived_title');
            // $message = Helper::tr('provider_arrived_message');
            // $this->dispatch( new sendPushNotification($requests->user_id, USER,$requests->id,$title, $message));

    		return response()->json(['message' => 'Provider Arrived', 'current_status' => PROVIDER_ARRIVED ]);
        
    	}

        catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Unable to make the request, Please try again later']);
        }

    }




}
