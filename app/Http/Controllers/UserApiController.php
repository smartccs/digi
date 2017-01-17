<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;


define('USER', 0);
define('PROVIDER',1);
define('NONE', 0);

define('DEFAULT_FALSE', 0);
define('DEFAULT_TRUE', 1);

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
define('REQUEST_TIME_EXCEED_CANCELLED', 10);

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
define('WAITING_TO_RESPOND', 1);
define('WAITING_TO_RESPOND_NORMAL',0);

define('RATINGS', '0,1,2,3,4,5');
define('DEVICE_ANDROID', 'android');
define('DEVICE_IOS', 'ios');


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
use App\RequestsMeta;
use App\ServiceType;
use App\Provider;
use App\Settings;
use App\FavouriteProvider;
use App\RequestPayment;
use App\UserRating;
use App\ProviderRating;
use App\ProviderAvailability;
use App\Cards;
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
                'picture' => 'required|mimes:jpeg,jpg,bmp,png',
            ]);

        $User = $request->all();

        $User['payment_mode'] = 'cod';
        $User['password'] = bcrypt($request->password);
        if($request->hasFile('picture')) {
            $User['picture'] = Helper::upload_picture($request->avatar);
        }

        $User = User::create($User);

        return $User;
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
                'id' => 'required',
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
                    $providers[$i]->favorite = FavouriteProvider::Nos($request->id, $providers[$i]->id)->count();
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
            $Provider->availability = ProviderAvailability::AvilableProviders($request->provider_id)->get();

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



    // Manual request
    public function manual_create_request(Request $request) {


         $this->validate($request, [
                    's_latitude' => 'required|numeric',
                    's_longitude' => 'required|numeric',
                    'service_type' => 'required|numeric|exists:service_types,id',
                    'provider_id' => 'required|exists:providers,id',
                    'promo_code' => 'exists:promocodes,promo_code',
                ]);


                $user = User::find($request->id);
            
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


}
