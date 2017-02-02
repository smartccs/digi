<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;

use DB;
use Log;
use Auth;
use Hash;
use Setting;
use Exception;
use Carbon\Carbon;

use App\User;
use App\ProviderService;
use App\UserRequests;
use App\Promocode;
use App\RequestFilter;
use App\ServiceType;
use App\Provider;
use App\Settings;
use App\UserRating;
use App\ProviderAvailability;
use App\Cards;
use App\UserPayment;
use App\ChatMessage;
use App\Helpers\Helper;


class UserApiController extends Controller
{
    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */

    public function signup(Request $request)
    {
        $this->validate($request, [
                'social_unique_id' => ['required_if:login_by,facebook,google','unique:users'],
                'device_type' => 'required|in:android,ios',
                'device_token' => 'required',
                'login_by' => 'required|in:manual,facebook,google',
                'first_name' => 'required|max:255',
                'last_name' => 'required|max:255',
                'email' => 'required|email|max:255|unique:users',
                'mobile' => 'required|digits_between:6,13',
                'password' => 'required|min:6',
            ]);

        try{
            
            $User = $request->all();

            $User['payment_mode'] = 'cod';
            $User['password'] = bcrypt($request->password);
            $User = User::create($User);

            return $User;
        } catch (Exception $e) {
             return response()->json(['error' => 'Something Went Wrong'], 500);
        }
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

        $User = Auth::user();

        if(Hash::check($request->old_password, $User->password))
        {
            $User->password = bcrypt($request->password);
            $User->save();

            return response()->json(['message' => 'Password changed successfully!']);
        } else {
            return response()->json(['error' => 'Please enter correct password'], 500);
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
            ]);

        if($user = User::find(Auth::user()->id)){

            $user->latitude = $request->latitude;
            $user->longitude = $request->longitude;
            $user->save();

            return response()->json(['message' => 'Location Updated successfully!']);

        }else{

            return response()->json(['error' => 'User Not Found!'], 500);

        }

    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */

    public function details(){

        if($user = User::find(Auth::user()->id)){
            return $user;
        }else{
            return response()->json(['error' => 'User Not Found!'], 500);
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
            ]);

         try {

            $user = User::findOrFail(Auth::user()->id);

            if($request->has('first_name')){ 
                $user->first_name = $request->first_name;
            }
            
            if($request->has('last_name')){
                $user->last_name = $request->last_name;
            }
            
            if($request->has('email')){
                $user->email = $request->email;
            }
            
            if ($request->mobile != ""){
                $user->mobile = $request->mobile;
            }

            if ($request->picture != "") {
                Helper::delete_avatar($user->picture); 
                $user->picture = Helper::upload_avatar($request->picture);
            }

            $user->save();

            return response()->json([
                        'message' => 'Profile Updated successfully!',
                        'first_name' => $user->first_name,
                        'last_name' => $user->last_name,
                        'picture' => $user->picture
                    ]);
        }

        catch (ModelNotFoundException $e) {
             return response()->json(['error' => 'User Not Found!'], 500);
        }

    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */

    public function services() {

        if($serviceList = ServiceType::all()) {
            foreach ($serviceList as $key => $value) {
                $serviceList[$key]->grey_image = url('/').\Image::url($value->image,array('grayscale'));
            }
            return $serviceList;
        } else {
            return response()->json(['error' => 'Services Not Found!'], 500);
        }

    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */

    public function guest_provider_list(Request $request) {

        $this->validate($request, [
                'latitude' => 'required|numeric',
                'longitude' => 'required|numeric',
                'service_id' => 'exists:service_types,id',
            ]);

        try {

                $latitude = $request->latitude;
                $longitude = $request->longitude;

                $distance = \Setting::get('search_radius');

                $providers = Provider::GuestProviderList($latitude, $longitude, $request->service_id, $distance);

                for($i = 0; $i < sizeof($providers); $i++) {

                    $providers[$i]->rating = UserRating::Average($providers[$i]->id) ?: 0;
                    $providers[$i]->availablity = ProviderAvailability::Providers($providers[$i]->id)->get()->toArray();
                }

                return response()->json($providers);
        }

        catch (Exception $e) {
             return response()->json(['error' => 'No Providers Found!'], 500);
        }
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */

    public function provider_details(Request $request)
    {
        $this->validate($request, [
                'provider_id' => 'required|exists:providers,id',
            ]);

        try{

            $provider = Provider::findOrFail($request->provider_id);
            $provider['rating'] = UserRating::Average($request->provider_id) ? : 0;

            return response()->json($provider);
        }

        catch (ModelNotFoundException $e) {
             return response()->json(['error' => 'No Provider Found!']);
        }
        
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */

    public function guest_provider_availability(Request $request) {

        $this->validate($request, [
                'provider_id' => 'exists:providers,id',
            ]);

        try{

            $Provider = Provider::findOrFail($request->provider_id);
            $Provider->rating = UserRating::Average($request->provider_id) ? : 0;
            $Provider->availability = ProviderAvailability::AvailableProviders($request->provider_id)->get();

            return $Provider;
        }

        catch (ModelNotFoundException $e) {
             return response()->json(['error' => 'No Provider Found!']);
        }
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */

    public function send_request(Request $request) {

        $this->validate($request, [
                's_latitude' => 'required|numeric',
                'd_latitude' => 'required|numeric',
                's_longitude' => 'required|numeric',
                'd_longitude' => 'required|numeric',
                'service_type' => 'required|numeric|exists:service_types,id',
                'promo_code' => 'exists:promocodes,promo_code',
                'distance' => 'required|numeric'
            ]);

        Log::info('New Request: ', $request->all());

        $ActiveRequests = UserRequests::PendingRequest(Auth::user()->id)->count();

        if($ActiveRequests > 0) {
            return response()->json(['error' => 'Already request is in progress. Try again later'], 500);
        }

        $ActiveProviders = ProviderService::AvailableServiceProvider($request->service_type)->get()->pluck('provider_id');

        /*Get default search radius*/
        $distance = Setting::get('search_radius', '10');
        $latitude = $request->s_latitude;
        $longitude = $request->s_longitude;

        $Providers = Provider::whereIn('id', $ActiveProviders)
            ->where('status', 'approved')
            ->whereRaw("(1.609344 * 3956 * acos( cos( radians('$latitude') ) * cos( radians(latitude) ) * cos( radians(longitude) - radians('$longitude') ) + sin( radians('$latitude') ) * sin( radians(latitude) ) ) ) <= $distance")
            ->get();

        // dd($Providers->toArray());
        // List Providers who are currently busy and add them to the filter list.

        if(count($Providers) == 0) {
            // Push Notification to User
            return response()->json(['error' => 'No Providers Found!'], 500); 
        }

        try{

            $UserRequest = new UserRequests;
            $UserRequest->user_id = Auth::user()->id;
            $UserRequest->current_provider_id = $Providers[0]->id;
            $UserRequest->service_type_id = $request->service_type;
            
            $UserRequest->status = 'CREATED';

            $UserRequest->s_address = $request->s_address ? : "";
            $UserRequest->d_address = $request->d_address ? : "";

            $UserRequest->s_latitude = $request->s_latitude;
            $UserRequest->s_longitude = $request->s_longitude;

            $UserRequest->d_latitude = $request->d_latitude;
            $UserRequest->d_longitude = $request->d_longitude;
            $UserRequest->distance = $request->distance;
            
            $UserRequest->assigned_at = Carbon::now();

            $UserRequest->save();

            foreach ($Providers as $key => $Provider) {

                $Filter = new RequestFilter;
                // Send push notifications to the first provider
                // $title = Helper::get_push_message(604);
                // $message = "You got a new request from".$user->name;

                $Filter->request_id = $UserRequest->id;
                $Filter->provider_id = $Provider->id; 
                $Filter->save();
            }

            return response()->json([
                    'message' => 'New request Created!',
                    'request_id' => $UserRequest->id,
                    'current_provider' => $UserRequest->current_provider_id,
                ]);

        } catch (Exception $e) {
            return response()->json(['error' => 'Something went wrong while sending request. Please try again.'], 500);
        }
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */

    // Manual request
    public function manual_create_request(Request $request) {

         $this->validate($request, [
                    's_latitude' => 'required|numeric',
                    's_longitude' => 'required|numeric',
                    'service_type' => 'required|numeric|exists:service_types,id',
                    'provider_id' => 'required|exists:providers,id',
                    'promo_code' => 'exists:promocodes,promo_code',
                ]);


                $user = User::find(Auth::user()->id);
            
            if($provider = Provider::CheckAvailability($request->provider_id)->first()) {

                try{

                    $check_requests = UserRequests::PendingRequest(Auth::user()->id)->count();

                    if($check_requests > 0) {
                        return response()->json(['error' => 'Already request is in progress. Try again later']);
                    }

                    $requests = new UserRequests;
                    $requests->user_id = $user->id;

                    if($request->service_type){
                        $requests->request_type = $request->service_type;
                    }

                    $requests->status = REQUEST_WAITING;
                    $requests->confirmed_provider = NONE;
                    $requests->request_start_time = date("Y-m-d H:i:s");
                    $requests->start_time = date("Y-m-d H:00:00");
                    $requests->s_address = $request->s_address ? $request->s_address : "";
                    $requests->d_address = $request->d_address ? $request->d_address : "";
                        
                    if($request->s_latitude){ $requests->s_latitude = $request->s_latitude; }
                    if($request->s_longitude) { $requests->s_longitude = $request->s_longitude; }
                    if($request->d_latitude){ $requests->d_latitude = $request->d_latitude; }
                    if($request->d_longitude) { $requests->d_longitude = $request->d_longitude; }

                     $promo_code = Promocode::where('promo_code' , $request->promo_code)->where('is_valid' , 1)->first();

                    if($promo_code) {
                        $requests->promo_code_id = $promo_code->id;
                        $requests->promo_code = $request->promo_code;
                        $requests->offer_amount = $promo_code->offer;  
                        $requests->is_promo_code = DEFAULT_TRUE;  
                    }  
                        
                    $requests->save();

                    $request_meta = new RequestFilter;
                    $request_meta->status = REQUEST_META_OFFERED;

                    $provider->waiting_to_respond = WAITING_TO_RESPOND;
                    $provider->save();
                

                    // // Send push notifications to the first provider
                    // $title = Helper::get_push_message(604);
                    // $message = "You got a new request from".$user->name;

                    // $this->dispatch(new sendPushNotification($request->provider_id,PROVIDER,$requests->id,$title,$message));

                    // Push End

                    $request_meta->request_id = $requests->id;
                    $request_meta->provider_id = $request->provider_id; 
                    $request_meta->save();

                    return response()->json(['message' => 'New request Created!','request_id' => $requests->id,
                    'current_provider' => $request->provider_id]);

                }

            catch (Exception $e) {
                return response()->json(['error' => 'Something went wrong while sending request. Please try again.'], 500);
            }
  

        } else {
            return response()->json(['error' => 'No Providers Found!'], 500); 
        }
  
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */

    public function manual_scheduled_request(Request $request) {
        
        $this->validate($request, [
                    'provider_id' => 'required|exists:providers,id',
                    's_latitude' => 'required|numeric',
                    's_longitude' => 'required|numeric',
                    'service_type' => 'required|integer|exists:service_types,id',
                    'service_start' => 'date',
            ]);

            $user = User::find(Auth::user()->id);

            if(!$user->payment_mode) {
                return response()->json(['error' => 'Please Fill the Payment Details!'], 500); 
            } 


            $allow = DEFAULT_FALSE;
            if($user->payment_mode == CARD) {
                if($user_card = Cards::find($user->default_card)) {
                    $allow = DEFAULT_TRUE;
                }
            } else {
                $allow = DEFAULT_TRUE;
            }

            if($allow == DEFAULT_FALSE) {
                return response()->json(
                    ['error' => 'Default card is not available. Please add a card or change the payment mode'], 500); 
            }



            $check_requests = UserRequests::PendingRequest(Auth::user()->id)->count();

            if($check_requests > 0) {
                return response()->json(['error' => 'Already request is in progress. Try again later'], 500);
            }



        
            $check_later_requests = Helper::check_later_request(Auth::user()->id, $request->service_start, DEFAULT_TRUE);

            if($check_later_requests) {
                return response()->json(['error' => 'Request is already scheduled on this time'], 500);
            }



            $request->service_start = \Carbon\Carbon::parse($request->service_start);

            $provider_available_check = ProviderAvailability::where('status' , DEFAULT_TRUE)
                        ->whereIn('start_time', [
                                $request->service_start->toTimeString(),
                                $request->service_start->subHour()->toTimeString(),
                            ])
                        ->where('available_date', $request->service_start->toDateString())
                        ->where('provider_id', $request->provider_id)
                        ->leftJoin('providers' , 'provider_availabilities.provider_id' ,'=' ,'providers.id')
                        ->select('provider_id', 'providers.waiting_to_respond as waiting')
                        ->get();

            if(!$provider_available_check) {
                return response()->json(['error' => 'No provider found for the selected service in your area currently.'], 500);
            }




            try{
                // Create Requests
                $requests = new UserRequests;
                $requests->user_id = Auth::user()->id;
                $requests->request_type = $request->service_type;
                $requests->status = REQUEST_WAITING;
                $requests->confirmed_provider = NONE;
                $requests->request_start_time = \Carbon\Carbon::now();
                $requests->s_address = $request->s_address;
                $requests->provider_id = $request->provider_id;
                $requests->current_provider = $request->provider_id;
                $requests->start_time = $request->service_start;

                //Later Details
                $requests->later = DEFAULT_TRUE;
                $requests->requested_time = $request->service_start;
                
                $requests->s_latitude = $request->s_latitude;
                $requests->s_longitude = $request->s_longitude;
                    
                $requests->save();

                $current_provider = Provider::find($request->provider_id);
                $current_provider->waiting_to_respond = WAITING_TO_RESPOND;
                $current_provider->save();

                // $title = Helper::get_push_message(604);
                // $message = "You got a new request from ".$user->name;
                // $this->dispatch(new sendPushNotification($request->provider_id, PROVIDER, $requests->id, $title, $message)); 

                $request_meta = new RequestFilter;
                $request_meta->status = REQUEST_META_OFFERED;  // Request status change
                $request_meta->request_id = $requests->id;
                $request_meta->provider_id = $request->provider_id;
                $request_meta->service_id = $request->service_type;
                $request_meta->save();

                return response()->json(['message' => 'New request Scheduled!',
                        'request_id' => $requests->id,
                        'current_provider' => $request->provider_id]);
            }

            catch (Exception $e) {
                return response()->json(['error' => 'Something went wrong while sending request. Please try again.'], 500);
            }

    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */

    public function cancel_request(Request $request) {
    
        $this->validate($request, [
                'request_id' => 'required|numeric|exists:user_requests,id,user_id,'.Auth::user()->id,
            ]);

        try{

            $UserRequests = UserRequests::findOrFail($request->request_id);

            if($UserRequests->status == 'CANCELLED')
            {
                 return response()->json(['error' => 'Request is Already Cancelled!'], 500); 
            }

                if(in_array($UserRequests->status, ['CREATED','ASSIGNED','STARTED','ARRIVED'])) {

                    $UserRequests->status = 'CANCELLED';
                    $UserRequests->save();

                    if($UserRequests->provider_id != DEFAULT_FALSE){

                        $provider = Provider::find( $UserRequests->provider_id );
                        $provider->is_available = PROVIDER_AVAILABLE;
                        $provider->waiting_to_respond = WAITING_TO_RESPOND_NORMAL;
                        $provider->save();

                        // send push and email
                    }

                    RequestFilter::where('request_id', '=', $request->request_id)->delete();

                    return response()->json(['message' => 'Request Cancelled Successfully']); 

                } else {
                    return response()->json(['error' => 'Service Already Started!'], 500); 
                }
        }

        catch (ModelNotFoundException $e) {
             return response()->json(['error' => 'No Provider Found!']);
        }

    }

    /**
     * Show the request status check.
     *
     * @return \Illuminate\Http\Response
     */

    public function request_status_check() {

        try{

            $check_status = ['COMPLETED','CANCELLED','SEARCHING'];

            $requests = UserRequests::UserRequestStatusCheck(Auth::user()->id,$check_status)->get()->toArray();

            $requests_data = [];$invoice = [];

                foreach ($requests as  $req) {

                    $requests_data[] = $req;

                    $allowed_status = ['DROPPED','COMPLETED'];

                    if( in_array($req['status'], $allowed_status)) {

                        // $invoice_query = UserPayment::where('request_id' , $req['request_id'])
                        //                 ->leftJoin('requests' , 'request_payments.request_id' , '=' , 'requests.id')
                        //                 ->leftJoin('users' , 'requests.user_id' , '=' , 'users.id')
                        //                 ->leftJoin('cards' , 'users.default_card' , '=' , 'cards.id');

                        // if(Auth::user()->payment_mode == CARD) {
                        //     $invoice_query = $invoice_query->where('cards.is_default' , DEFAULT_TRUE) ;  
                        // }

                        // $invoice = []
                    }
                }

            return response()->json(['data' => $requests_data, 'invoice' => $invoice]);

        }

        catch (Exception $e) {
            return response()->json(['error' => 'Something went wrong while sending request. Please try again.'], 500);
        }

    } 


    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */

    public function paynow(Request $request) {

        $this->validate($request, [
            'request_id' => 'required|exists:user_requests,id,user_id,'.Auth::user()->id,
            'payment_mode' => 'required|in:'.COD.','.PAYPAL.','.CARD.'|exists:settings,key,value,1',
            'is_paid' => 'required',
        ]);


        $requests = UserRequests::where('id',$request->request_id)
                    ->where('status' , REQUEST_COMPLETE_PENDING)->first();

        $user = User::find(Auth::user()->id);

        if(!$requests && intval($requests->status) == REQUEST_RATING ) {

            return response()->json(['error' => 'Service is Already Paid']);
        }


            $total = 0;

            if($request_payment = UserPayment::where('request_id' , $request->request_id)->first()) {
                $request_payment->payment_mode = $request->payment_mode;
                $request_payment->save();
                $total = $request_payment->total;
            }

            if($request->payment_mode == COD) {

                $requests->status = WAITING_FOR_PROVIDER_CONFRIMATION_COD;
                $request_payment->payment_id = uniqid();
                $request_payment->status = DEFAULT_TRUE;

            } elseif($request->payment_mode == CARD) {




                $check_card_exists = User::where('users.id' , Auth::user()->id)
                            ->leftJoin('cards' , 'users.id','=','cards.user_id')
                            ->where('cards.id' , $user->default_card)
                            ->where('cards.is_default' , DEFAULT_TRUE);

                if($check_card_exists->count() == 0) {
                     return response()->json(['error' => 'No default card is available. Please add a card']);
                }



                $user_card = $check_card_exists->first();

                // Get the key from settings table
                $settings = Settings::where('key' , 'stripe_secret_key')->first();
                $stripe_secret_key = $settings->value;

                $customer_id = $user_card->customer_id;
            
                \Stripe\Stripe::setApiKey($stripe_secret_key);

                try{

                   $user_charge =  \Stripe\Charge::create(array(
                      "amount" => $total * 100,
                      "currency" => "usd",
                      "customer" => $customer_id,
                    ));

                   $payment_id = $user_charge->id;
                   $amount = $user_charge->amount/100;
                   $paid_status = $user_charge->paid;

                   $request_payment->payment_id = $payment_id;
                   $request_payment->status = 1;

                   if($paid_status) {
                        $requests->is_paid =  DEFAULT_TRUE;
                   }
                    $requests->status = REQUEST_RATING;
                    $requests->amount = $amount;
                
                } catch (\Stripe\StripeInvalidRequestError $e) {
                    return response()->json(['error' => 'Something Went Wrong While Paying'], 500);
                }


            }  

        $requests->save();
        $request_payment->save();


        // // Send notification to the provider Start
        // if($user)
        //     $title =  "The"." ".$user->first_name.' '.$user->last_name." done the payment";
        // else
        //     $title = Helper::tr('request_completed_user_title');

        // $message = Helper::get_push_message(603);
        // $this->dispatch(new sendPushNotification($requests->confirmed_provider,PROVIDER,$requests->id,$title,$message));
        // // Send notification end

        // // Send invoice notification to the user, provider and admin
        // $subject = Helper::tr('request_completed_bill');
        // $email = Helper::get_emails(3,Auth::user()->id,$requests->confirmed_provider);
        // $page = "emails.user.invoice";
        // Helper::send_invoice($requests->id,$page,$subject,$email);

        return response()->json(['message' => 'Paid Successfully']); 

    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */


    public function rate_provider(Request $request) {

        $user = User::find(Auth::user()->id);

        $this->validate($request, [
                'request_id' => 'required|integer|exists:user_requests,id,user_id,'.$user->id.'|unique:user_ratings,request_id',
                'rating' => 'required|integer|in:'.RATINGS,
                'comments' => 'max:255',
                'is_favorite' => 'in:'.DEFAULT_TRUE.','.DEFAULT_FALSE,
            ]);
    
            $req = Requests::where('id' ,$request->request_id)
                    ->where('status' ,REQUEST_RATING)
                    ->first();

            if (!$req && intval($req->status) == REQUEST_COMPLETED) {
                 return response()->json(['error' => 'Request is already Completed'], 500);
            }

            try{
                //Save Rating
                $rev_user = new UserRating();
                $rev_user->provider_id = $req->confirmed_provider;
                $rev_user->user_id = $req->user_id;
                $rev_user->request_id = $req->id;
                $rev_user->rating = $request->rating;
                $rev_user->comment = $request->comment ? $request->comment: '';
                $rev_user->save();

                $req->status = REQUEST_COMPLETED;
                $req->save();

                // Send Push Notification to Provider
                // $title = Helper::tr('provider_rated_by_user_title');
                // $messages = Helper::tr('provider_rated_by_user_message');
                // $this->dispatch( new sendPushNotification($req->confirmed_provider, PROVIDER,$req->id,$title, $messages,''));     

                return response()->json(['message' => 'Provider Rated Successfully']); 
            }

        catch (Exception $e) {
            return response()->json(['error' => 'Something went wrong'], 500);
        }

    } 


    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */

    public function history() {
    
        try{
            $requests = UserRequests::GetUserHistory(Auth::user()->id)->get()->toArray();
            return $requests;
        }

        catch (Exception $e) {
            return response()->json(['error' => 'Something went wrong']);
        }
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */

    public function single_request(Request $request) {

        $user = User::find(Auth::user()->id);

        $this->validate($request, [
                'request_id' => 'required|integer|exists:user_requests,id,user_id,'.$user->id,
            ]);


        try{

            $requests = UserRequests::where('requests.id' , $request->request_id)
                            ->leftJoin('providers' , 'requests.confirmed_provider','=' , 'providers.id')
                            ->leftJoin('users' , 'requests.user_id','=' , 'users.id')
                            ->leftJoin('user_ratings' , 'requests.id','=' , 'user_ratings.request_id')
                            ->leftJoin('request_payments' , 'requests.id','=' , 'request_payments.request_id')
                            ->leftJoin('cards','users.default_card','=' , 'cards.id')
                            ->select('providers.id as provider_id' , 'providers.picture as provider_picture', 'requests.s_address as s_address','requests.requested_time as requested_time','requests.status as status','requests.s_latitude as s_latitude','requests.s_longitude as s_longitude',
                                DB::raw('CONCAT(providers.first_name, " ", providers.last_name) as provider_name'),'user_ratings.rating','user_ratings.comment',
                                 DB::raw('ROUND(request_payments.base_price) as base_price'), DB::raw('ROUND(request_payments.tax_price) as tax_price'),
                                 DB::raw('ROUND(request_payments.time_price) as time_price'), DB::raw('ROUND(request_payments.total) as total'),
                                'cards.id as card_id','cards.customer_id as customer_id',
                                'cards.card_token','cards.last_four',
                                'requests.id as request_id','requests.before_image','requests.after_image',
                                'requests.user_id as user_id',
                                DB::raw('CONCAT(users.first_name, " ", users.last_name) as user_name'))
                            ->get()->toArray();
            return $requests;
        }

        catch (Exception $e) {
            return response()->json(['error' => 'Something went wrong'], 500);
        }
    
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */

    public function payment_modes() {

        $payment_modes = [];

        try{

            $modes = Settings::whereIn('key' , array('cod','paypal','card'))->where('value' , 1)->get();
            if($modes) {
                foreach ($modes as $key => $mode) {
                    $payment_modes[$mode->key] = $mode->key;
                }            
            }

            return $payment_modes;
        }

        catch (Exception $e) {
            return response()->json(['error' => 'Something went wrong'], 500);
        }
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */

    public function get_user_payment_modes() {

        try{

            $data = $card_data = array();

            if($user_cards = Cards::where('user_id' , Auth::user()->id)->get()) {
                foreach ($user_cards as $c => $card) {
                    $data['id'] = $card->id;
                    $data['customer_id'] = $card->customer_id;
                    $data['card_id'] = $card->card_token;
                    $data['last_four'] = $card->last_four;
                    $data['is_default']= $card->is_default;

                    array_push($card_data, $data);
                }
            } 

            return ['payment_mode' => Auth::user()->payment_mode , 'card' => $card_data];
        }

        catch (Exception $e) {
            return response()->json(['error' => 'Something went wrong'], 500);
        }
    
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */

    public function payment_mode_update(Request $request) {
        
        $this->validate($request, [
                'payment_mode' => 'required|in:'.COD.','.PAYPAL.','.CARD,
         ]);

        try{

            $user = User::where('id', '=', Auth::user()->id)->update( ['payment_mode' => $request->payment_mode]);
            return response()->json(['message' => 'Payment Mode Updated']);
        }

        catch (Exception $e) {
            return response()->json(['error' => 'Something went wrong']);
        }

    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */

    public function add_card(Request $request) {

        $this->validate($request, [
                'last_four' => 'required',
                'payment_token' => 'required',
            ]);

            $user = User::find(Auth::user()->id);

            try{

                $settings = Settings::where('key' , 'stripe_secret_key')->first();

                $stripe_secret_key = $settings->value;
                
                \Stripe\Stripe::setApiKey($stripe_secret_key);

                $customer = \Stripe\Customer::create(array(
                              "card" => $request->payment_token,
                              "description" => $user->email)
                            );

                Log::info('customer = '.print_r($customer, true));

                if($customer){

                    $customer_id = $customer->id;

                    $cards = new Cards;
                    $cards->user_id = Auth::user()->id;
                    $cards->customer_id = $customer_id;
                    $cards->last_four = $request->last_four;
                    $cards->card_token = $customer->sources->data[0]->id;

                    // Check is any default is available
                    $check_card = Cards::where('user',Auth::user()->id)->first();

                    if($check_card ) 
                        $cards->is_default = 0;
                    else
                        $cards->is_default = 1;
                    
                    $cards->save();

                    if($user) {
                        $user->payment_mode = CARD;
                        $user->default_card = $cards->id;
                        $user->save();
                    }
                
                    return response()->json(['message' => 'Card Added']);

                } else {
                    return response()->json(['error' => 'Could not create client ID'], 500);
                }
            
            } catch(Exception $e) {
                    return response()->json(['error' => $e], 500);
            }
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */

    public function delete_card(Request $request) {
    
        $this->validate($request, [
                'card_id' => 'required|integer|exists:cards,id,user_id,'.Auth::user()->id,
            ]);

        try{

            Cards::where('id',$request->card_id)->delete();

            $user = User::find(Auth::user()->id);

            if($user) {
                $user->payment_mode = CARD;
                $user->default_card = DEFAULT_FALSE;
                $user->save();
            }
            return response()->json(['message' => 'Card Deleted']);
        }

        catch(Exception $e) {
                return response()->json(['error' => $e], 500);
        }
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */

    public function default_card(Request $request) {

         $this->validate($request, [
                'card_id' => 'required|integer|exists:cards,id,user_id,'.Auth::user()->id,
            ]);

        try{

            $user = User::find(Auth::user()->id);
            
            $old_default = Cards::where('user_id' , Auth::user()->id)
                            ->where('is_default', DEFAULT_TRUE)
                            ->update(['is_default' => DEFAULT_FALSE]);

            $card = Cards::where('id' , $request->card_id)
                    ->update(['is_default' => DEFAULT_TRUE ]);

                if($user) {
                    $user->payment_mode = CARD;
                    $user->default_card = $request->card_id;
                    $user->save();
                }
                return response()->json(['message' => 'Successfully Done']);
        }

        catch(Exception $e) {
                return response()->json(['error' => "Something Went Wrong"], 500);
        }
    
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */


    public function message(Request $request)
    {
        $this->validate($request, [
                'request_id' => 'required|integer|exists:user_requests,id',
            ]);

        try{

        $Messages = ChatMessage::where('user_id', Auth::user()->id)
                ->where('request_id', $request->request_id)
                ->get()->toArray();

            return $Messages;

        }

        catch(Exception $e) {
                return response()->json(['error' => "Something Went Wrong"], 500);
        }
    }


    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */

    public function upcoming_request() {

        try{

            $requests = UserRequests::UserUpcomingRequest(Auth::user()->id)->get()->toArray();

            return $requests;
        }

        catch(Exception $e) {
                return response()->json(['error' => "Something Went Wrong"], 500);
        }        
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */

    public function add_money(Request $request){
        $this->validate($request, [
                    'amount' => 'required|integer',
                    'payment_mode' => 'required|in:'.PAYPAL.','.CARD.'|exists:settings,key,value,'.DEFAULT_TRUE,
                ]);

        try{

            $user = User::find(Auth::user()->id);

            if($request->payment_mode == CARD) {

                    $check_card_exists = User::where('users.id' , Auth::user()->id)
                                ->leftJoin('cards' , 'users.id','=','cards.user_id')
                                ->where('cards.id' , $user->default_card)
                                ->where('cards.is_default' , DEFAULT_TRUE);

                    if($check_card_exists->count() != 0) {

                        $user_card = $check_card_exists->first();

                        // Get the key from settings table
                        $settings = Settings::where('key' , 'stripe_secret_key')->first();
                        $stripe_secret_key = $settings->value;

                        $customer_id = $user_card->customer_id;
                    
                        if($stripe_secret_key) {
                            \Stripe\Stripe::setApiKey($stripe_secret_key);
                        } else {
                           return response()->json(['error' => "Something Went Wrong"]);
                        }

                        try{

                           $user_charge =  \Stripe\Charge::create(array(
                              "amount" => $request->amount * 100,
                              "currency" => "usd",
                              "customer" => $customer_id,
                            ));
                        
                        } catch (\Stripe\StripeInvalidRequestError $e) {
                            Log::info(print_r($e,true));
                            return response()->json(['error' => $e]);
                        
                        }

                    } else {
                        return response()->json(['error' => "No Card Exist"]);
                    }

                }  
        }

        catch(Exception $e) {
                return response()->json(['error' => "Something Went Wrong"], 500);
        }  
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    
    public function request_later(Request $request) {

       $this->validate($request, [
                    'latitude' => 'required|numeric',
                    'longitude' => 'required|numeric',
                    'service_type' => 'numeric|exists:service_types,id',
                    'requested_time' => 'required',
                ]);

            $user = User::find(Auth::user()->id);

            if(!$user->payment_mode) {
                return response()->json(['error' => "Payment Mode Not Exist"]);
            }


            $allow = DEFAULT_FALSE;
            if($user->payment_mode == CARD) {
                if($user_card = Cards::find($user->default_card)) {
                    $allow = DEFAULT_TRUE;
                }
            } else {
                $allow = DEFAULT_TRUE;
            }

            if($allow == DEFAULT_FALSE) {
                return response()->json(
                    ['error' => 'Default card is not available. Please add a card or change the payment mode']); 
            }   




            $check_requests = UserRequests::PendingRequest(Auth::user()->id)->count();

            if($check_requests > 0) {
                return response()->json(['error' => 'Already request is in progress. Try again later']);
            }



            $check_later_requests = Helper::check_later_request(Auth::user()->id,$request->requested_time,DEFAULT_TRUE);

            if($check_later_requests) {
                return response()->json(['error' => 'Request is already scheduled on this time']);
            }

                $service_type = $request->service_type;
                $fav_providers = array(); $first_provider_id = 0;
                $favProviders = Helper::get_fav_providers($service_type,Auth::user()->id);
                if($favProviders) {
                    foreach ($favProviders as $key => $favProvider) {
                        $fav_providers[] = $favProvider->provider_id;
                    }                
                }


                $latitude = $request->latitude;
                $longitude = $request->longitude;
                $request_start_time = time();


                $distance = \Setting::get('search_radius');

                $providers = array();   // Initialize providers variable

                if($service_type) {

                    $service_providers = ProviderService::AvailableServiceProvider($service_type)->get();

                    $list_service_ids = array();   
                    if($service_providers) {
                        foreach ($service_providers as $sp => $service_provider) {
                            $list_service_ids[] = $service_provider->provider_id;
                        }
                        $list_service_ids = implode(',', $list_service_ids);
                    }


                    if($list_service_ids) {
                        $query = "SELECT providers.id,providers.waiting_to_respond as waiting, 1.609344 * 3956 * acos( cos( radians('$latitude') ) * cos( radians(latitude) ) * cos( radians(longitude) - radians('$longitude') ) + sin( radians('$latitude') ) * sin( radians(latitude) ) ) AS distance FROM providers
                                WHERE id IN ($list_service_ids) AND is_available = 1 AND is_activated = 1 AND is_approved = 1
                                AND (1.609344 * 3956 * acos( cos( radians('$latitude') ) * cos( radians(latitude) ) * cos( radians(longitude) - radians('$longitude') ) + sin( radians('$latitude') ) * sin( radians(latitude) ) ) ) <= $distance
                                ORDER BY distance";

                        $providers = DB::select(DB::raw($query));
                        Log::info("Search query: " . $query);
                    } 
                }

                $merge_providers  = $nearby_providers = $fav_and_near_providers = array();

                $fav_near_providers = array();

                if ($providers) {

                    foreach ($providers as $provider) {
                        $nearby_providers[] = $provider->id;
                    }
                }



                $fav_and_near_providers = array_unique(array_merge($fav_providers,$nearby_providers));

                if(!$fav_and_near_providers) {
                    return response()->json(
                        ['error' => 'No provider found for the selected service in your area currently.']);
                    
                }

            try {

                // Get the Available providers based on the requested time
                $list_available_providers = array();

                $requested_date = Helper::formatDate($request->requested_time);
                $requested_time = $start_time = Helper::formatHour($request->requested_time);

                // get the +2 hours from the requested time
                $change_date = new \DateTime($request->requested_time);
                $change_date->modify("+1 hours");
                $end_time = $change_date->format("H:i:s");

                $next_start_time = $end_time;
                $next_end_date = Helper::add_date($request->requested_time,'+2');
                $next_end_time = Helper::formatHour($next_end_date);

                $available_providers = ProviderAvailability::where('available_date' , $requested_date)
                            ->where(array('start_time' => $start_time,'end_time' => $end_time ))
                            ->orWhere(array('available_date' => $requested_date,'start_time' => $next_start_time,'end_time' => $next_end_time ))
                            ->where('status' , DEFAULT_TRUE)
                            ->whereIn('provider_id',$fav_and_near_providers)
                            ->leftJoin('providers' , 'provider_availabilities.provider_id' ,'=' ,'providers.id')
                            ->select('provider_id' , DB::raw('COUNT(provider_id) as count') , 'providers.waiting_to_respond as waiting')
                            ->groupBy('provider_id')
                            ->having('count' , '=' , 2)
                            ->get();

                $before_final_providers = array();

                foreach ($available_providers as $key => $available_provider) {
                    $list_available_providers['id'] = $available_provider->provider_id;
                    $list_available_providers['waiting'] = $available_provider->waiting;

                    array_push($before_final_providers, $list_available_providers);
                }

                /*************************************/

                // Sort the providers based on the waiting time
                $sort_waiting_providers = Helper::sort_waiting_providers($before_final_providers);  

                // Get the final providers list
                $final_providers = $sort_waiting_providers['providers'];   
                
                $check_waiting_provider_count = $sort_waiting_providers['check_waiting_provider_count'];

                if(count($final_providers) == $check_waiting_provider_count)
                {
                    return response()->json(['error' => "Something Went Wrong"]);
                }

                // Create Requests
                $requests = new UserRequests;
                $requests->user_id = $user->id;

                if($service_type)
                    $requests->request_type = $service_type;

                $requests->status = REQUEST_NEW;
                $requests->confirmed_provider = NONE;
                $requests->request_start_time = date("Y-m-d H:i:s", time());
                $requests->s_address = $request->s_address ? $request->s_address : "";

                //Later Details
                $requests->later = DEFAULT_TRUE;
                $requests->requested_time = date('Y-m-d H:i:s', strtotime($request->requested_time));
                
                if($latitude){ $requests->s_latitude = $latitude; }
                if($longitude) { $requests->s_longitude = $longitude; }
                    
                $requests->save();

                $requests->status = REQUEST_WAITING;
                //No need fo current provider state
                // $requests->current_provider = $first_provider_id;
                $requests->save();

                // Save all the final providers
                $first_provider_id = 0;

                if($final_providers) {
                    foreach ($final_providers as $key => $final_provider) {

                        $request_meta = new RequestsMeta;

                        if($first_provider_id == 0) {

                            $first_provider_id = $final_provider;

                            $request_meta->status = REQUEST_META_OFFERED;  // Request status change

                            // Availablity status change
                            if($current_provider = Provider::find($first_provider_id)) {
                                $current_provider->waiting_to_respond = WAITING_TO_RESPOND;
                                $current_provider->save();
                            }

                            // Send push notifications to the first provider
                            // $title = Helper::get_push_message(604);
                            // $message = "You got a new request from".$user->name;
                            // $this->dispatch(new sendPushNotification($first_provider_id, PROVIDER,$requests->id,$title, $message,'')); 
                            // Push End
                        }
                        $request_meta->request_id = $requests->id;
                        $request_meta->provider_id = $final_provider; 
                        $request_meta->save();
                    }
                }

            return response()->json(['message' => 'New request Scheduled!','request_id' => $requests->id,
                    'current_provider' => $first_provider_id]);

        }

        catch(Exception $e) {
                return response()->json(['error' => "Something Went Wrong"], 500);
        }  
    
    }

    public function forgot_password(Request $request){

        $this->validate($request, [
                'email' => 'required|email|exists:users,email',
            ]);

        try{  

            // $user = User::where('email' , $email)->first();
            // $new_password = uniqid();
            // $user->password = Hash::make($new_password);

            // send mail

            return response()->json(['message' => 'New Password Sent to your mail!']);

        }

        catch(Exception $e){
                return response()->json(['error' => "Something Went Wrong"], 500);
        }
    }


    public function estimated_fare(Request $request){

        $this->validate($request,[
                's_latitude' => 'required|numeric',
                's_longitude' => 'required|numeric',
                'd_latitude' => 'required|numeric',
                'd_longitude' => 'required|numeric',
            ]);

        try{


            $details = "http://maps.googleapis.com/maps/api/distancematrix/json?origins=".$request->s_latitude.",".$request->s_longitude."&destinations=".$request->d_latitude.",".$request->d_longitude."&mode=driving&sensor=false";

            $json = file_get_contents($details);

            $details = json_decode($json, TRUE);

            $meter = $details['rows'][0]['elements'][0]['distance']['value'];

            $kilometer = round($meter/1000);

            $price = Helper::calculate_fare($kilometer);

            return response()->json([
                    'message' => 'Estimated Amount',
                    'estimated_fare' => currency($price['total']), 
                    'distance' => $kilometer
                ]);

        }

        catch(Exception $e){
                return response()->json(['error' => "Something Went Wrong"], 500);
        }

    }

}
