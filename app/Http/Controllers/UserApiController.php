<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Log;
use Hash;
use Validator;
use File;
use DB;
use Auth;

use App\User;
use App\ProviderService;
use App\ProviderLocation;
use App\UserRequests;
use App\Promocode;
use App\Admin;
use App\RequestsFilter;
use App\ServiceType;
use App\Provider;
use App\Settings;
use App\FavouriteProvider;
use App\UserRating;
use App\ProviderRating;
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
                'picture' => 'mimes:jpeg,jpg,bmp,png',
            ]);

        try{
            
            $User = $request->all();

            $User['payment_mode'] = 'cod';
            $User['password'] = bcrypt($request->password);
            if($request->hasFile('picture')) {
                $User['picture'] = Helper::upload_picture($request->picture);
            }

            $User = User::create($User);

            return $User;
        }

        catch (ModelNotFoundException $e) {
             return response()->json(['error' => 'Something Went Wrong']);
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

        $User = \Auth::user();

        if(\Hash::check($request->old_password, $User->password))
        {
            $User->password = bcrypt($request->password);
            $User->save();

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

    public function update_location(Request $request){

        $this->validate($request, [
                'latitude' => 'required|numeric',
                'longitude' => 'required|numeric',
                'address' => 'required',
            ]);

        if($user = User::find(\Auth::user()->id)){

            $user->latitude = $request->latitude;
            $user->longitude = $request->longitude;
            $user->address = $request->address;
            $user->save();

            return response()->json(['message' => 'Location Updated successfully!']);

        }else{

            return response()->json(['error' => 'User Not Found!']);

        }

    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */

    public function details(){

        if($user = User::find(\Auth::user()->id)){
            return $user;
        }else{
            return response()->json(['error' => 'User Not Found!']);
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

            $user = User::findOrFail(Auth::user()->id);

            if($request->has('first_name')) 
                $user->first_name = $request->first_name;
            
            if($request->has('last_name')) 
                $user->last_name = $request->last_name;
            
            if($request->has('email')) 
                $user->email = $email;
            
            if ($mobile != "")
                $user->mobile = $mobile;

            // Upload picture
            if ($picture != "") {
                Helper::delete_picture($user->picture); // Delete the old pic
                $user->picture = Helper::upload_picture($picture);
            }

            if($request->has('gender')) 
                $user->gender = $request->gender;
            
            $user->save();

            return response()->json(['message' => 'Profile Updated successfully!']);
        }

        catch (ModelNotFoundException $e) {
             return response()->json(['error' => 'User Not Found!']);
        }

    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */

    public function services() {

        if($serviceList = ServiceType::Approved()->get()) {
            return $serviceList;
        } else {
            return response()->json(['error' => 'Services Not Found!']);
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

            /*Get default search radius*/
            $settings = Settings::where('key', 'search_radius')->first();
            $distance = $settings->value;

            $service_type_id = $request->service_id;


           $query = "SELECT 
                        providers.id,
                        providers.first_name,
                        providers.last_name,
                        providers.picture,
                        providers.address,
                        providers.latitude,
                        providers.longitude,
                        provider_services.service_type_id,
                        1.609344 * 3956 * acos( cos( radians('$latitude') ) * cos( radians(latitude) ) * cos( radians(longitude) - radians('$longitude') ) + sin( radians('$latitude') ) * sin( radians(latitude) ) ) AS distance
                    FROM providers
                    LEFT JOIN provider_services ON providers.id = provider_services.provider_id
                    WHERE provider_services.service_type_id = $service_type_id 
                        AND providers.is_available = 1 
                        AND providers.waiting_to_respond = 0
                        AND is_activated = 1
                        AND is_approved = 1
                        AND (1.609344 * 3956 * acos( cos( radians('$latitude') ) * cos( radians(latitude) ) * cos( radians(longitude) - radians('$longitude') ) + sin( radians('$latitude') ) * sin( radians(latitude) ) ) ) <= $distance
                        ORDER BY distance";


                $providers = DB::select(DB::raw($query));

                for($i = 0; $i < sizeof($providers); $i++) {

                    $providers[$i]->rating = UserRating::Average($providers[$i]->id) ?: 0;
                    $providers[$i]->availablity = ProviderAvailability::Providers($providers[$i]->id)->get()->toArray();
                    $providers[$i]->favorite = FavouriteProvider::Nos(Auth::user()->id, $providers[$i]->id)->count();
                }

                return response()->json($providers , 200);
            }

        catch (ModelNotFoundException $e) {
             return response()->json(['error' => 'No Providers Found!']);
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

            $provider = Provider::find($request->provider_id);
            $provider['rating'] = UserRating::Average($request->provider_id) ? : 0;

            return $provider;
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

            $Provider = Provider::find($request->provider_id);
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

    // Automated Request
    public function send_request(Request $request) {

        $this->validate($request, [
            's_latitude' => 'required|numeric',
            's_longitude' => 'required|numeric',
            'service_type' => 'required|numeric|exists:service_types,id',
            'promo_code' => 'exists:promocodes,promo_code',
        ]);

            Log::info('Create request start');
            $service_type = $request->service_type; 
            $latitude = $request->s_latitude;
            $longitude = $request->s_longitude;

            $user = User::find(Auth::user()->id);
            $user->latitude = $request->s_latitude;
            $user->longitude = $request->s_longitude;
            $user->save();

            $check_requests = UserRequests::PendingRequest(Auth::user()->id)->count();

            if($check_requests > 0) {

                return response()->json(['error' => 'Already request is in progress. Try again later']);

            }



                $settings = Settings::where('key', 'search_radius')->first();
                $distance = $settings->value;

                $list_service_ids = array();$providers = array();  

                $service_providers = ProviderService::where('service_type_id' , $service_type)
                                        ->where('is_available' , 1)
                                        ->select('provider_id')
                                        ->get();

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
                

                $list_fav_providers = array(); $first_provider_id = 0; $list_fav_provider = array();
                $favProviders = Helper::get_fav_providers($service_type,Auth::user()->id);
                if($favProviders) {
                    foreach ($favProviders as $key => $favProvider) {
                        $list_fav_provider['id'] = $favProvider->provider_id;
                        $list_fav_provider['waiting'] = $favProvider->waiting;
                        $list_fav_provider['distance'] = 0;

                        array_push($list_fav_providers, $list_fav_provider);
                    }            

                }



                $merge_providers = array();$search_providers = array();

                if ($providers) {
                    $search_provider = array();
                    foreach ($providers as $provider) {
                        $search_provider['id'] = $provider->id;
                        $search_provider['waiting'] = $provider->waiting;
                        $search_provider['distance'] = $provider->distance;
                        
                        array_push($search_providers, $search_provider);
                    }

                } else {

                    if(!$list_fav_providers) {
                        Log::info("No Provider Found");
                        // Send push notification to User

                        // $title = Helper::get_push_message(601);
                        // $messages = Helper::get_push_message(602);
                        // $this->dispatch( new NormalPushNotification($user->id, USER,$title, $messages));     
                        // $response_array = array('success' => false, 'error' => Helper::get_error_message(112), 'error_code' => 112);
                        return response()->json(['error' => 'No Providers Found!']); 
                    }
                }

                $merge_providers = array_merge($list_fav_providers,$search_providers);
                $sort_waiting_providers = Helper::sort_waiting_providers($merge_providers);  
                $final_providers = $sort_waiting_providers['providers'];    
                $check_waiting_provider_count = $sort_waiting_providers['check_waiting_provider_count'];

            try{

                // Create Requests
                $requests = new UserRequests;
                $requests->user_id = Auth::user()->id;
                $requests->request_type = $request->service_type;
                $requests->status = REQUEST_WAITING;
                $requests->confirmed_provider = NONE;
                $requests->request_start_time = date("Y-m-d H:i:s", time());
                $requests->s_address = $request->s_address ? $request->s_address : "";
                    
                if($latitude){ $requests->s_latitude = $latitude; }
                if($longitude){ $requests->s_longitude = $longitude; }

                $promo_code = Promocode::where('promo_code' , $request->promo_code)->where('is_valid' , 1)->first();

                if($promo_code) {
                    $requests->promo_code_id = $promo_code->id;
                    $requests->promo_code = $request->promo_code;
                    $requests->offer_amount = $promo_code->offer;  
                    $requests->is_promo_code = DEFAULT_TRUE;  
                }   
                    
                $requests->save();

                // Save all the final providers
                $first_provider_id = 0;

                if($final_providers) {
                    foreach ($final_providers as $key => $final_provider) {

                        $request_meta = new RequestsFilter;

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

                            // Log::info('Push initiated');
                            // $this->dispatch(new sendPushNotification($first_provider_id,PROVIDER,$requests->id,$title,$message));


                            // Push End
                        }

                        $request_meta->request_id = $requests->id;
                        $request_meta->provider_id = $final_provider; 
                        $request_meta->save();
                    }
                }

                return response()->json(['message' => 'New request Created!','request_id' => $requests->id,
                    'current_provider' => $first_provider_id]);
            }

            catch (ModelNotFoundException $e) {
                return response()->json(['error' => 'Something went wrong while sending request. Please try again.']);
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

                    $request_meta = new RequestsFilter;
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

            catch (ModelNotFoundException $e) {
                return response()->json(['error' => 'Something went wrong while sending request. Please try again.']);
            }
  

        } else {
            return response()->json(['error' => 'No Providers Found!']); 
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
                return response()->json(['error' => 'Please Fill the Payment Details!']); 
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



        
            $check_later_requests = Helper::check_later_request(Auth::user()->id, $request->service_start, DEFAULT_TRUE);

            if($check_later_requests) {
                return response()->json(['error' => 'Request is already scheduled on this time']);
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
                return response()->json(['error' => 'No provider found for the selected service in your area currently.']);
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

                $request_meta = new RequestsFilter;
                $request_meta->status = REQUEST_META_OFFERED;  // Request status change
                $request_meta->request_id = $requests->id;
                $request_meta->provider_id = $request->provider_id;
                $request_meta->service_id = $request->service_type;
                $request_meta->save();

                return response()->json(['message' => 'New request Scheduled!',
                        'request_id' => $requests->id,
                        'current_provider' => $request->provider_id]);
            }

            catch (ModelNotFoundException $e) {
                return response()->json(['error' => 'Something went wrong while sending request. Please try again.']);
            }

    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */

    public function cancel_request(Request $request) {
    
        $user_id = Auth::user()->id;

        $this->validate($request, [
                'request_id' => 'required|numeric|exists:user_requests,id,user_id,'.$user_id,
            ]);

            $request_id = $request->request_id;
            $requests = UserRequests::find($request_id);

            if($requests->status == REQUEST_CANCELLED)
            {
                 return response()->json(['error' => 'Request is Already Cancelled!']); 
            }

                if(in_array($requests->provider_status, [PROVIDER_NONE,PROVIDER_ACCEPTED,PROVIDER_STARTED])) {

                    $requests->status = REQUEST_CANCELLED;
                    $requests->save();

                    if($requests->confirmed_provider != DEFAULT_FALSE){

                        $provider = Provider::find( $requests->confirmed_provider );
                        $provider->is_available = PROVIDER_AVAILABLE;
                        $provider->waiting_to_respond = WAITING_TO_RESPOND_NORMAL;
                        $provider->save();

                        // $title = Helper::tr('cancel_by_user_title');
                        // $message = Helper::tr('cancel_by_user_message');
                        
                        // $this->dispatch(new sendPushNotification($requests->confirmed_provider,PROVIDER,$requests->id,$title,$message));

                        // Log::info("Cancelled request by user");
                        // $email_data = array();

                        // $subject = Helper::tr('request_cancel_user');

                        // $email_data['provider_name'] = $email_data['username'] = "";

                        // if($user = User::find($requests->user_id)) {
                        //     $email_data['username'] = $user->first_name." ".$user->last_name;    
                        // }
                        
                        // if($provider = Provider::find($requests->confirmed_provider)) {
                        //     $email_data['provider_name'] = $provider->first_name. " " . $provider->last_name;
                        // }

                        // $page = "emails.user.request_cancel";
                        // $email_send = Helper::send_email($page,$subject,$provider->email,$email_data);
                    }

                    RequestsFilter::where('request_id', '=', $request_id)->delete();

                    return response()->json(['message' => 'Request Cancelled Successfully']); 

                } else {
                    return response()->json(['error' => 'Service Already Started!']); 
                }
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */

    public function request_status_check() {

        $user = User::find(Auth::user()->id);

        try{

            $check_status = [REQUEST_COMPLETED,REQUEST_CANCELLED,REQUEST_NO_PROVIDER_AVAILABLE,REQUEST_TIME_EXCEED_CANCELLED];

            $requests = UserRequests::UserRequestStatusCheck(Auth::user()->id,$check_status)->get()->toArray();

            $requests_data = [];$invoice = [];

                foreach ($requests as  $req) {

                    $req['rating'] = UserRating::Average($req['provider_id']) ?: 0;
                    $req['is_fav_provider'] = FavouriteProvider::IsFavCount($req['provider_id'],Auth::user()->id)->count() ? 1 : 0;

                    $requests_data[] = $req;

                    $allowed_status = [REQUEST_COMPLETE_PENDING,REQUEST_COMPLETED,REQUEST_RATING];

                    if( in_array($req['status'], $allowed_status)) {

                        $invoice_query = UserPayment::where('request_id' , $req['request_id'])
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
                                            'request_payments.trip_fare')
                                            ->get()->toArray();
                    }
                }

            return response()->json(['data' => $requests_data, 'invoice' => $invoice]);

        }

        catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Something went wrong while sending request. Please try again.']);
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
                    return response()->json(['error' => 'Something Went Wrong While Paying']);
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
                 return response()->json(['error' => 'Request is already Completed']);
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

                // Save favourite provider details
                if($request->is_favorite ==  DEFAULT_TRUE) {
                    $fav_provider = FavouriteProvider::IsFavCount(Auth::user()->id,$req->confirmed_provider )->count();
                    if($fav_provider == 0){
                        $favProvider = new FavouriteProvider;
                        $favProvider->provider_id = $req->confirmed_provider;
                        $favProvider->user_id = Auth::user()->id;
                        $favProvider->status = DEFAULT_TRUE;
                        $favProvider->save();
                    }
                }

                // Send Push Notification to Provider
                // $title = Helper::tr('provider_rated_by_user_title');
                // $messages = Helper::tr('provider_rated_by_user_message');
                // $this->dispatch( new sendPushNotification($req->confirmed_provider, PROVIDER,$req->id,$title, $messages,''));     

                return response()->json(['message' => 'Provider Rated Successfully']); 
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

    public function add_fav_provider(Request $request) {

         $this->validate($request, [
                'fav_provider' => 'exists:providers,id'
            ]);
    

            $fav_provider = FavouriteProvider::IsFavCount(Auth::user()->id,$request->fav_provider)->count();
            if($fav_provider == 0){

                $favProvider = new FavouriteProvider;
                $favProvider->provider_id = $request->fav_provider;
                $favProvider->user_id = Auth::user()->id;
                $favProvider->status = DEFAULT_TRUE;
                $favProvider->save();

                return response()->json(['message' => 'Provider Favorited Successfully']); 

            } else {
                return response()->json(['error' => 'Something went wrong']);
            }
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */

    public function fav_providers() {

        $fav_providers = FavouriteProvider::where('favourite_providers.user_id' , Auth::user()->id)
                            ->leftJoin('providers' , 'favourite_providers.provider_id' , '=' ,'providers.id')
                            ->leftJoin('provider_services' , 'provider_services.provider_id' , '=' ,'providers.id')
                            ->select(
                                'favourite_providers.id as favourite_id',
                                'providers.id as provider_id',
                                'provider_services.id as service_id',
                                DB::raw('CONCAT(providers.first_name, " ", providers.last_name) as provider_name'),
                                'providers.picture'
                                )
                            ->get()
                            ->toArray();

        $providers = [];$data = [];

        if($fav_providers) {

            foreach ($fav_providers as $f => $fav_provider) {
                $fav_provider['user_rating'] = Average($fav_provider['provider_id']) ? : 0;
                $providers[] = $fav_provider;
            }

            return $providers;

        } else {
            return response()->json(['error' => 'No Providers Found']);
        }

    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */

    public function delete_fav_provider(Request $request) {

        $this->validate($request, [
                'favourite_id' => "required|exists:favourite_providers,id",
            ]);

        try{

            $favourite = FavouriteProvider::find($request->favourite_id);

            if($provider = Provider::find($favourite->provider_id)) {

                $fav_delete = $favourite->delete();

                return response()->json(['message' => 'Provider Deleted Successfully']); 
            }
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

    public function history() {
    
        try{
            $requests = UserRequests::GetUserHistory(Auth::user()->id)->get()->toArray();
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

        catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Something went wrong']);
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

        catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Something went wrong']);
        }
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */

    public function get_user_payment_modes() {

        try{

            $user = User::find(Auth::user()->id);

            $payment_data = $data = $card_data = array();

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

            return ['payment_mode' => $user->payment_mode , 'card' => $card_data];
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

    public function payment_mode_update(Request $request) {
        
        $this->validate($request, [
                'payment_mode' => 'required|in:'.COD.','.PAYPAL.','.CARD,
         ]);

        try{

            $user = User::where('id', '=', Auth::user()->id)->update( ['payment_mode' => $request->payment_mode]);
            return response()->json(['message' => 'Payment Mode Updated']);
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
                    return response()->json(['error' => 'Could not create client ID']);
                }
            
            } catch(Exception $e) {
                    return response()->json(['error' => $e]);
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
                return response()->json(['error' => $e]);
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
                return response()->json(['error' => "Something Went Wrong"]);
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
                return response()->json(['error' => "Something Went Wrong"]);
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
                return response()->json(['error' => "Something Went Wrong"]);
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
                return response()->json(['error' => "Something Went Wrong"]);
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


                $settings = Settings::where('key', 'search_radius')->first();
                $distance = $settings->value;

                $providers = array();   // Initialize providers variable

                if($service_type) {

                    $service_providers = ProviderService::where('service_type_id' , $service_type)
                                ->where('is_available' , 1)
                                ->select('provider_id')->get();

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
                return response()->json(['error' => "Something Went Wrong"]);
        }  
    
    }



}
