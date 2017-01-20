<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use App\Helpers\Helper;

use Log;
use Hash;
use Validator;
use DB;
use Auth;
use Config;

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
            
        $provider = Provider::find(Auth::user()->id);
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

                UserRequests::where('id', '=', $request->request_id)->update(['request_start_time' => date("Y-m-d H:i:s")]);

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

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */

    public function started(Request $request){

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

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */

    public function arrived(Request $request){

        $this->validate($request, [
                'request_id' => 'required|integer|exists:user_requests,id,confirmed_provider,'.Auth::user()->id,
            ]);

        $requests = UserRequests::CheckRequestProvider($request->request_id, Auth::user()->id,PROVIDER_STARTED)
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

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */


    public function start_service(Request $request){

        $this->validate($request, [
             'request_id' => 'required|integer|exists:user_requests,id,confirmed_provider,'.Auth::user()->id,
          ]);


        $requests = UserRequests::CheckRequestProvider($request->request_id, Auth::user()->id,PROVIDER_ARRIVED)
                        ->first();

        if (!$requests && intval($requests->provider_status) == PROVIDER_SERVICE_STARTED) 
        {
        	return response()->json(['error' => 'Status is Mismatched']);
        }


        try{

            if($request->hasFile('before_image'))
            {
                $image = $request->file('before_image');
                $requests->before_image = Helper::upload_picture($image);
            }

            $requests->start_time = date("Y-m-d H:i:s");
            $requests->status = REQUEST_INPROGRESS;
            $requests->user_later_status = DEFAULT_FALSE;
            $requests->provider_status = PROVIDER_SERVICE_STARTED;
            $requests->save();

            // Send Push Notification to User
            // $title = Helper::tr('request_started_title');
            // $message = Helper::tr('request_started_message');
            // $this->dispatch( new sendPushNotification($requests->user_id, USER,$requests->id,$title, $message));

    		return response()->json(['message' => 'Service Started', 'current_status' => PROVIDER_SERVICE_STARTED ]);

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

    public function end_service(Request $request){

        $this->validate($request, [
            'request_id' => 'required|integer|exists:user_requests,id,confirmed_provider,'.Auth::user()->id,
          ]);

        $requests = UserRequests::CheckRequestProvider($request->request_id, Auth::user()->id,PROVIDER_SERVICE_STARTED)
                        ->first();

        if (!$requests && intval($requests->provider_status) == PROVIDER_SERVICE_COMPLETED) 
        {
        	return response()->json(['error' => 'Status is Mismatched']);
        }


        try {

	            if($request->hasFile('after_image'))
	            {
	                $image = $request->file('after_image');
	                $requests->after_image = Helper::upload_picture($image);
	            }

	            $requests->status = REQUEST_COMPLETE_PENDING;
	            $requests->end_time = date("Y-m-d H:i:s");
	            $requests->provider_status = PROVIDER_SERVICE_COMPLETED;
	            $requests->save();


	            $base_price = $price_per_minute = $tax_price = $total_time = $total = 0;
	            
	            // Invoice details

	            $base = Settings::where('key' , 'base_price')->first();
	            $base_price = $base->value;

	            $price_minute = ServiceType::find($requests->request_type);
	            $price_per_minute = $price_minute->service_price;

	            $admin_tax = Settings::where('key','tax_price')->first();
	            $tax_price = $admin_tax->value;

	            $get_time = Helper::time_diff($requests->start_time,$requests->end_time);
	            $total_time = $get_time->i;

	            $total = ($total_time * $price_per_minute) + $base_price;

	            if($requests->is_promo_code) {
	                
	                if($requests->offer_amount < $total ) {
	                    $total = $total - $requests->offer_amount;    
	                } else {
	                    $total = 0.00;
	                }
	            
	            }

	            // get payment mode from user table.
	            $user_payment_mode = $card_token = $customer_id = $last_four = "";

	            $user = User::find($requests->user_id);

	            if($user) {

	                $user_payment_mode = $user->payment_mode;

	                if($user_payment_mode == CARD) {
	                    if($user_card = Cards::find($user->default_card)) {
	                        $card_token = $user_card->card_token;
	                        $customer_id = $user_card->customer_id;
	                        $last_four = $user_card->last_four;
	                    }
	                }
	            }

	            // Save the payment details
	            if(!UserPayment::where('request_id' , $requests->id)->first()) {
	                $request_payment = new UserPayment;
	                $request_payment->request_id = $requests->id;
	                $request_payment->payment_mode = $user_payment_mode;
	                $request_payment->base_price = $base_price;
	                $request_payment->time_price = $total_time_price;
	                $request_payment->tax_price = $tax_price;
	                $request_payment->total_time = $total_time;
	                $request_payment->total = $total;

	                if($requests->is_promo_code) {
	                    $request_payment->promo_code = $requests->promo_code;
	                    $request_payment->promo_code_id = $requests->promo_code_id;
	                    $request_payment->offer_amount = $requests->offer_amount;
	                }

	                $request_payment->save();
	            }

	            UserRequests::where('id',$requests->id)->update(['amount' => $total]);

	            $invoice_data = [];

	            $provider = Provider::find($requests->confirmed_provider);

	            $invoice_data['request_id'] = $requests->id;
	            $invoice_data['user_id'] = $requests->user_id;
	            $invoice_data['provider_id'] = $requests->confirmed_provider;
	            $invoice_data['provider_name'] = $provider->first_name." ".$provider->last_name;
	            $invoice_data['provider_address'] = $provider->address;
	            $invoice_data['user_name'] = $user->first_name." ".$user->last_name;
	            $invoice_data['user_address'] = $requests->s_address;
	            $invoice_data['base_price'] = $base_price;
	            $invoice_data['other_price'] = 0;
	            $invoice_data['total_time_price'] = $total_time_price;
	            $invoice_data['sub_total'] = $total_time_price + $base_price;
	            $invoice_data['tax_price'] = $tax_price;
	            $invoice_data['total'] = $total;
	            $invoice_data['payment_mode'] = $user_payment_mode;
	            $invoice_data['payment_mode_status'] = $user_payment_mode ? 1 : 0;
	            $invoice_data['bill_no'] = "Not paid";
	            $invoice_data['card_token'] = $card_token;
	            $invoice_data['customer_id'] = $customer_id;
	            $invoice_data['last_four'] = $last_four;

	            // Send Push Notification to User
	            // $title = Helper::tr('request_complete_payment_title');
	            // $message = $invoice_data;

	            // $this->dispatch( new sendPushNotification($requests->user_id, USER,$requests->id,$title, $message));

	            // // Send invoice notification to the user and provider
	            // $subject = Helper::tr('request_completed_invoice');
	            // $email = Helper::get_emails(3,$requests->user_id,$requests->confirmed_provider);
	            // $page = "emails.provider.invoice";
	            // $email_send = Helper::send_email($page,$subject,$user->email,$invoice_data);


	    		return response()->json([
	    			'message' => 'Service Completed',
	    		 	'current_status' => REQUEST_RATING,
	    		  	'invoice' => $invoice_data 
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

    public function rate_user(Request $request){

        $this->validate($request, [
                'request_id' => 'required|integer|exists:user_requests,id,confirmed_provider,'.Auth::user()->id.'|unique:provider_ratings,request_id',
                'rating' => 'integer|in:'.RATINGS,
                'comments' => 'max:255'
            ]);

        $requests = UserRequests::where('id' ,$request->request_id)
                ->whereIn('status' , [REQUEST_COMPLETE_PENDING,REQUEST_RATING,REQUEST_COMPLETED])
                ->where('provider_status' , PROVIDER_SERVICE_COMPLETED)
                ->first();

        if (!$requests && intval($req->provider_status) == PROVIDER_RATED) {
        	return response()->json(['error' => 'Request is already Completed']);
        } 
                
        try{

            if($request->has('rating')) {
                $rev_user = new ProviderRating();
                $rev_user->provider_id = $req->confirmed_provider;
                $rev_user->user_id = $req->user_id;
                $rev_user->request_id = $req->id;
                $rev_user->rating = $request->rating;
                $rev_user->comment = $request->comments ?: '';
                $rev_user->save();
            }

            $requests->provider_status = PROVIDER_RATED;
            $requests->save();

            Provider::where('id',$requests->confirmed_provider)->update(['is_available' => PROVIDER_AVAILABLE]);

            // Send Push Notification to User
            // $title = Helper::tr('user_rated_by_provider_title');
            // $message = Helper::tr('user_rated_by_provider_title');
            // $this->dispatch( new sendPushNotification($requests->user_id, USER,$requests->id,$title, $message));     

            return response()->json([
    			'message' => 'User Rated',
    		 	'current_status' => REQUEST_COMPLETE_PENDING,
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

    public function cancel_request(Request $request){

       	$this->validate($request, [
            'request_id' => 'required|numeric|exists:user_requests,id,confirmed_provider,'.Auth::user()->id,
         ]);


        $requests = Requests::find($request->request_id);

        if($requests->status == REQUEST_CANCELLED)
        {
        	return response()->json(['error' => 'Request Already Cancelled']);
    	}

        if( in_array($requests->provider_status, PROVIDER_NONE,PROVIDER_ACCEPTED,PROVIDER_STARTED]) )
        {
        	return response()->json(['error' => 'Service Already in Progress']);
        }

        try{

            $requests->status = REQUEST_CANCELLED;
            $requests->save();

            // $title = Helper::tr('cancel_by_provider_title');
            // $message = Helper::tr('cancel_by_provider_message');
            // $this->dispatch(new sendPushNotification($requests->user_id,USER,$requests->id,$title,$message));

            if($requests->confirmed_provider != DEFAULT_FALSE){
                Provider::where('id',$requests->confirmed_provider)->update(['is_available' => PROVIDER_AVAILABLE]);
            }

            RequestsFilter::where('request_id', '=', $request->request_id)->delete();

            // $email_data = array();
            // $email_data['provider_name'] = $email_data['username'] = "";
            //  if($user = User::find($requests->user_id)) {
            //     $email_data['username'] = $user->first_name." ".$user->last_name;    
            // }
            // if($provider = Provider::find($requests->confirmed_provider)) {
            //     $email_data['provider_name'] = $provider->first_name. " " . $provider->last_name;
            // }
            // $subject = Helper::tr('request_cancel_provider');
            // $page = "emails.provider.request_cancel";
            // Helper::send_email($page,$subject,$user->email,$email_data);

            return response()->json([
    			'message' => 'Request Cancelled',
    		 	'current_status' => REQUEST_CANCELLED,
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

    public function history(){

    	try{

	        $requests = UserRequests::GetProviderHistory(Auth::user()->id)->get()->toArray();
	        return $requests;
	    }
            
        catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Something went wrong']);
        }

    }


    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */

    public function incoming_request(){

    	try{

	        $request_meta = RequestsFilter::IncomingRequest(Auth::user()->id)->get()->toArray();

	        $settings = Settings::where('key', 'provider_select_timeout')->first();
	        $provider_timeout = $settings->value;

	        $request_meta_data = array();
	        foreach($request_meta as $each_request_meta){
	            $each_request_meta['user_rating'] = ProviderRating::Average($each_request_meta['user_id']) ?: 0;
	            $time_left_to_respond = $provider_timeout - (time() - strtotime($each_request_meta['request_start_time']) );
	            $each_request_meta['time_left_to_respond'] = $time_left_to_respond;
	            if($time_left_to_respond < 0) {
	                Helper::assign_next_provider($each_request_meta['request_id'],Auth::user()->id);
	            }
	            $request_meta_data[] = $each_request_meta;
	        }

	        return $request_meta_data;
 		}
            
        catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Something went wrong']);
        }

    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */

    public function request_status_check(){

        $check_status = [REQUEST_COMPLETED,REQUEST_CANCELLED,REQUEST_NO_PROVIDER_AVAILABLE];

        try{
        
        	$requests = Requests::ProviderRequestStatusCheck(Auth::user()->id, $check_status)->get()->toArray();
        	$requests_data = [];$invoice = [];

            foreach($requests as $each_request){

                $each_request['user_rating'] = ProviderRating::Average($each_request['user_id']) ?: 0;
                $each_request['service_time_diff'] = "00:00:00";
                if($each_request['start_time'] != "0000-00-00 00:00:00") {
                    $time_diff = Helper::time_diff($each_request['start_time'],date('Y-m-d H:i:s'));
                    $each_request['service_time_diff'] = $time_diff->format('%h:%i:%s');
                }
                $requests_data[] = $each_request;

                $allowed_status = [REQUEST_COMPLETE_PENDING,WAITING_FOR_PROVIDER_CONFRIMATION_COD,REQUEST_COMPLETED,REQUEST_RATING];

                if( in_array($each_request['status'], $allowed_status)) {

                    $user = User::find($each_request['user_id']);
                    $invoice_query = RequestPayment::where('request_id' , $each_request['request_id'])
                                    ->leftJoin('requests' , 'request_payments.request_id' , '=' , 'requests.id')
                                    ->leftJoin('users' , 'requests.user_id' , '=' , 'users.id')
                                    ->leftJoin('cards' , 'users.default_card' , '=' , 'cards.id');

                    if($user->payment_mode == CARD) {
                        $invoice_query = $invoice_query->where('cards.is_default' , DEFAULT_TRUE) ;  
                    }

                    $invoice = $invoice_query->select(
                                        'requests.confirmed_provider as provider_id' , 
                                        'request_payments.total_time',
                                        'request_payments.payment_mode as payment_mode' , 
                                        'request_payments.base_price',
                                        'request_payments.time_price' , 
                                        'request_payments.tax_price' , 
                                        'request_payments.total',
                                        'cards.card_token',
                                        'cards.customer_id',
                                        'cards.last_four',
                                        'requests.promo_code',
                                        'requests.promo_code_id',
                                        'requests.offer_amount',
                                        'request_payments.trip_fare',
                                        'requests.is_promo_code')
                                    ->get()->toArray();
                }
            }

        	return response()->json(['data' => $requests_data, 'invoice' => $invoice ]);

    	}

    	catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Something went wrong']);
        }

    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */

    public function message(Request $request){

    	$this->validate($request, [
                'request_id' => 'required|integer|exists:user_requests,id',
            ]);

    	try{

	        $Messages = ChatMessage::where('provider_id', Auth::user()->id)
	                	->where('request_id', $request->request_id)->get()->toArray();
	        return $Messages;

        }

        catch(Exception $e) {
                return response()->json(['error' => "Something Went Wrong"]);
        }
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */

    public function cod_paid(Request $request) {

		$this->validate($request, [
            'request_id' => 'required|integer|exists:user_requests,id,confirmed_provider,'.$request->id,
        ]);

        $requests = Requests::find($request->request_id);

        if($requests->status != WAITING_FOR_PROVIDER_CONFRIMATION_COD && $requests->status == REQUEST_RATING) {
        	return response()->json(['error' => "Something Went Wrong"]);
        }

        try{

            $requests->status = REQUEST_RATING;
            $requests->is_paid = DEFAULT_TRUE;
            $requests->save();

            // $title = Helper::tr('cod_paid_confirmation_title');
            // $message = Helper::tr('cod_paid_confirmation_message');
            // $this->dispatch(new sendPushNotification($requests->user_id,USER,$requests->id,$title,$message));

            $user = User::find($requests->user_id);
        	return response()->json(['request' => $requests, 'user' => $user ]);
        }

        catch(Exception $e) {
            return response()->json(['error' => "Something Went Wrong"]);
        }
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */


    public function provider_testimonial(){

        $testimonial = UserRating::where('provider_id',Auth::user()->id)
                                ->leftJoin('users', 'users.id', '=', 'user_ratings.user_id')
                                ->select('user_ratings.rating','users.first_name','user_ratings.comment','user_ratings.request_id','users.last_name','user_ratings.created_at')
                                ->orderBy('user_ratings.created_at')
                                ->get();

        return $testimonial;
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */


    public function upcoming_request() {

    	try{
		    $requests = UserRequests::ProviderUpcomingRequest(Auth::user()->id)->get();
		    return $requests;
        }

        catch(Exception $e) {
            return response()->json(['error' => "Something Went Wrong"]);
        }
        
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */

    public function request_details(Request $request) {

        $this->validate($request, [
                'request_id' => 'required|integer|exists:requests,id,confirmed_provider,'.Auth::user()->id,
            ]);
    
        try{

            $requests = UserRequests::RequestDetails($request->request_id)->first();
            return $requests;
        }

        catch(Exception $e) {
            return response()->json(['error' => "Something Went Wrong"]);
        }
    
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */

    public function availabilities() {

        $availabilities = ProviderAvailability::where('provider_id' , Auth::user()->id)
                         ->select('provider_id','id as availability_id' ,'available_date','start_time' ,'end_time' ,'status')
                            ->get();

        $data = array();

        foreach($availabilities as $availability){
            $provider_data = array();
            $provider_data['id'] = $availability->availability_id;
            $provider_data['start'] = date('Y-m-d',strtotime($availability->available_date)).'T'.date('H:i:s',strtotime($availability->start_time)).'Z';
            $provider_data['end'] = date('Y-m-d',strtotime($availability->available_date)).'T'.date('H:i:s',strtotime($availability->end_time)).'Z';
            $provider_data['allDay'] = false;
            if($availability->status == PROVIDER_AVAILABILITY_BOOKED) {
                $provider_data['className'] = "booked"; 
            } else {
                $provider_data['className'] = "no-booked";
            }
           
            if($availability->status == DEFAULT_FALSE) {
                $provider_data['editable'] = false;
            }

            array_push($data, $provider_data);
        }

        if($availabilities) {
            $response_array = ['success' => true , 'data' => $data];
        } else {
            $response_array = ['success' => false ];
        }

        return response()->json($response_array,200);
    
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */


    public function schedule_availability_time(Request $request) {
        $availability = $request->availability;

         $this->validate($request, ['availability' => 'required|json']);

            $limit_days = 10;

            // Check the input date is not exceed current date+ 7 days      
            $date = strtotime("+".$limit_days." day");
            $check_limit_date = date('Y-m-d', $date);

            $availability = json_decode($availability, true);

            $avail_data = array();

            foreach($availability as $available) {
                // Check the start date is less than the end date
                if(strtotime($available['start']) <= strtotime($available['end'])) {
            
                    // Get the time difference
                    $get_time_diff = Helper::time_diff($available['start'],$available['end']);
                    
                    $hours = $get_time_diff->h;

                    if($hours > 1) {

                        $current_date = $available['start'];
                        $start_time = $available['start'];
                        $end_time = $available['end'];

                        $start_end = "";

                        for($i=0;$i<$hours;$i++) {

                            if($start_end == ""){
                                $start_new = $start_time;   
                            }else{
                                $start_new = $start_end; 
                            }
                            // Add 1 hour from the START TIME
                            $change_date = new \DateTime($start_new);
                            $change_date->modify("+1 hours");
                            $end_new = $change_date->format("Y-m-d H:i:s");

                            // Assign new END TIME to end variable
                            $start_end = $end_new;

                            $current_date = Helper::formatDate($end_new);
                            $start_time = Helper::formatHour($start_new);
                            $end_time = Helper::formatHour($end_new);

                            // Check already availability is filled
                            $check_availability = ProviderAvailability::where('provider_id',$request->id)
                                            ->where('available_date',$current_date)
                                            ->where('start_time',date('H', strtotime($start_time)).':00:00')
                                            ->where('end_time',date('H', strtotime($end_time)).':00:00')
                                            ->first();

                            if(!$check_availability) {
                                $provider_availablity = new ProviderAvailability;
                                $provider_availablity->provider_id = $request->id;
                                $provider_availablity->start_time = date('H', strtotime($start_time)).':00:00';
                                $provider_availablity->end_time = date('H', strtotime($end_time)).':00:00';
                                $provider_availablity->available_date = $current_date;
                                $provider_availablity->status = PROVIDER_AVAILABILITY_SET;
                                $provider_availablity->save();
                            }
                            $data['start_time'] = date('H', strtotime($start_time)).':00:00';
                            $data['end_time'] = date('H', strtotime($end_time)).':00:00';
                            $data['date'] = $current_date;
                                        
                            array_push($avail_data, $data); 
                        }
                    } else {

                        Log::info('Single hour function');

                        Log::info('single time Diff'." ".$hours);

                        if($hours != 0) {

                            $current_date = Helper::formatDate($available['start']);
                            $start_time = Helper::formatHour($available['start']);
                            $end_time = Helper::formatHour($available['end']);

                            $check_availability = ProviderAvailability::where('provider_id',$request->id)
                                                ->where('available_date',$current_date)
                                                ->where('start_time',date('H', strtotime($start_time)).':00:00')
                                                ->where('end_time',date('H', strtotime($end_time)).':00:00')
                                                ->first();

                            if(!$check_availability) {
                                $provider_availablity = new ProviderAvailability;
                                $provider_availablity->provider_id = $request->id;  
                                $provider_availablity->start_time = date('H', strtotime($start_time)).':00:00'; 
                                $provider_availablity->end_time = date('H', strtotime($end_time)).':00:00';
                                $provider_availablity->available_date = $current_date;
                                $provider_availablity->status = DEFAULT_TRUE;
                                $provider_availablity->save();
                            }

                            $avail_data['start_time'] = date('H', strtotime($start_time)).':00:00';
                            $avail_data['end_time'] = date('H', strtotime($end_time)).':00:00';
                            $avail_data['date'] = $current_date;
                        }
                    }
                }
            }

            $response_array = array('success' => true , 'data' => $avail_data);
        return response()->json($response_array,200);
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */

    public function delete_availability(Request $request) {

         $this->validate($request, [
                    'availability_id' => 'required|exists:provider_availabilities,id,provider_id,'.Auth::user()->id,
                ]);

            if($avail = ProviderAvailability::where('id',$request->availability_id)->first()) {

                if($avail->status != PROVIDER_AVAILABILITY_BOOKED)  {

                    if(ProviderAvailability::where('id',$request->availability_id)->delete()) {
                        $response_array = array('success' => true);
                    } else {
                        $response_array = array('success' => false );
                    }
                } else {
                    $response_array = array('success' => false  );
                }

            } else {
                $response_array = array('success' => false);
            }

        return response()->json($response_array,200);

    }




}
