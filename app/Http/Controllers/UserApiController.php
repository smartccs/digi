<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;

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
use App\UserRequestRating;
use App\Card;

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
                'device_id' => 'required',
                'login_by' => 'required|in:manual,facebook,google',
                'first_name' => 'required|max:255',
                'last_name' => 'required|max:255',
                'email' => 'required|email|max:255|unique:users',
                'mobile' => 'required|digits_between:6,13',
                'password' => 'required|min:6',
            ]);

        try{
            
            $User = $request->all();

            $User['payment_mode'] = 'CASH';
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
                'password' => 'required|confirmed|min:6',
                'old_password' => 'required',
            ]);

        $User = Auth::user();

        if(Hash::check($request->old_password, $User->password))
        {
            $User->password = bcrypt($request->password);
            $User->save();

            if($request->ajax()) {
                return response()->json(['message' => 'Password changed successfully!']);
            }else{
                return back()->with('flash_success', 'Password Updated');
            }

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

    public function details(Request $request){

        $this->validate($request, [
            'device_type' => 'in:android,ios',
        ]);

        try{

            if($user = User::find(Auth::user()->id)){

                if($request->has('device_token')){
                    $user->device_token = $request->device_token;
                }

                if($request->has('device_type')){
                    $user->device_type = $request->device_type;
                }

                if($request->has('device_id')){
                    $user->device_id = $request->device_id;
                }

                $user->save();

                $user->currency = currency();
                return $user;

            }else{
                return response()->json(['error' => 'User Not Found!'], 500);
            }
        }
        catch (Exception $e) {
            return response()->json(['error' => 'Something Went Wrong!'], 500);
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
                $user->mobile = $request->mobile;
            }

            if ($request->picture != "") {
                Storage::delete($user->picture);
                $user->picture = $request->picture->store('user/profile');
            }

            $user->save();

            if($request->ajax()) {
                return response()->json($user);
            }else{
                return back()->with('flash_success', 'Profile Updated');
            }
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

    public function send_request(Request $request) {

        $this->validate($request, [
                's_latitude' => 'required|numeric',
                'd_latitude' => 'required|numeric',
                's_longitude' => 'required|numeric',
                'd_longitude' => 'required|numeric',
                'service_type' => 'required|numeric|exists:service_types,id',
                'promo_code' => 'exists:promocodes,promo_code',
                'distance' => 'required|numeric',
                'use_wallet' => 'numeric',
                'payment_mode' => 'required|in:CASH,CARD,PAYPAL',
                'card_id' => ['required_if:payment_mode,CARD','exists:cards,card_id,user_id,'.Auth::user()->id],
            ]);

        Log::info('New Request from user id :'. Auth::user()->id .' params are :');
        Log::info($request->all());

        $ActiveRequests = UserRequests::PendingRequest(Auth::user()->id)->count();

        if($ActiveRequests > 0) {
            if($request->ajax()) {
                return response()->json(['error' => 'Already request is in progress. Try again later'], 500);
            }else{
                return back()->with('flash_error', 'Already request is in progress. Try again later');
            }
        }

        $ActiveProviders = ProviderService::AvailableServiceProvider($request->service_type)->get()->pluck('provider_id');

        $distance = Setting::get('search_radius', '10');
        $latitude = $request->s_latitude;
        $longitude = $request->s_longitude;

        $Providers = Provider::whereIn('id', $ActiveProviders)
            ->where('status', 'approved')
            ->whereRaw("(1.609344 * 3956 * acos( cos( radians('$latitude') ) * cos( radians(latitude) ) * cos( radians(longitude) - radians('$longitude') ) + sin( radians('$latitude') ) * sin( radians(latitude) ) ) ) <= $distance")
            ->get();

        // List Providers who are currently busy and add them to the filter list.

        if(count($Providers) == 0) {
            if($request->ajax()) {
                // Push Notification to User
                return response()->json(['message' => 'No Providers Found! Please try again.']); 
            }else{
                return back()->with('flash_success', 'No Providers Found! Please try again.');
            }
        }

        try{

            $UserRequest = new UserRequests;
            $UserRequest->user_id = Auth::user()->id;
            $UserRequest->current_provider_id = $Providers[0]->id;
            $UserRequest->service_type_id = $request->service_type;
            $UserRequest->payment_mode = $request->payment_mode;
            
            $UserRequest->status = 'SEARCHING';

            $UserRequest->s_address = $request->s_address ? : "";
            $UserRequest->d_address = $request->d_address ? : "";

            $UserRequest->s_latitude = $request->s_latitude;
            $UserRequest->s_longitude = $request->s_longitude;

            $UserRequest->d_latitude = $request->d_latitude;
            $UserRequest->d_longitude = $request->d_longitude;
            $UserRequest->distance = $request->distance;

            $UserRequest->use_wallet = $request->use_wallet ? : 0;
            
            $UserRequest->assigned_at = Carbon::now();

            $UserRequest->save();

            Log::info('New Request id : '. $UserRequest->id .' Assigned to provider : '. $UserRequest->current_provider_id);

            // update payment mode 

            User::where('id',Auth::user()->id)->update(['payment_mode' => $request->payment_mode]);

            if($request->has('card_id')){

                Card::where('user_id',Auth::user()->id)->update(['is_default' => 0]);
                Card::where('card_id',$request->card_id)->update(['is_default' => 1]);
                
            }

            foreach ($Providers as $key => $Provider) {

                $Filter = new RequestFilter;
                // Send push notifications to the first provider
                // $title = Helper::get_push_message(604);
                // $message = "You got a new request from".$user->name;

                $Filter->request_id = $UserRequest->id;
                $Filter->provider_id = $Provider->id; 
                $Filter->save();
            }

            if($request->ajax()) {
                return response()->json([
                        'message' => 'New request Created!',
                        'request_id' => $UserRequest->id,
                        'current_provider' => $UserRequest->current_provider_id,
                    ]);
            }else{
                return redirect('dashboard');
            }

        } catch (Exception $e) {
            if($request->ajax()) {
                return response()->json(['error' => 'Something went wrong while sending request. Please try again.'], 500);
            }else{
                return back()->with('flash_error', 'Something went wrong while sending request. Please try again.');
            }
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

            $UserRequest = UserRequests::findOrFail($request->request_id);

            if($UserRequest->status == 'CANCELLED')
            {
                if($request->ajax()) {
                    return response()->json(['error' => 'Request is Already Cancelled!'], 500); 
                }else{
                    return back()->with('flash_error', 'Request is Already Cancelled!');
                }
            }

            if(in_array($UserRequest->status, ['SEARCHING','STARTED','ARRIVED'])) {

                $UserRequest->status = 'CANCELLED';
                $UserRequest->save();

                RequestFilter::where('request_id', $UserRequest->id)->delete();

                if($UserRequest->provider_id != 0){

                    ProviderService::where('provider_id',$UserRequest->provider_id)->update(['status', 'riding']);

                    // send push and email
                }

                if($request->ajax()) {
                    return response()->json(['message' => 'Request Cancelled Successfully']); 
                }else{
                    return redirect('dashboard')->with('flash_success','Request Cancelled Successfully');
                }

            } else {
                if($request->ajax()) {
                    return response()->json(['error' => 'Service Already Started!'], 500); 
                }else{
                    return back()->with('flash_error', 'Service Already Started!');
                }
            }
        }

        catch (ModelNotFoundException $e) {
            if($request->ajax()) {
                return response()->json(['error' => 'No Request Found!']);
            }else{
                return back()->with('flash_error', 'No Request Found!');
            }
        }

    }

    /**
     * Show the request status check.
     *
     * @return \Illuminate\Http\Response
     */

    public function request_status_check() {

        try{

            $check_status = ['CANCELLED'];

            $UserRequests = UserRequests::UserRequestStatusCheck(Auth::user()->id,$check_status)
                                        ->get()
                                        ->toArray();

            return response()->json(['data' => $UserRequests]);

        }

        catch (Exception $e) {
            return response()->json(['error' => 'Something went wrong. Please try again.'], 500);
        }

    } 

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */


    public function rate_provider(Request $request) {

        $this->validate($request, [
                'request_id' => 'required|integer|exists:user_requests,id,user_id,'.Auth::user()->id,
                'rating' => 'required|integer|in:1,2,3,4,5',
                'comment' => 'max:255',
            ]);
    
        $UserRequests = UserRequests::where('id' ,$request->request_id)
                ->where('status' ,'COMPLETED')
                ->where('paid', 0)
                ->first();

        if ($UserRequests) {
            if($request->ajax()){
                return response()->json(['error' => 'Not Paid!'], 500);
            } else {
                return back()->with('flash_error', 'Service Already Started!');
            }
        }

        try{

            $UserRequest = UserRequests::findOrFail($request->request_id);
            
            if($UserRequest->rating == null) {
                UserRequestRating::create([
                        'provider_id' => $UserRequest->provider_id,
                        'user_id' => $UserRequest->user_id,
                        'request_id' => $UserRequest->id,
                        'user_rating' => $request->rating,
                        'user_comment' => $request->comment,
                    ]);
            } else {
                $UserRequest->rating->update([
                        'user_rating' => $request->rating,
                        'user_comment' => $request->comment,
                    ])
            }

            $UserRequest->user_rated = 1;
            $UserRequest->save();

            $average = UserRequestRating::where('provider_id', $UserRequest->provider_id)->avg('user_rating');

            Provider::where('id',$UserRequest->provider_id)->update(['rating' => $average]);

            // Send Push Notification to Provider 
            if($request->ajax()){
                return response()->json(['message' => 'Driver Rated Successfully']); 
            }else{
                return redirect('dashboard')->with('flash_success', 'Driver Rated Successfully!');
            }
        } catch (Exception $e) {
            if($request->ajax()){
                return response()->json(['error' => 'Something went wrong'], 500);
            }else{
                return back()->with('flash_error', 'Something went wrong');
            }
        }

    } 


    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */

    public function trips() {
    
        try{
            $UserRequests = UserRequests::UserTrips(Auth::user()->id)->get();
            if(!empty($UserRequests)){
                $map_icon = asset('asset/marker.png');
                foreach ($UserRequests as $key => $value) {
                    $UserRequests[$key]->static_map = "https://maps.googleapis.com/maps/api/staticmap?autoscale=1&size=320x130&maptype=terrian&format=png&visual_refresh=true&markers=icon:".$map_icon."%7C".$value->s_latitude.",".$value->s_longitude."&markers=icon:".$map_icon."%7C".$value->d_latitude.",".$value->d_longitude."&path=color:0x000000|weight:3|".$value->s_latitude.",".$value->s_longitude."|".$value->d_latitude.",".$value->d_longitude."&key=".env('GOOGLE_API_KEY');
                }
            }
            return $UserRequests;
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

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */

    public function estimated_fare(Request $request){

        $this->validate($request,[
                's_latitude' => 'required|numeric',
                's_longitude' => 'required|numeric',
                'd_latitude' => 'required|numeric',
                'd_longitude' => 'required|numeric',
                'service_type' => 'required|numeric|exists:service_types,id',
            ]);

        try{

            $details = "http://maps.googleapis.com/maps/api/distancematrix/json?origins=".$request->s_latitude.",".$request->s_longitude."&destinations=".$request->d_latitude.",".$request->d_longitude."&mode=driving&sensor=false";

            $json = file_get_contents($details);

            $details = json_decode($json, TRUE);

            $meter = $details['rows'][0]['elements'][0]['distance']['value'];
            $time = $details['rows'][0]['elements'][0]['duration']['text'];

            $kilometer = round($meter/1000);

            $tax_percentage = \Setting::get('tax_percentage');
            $commission_percentage = \Setting::get('commission_percentage');
            $service_type = ServiceType::findOrFail($request->service_type);
            $base_price = $service_type->fixed;

            $price_per_kilometer = $service_type->price;
            $price = $base_price + ($kilometer * $price_per_kilometer);
            $price += ( $commission_percentage/100 ) * $price;
            $tax_price = ( $tax_percentage/100 ) * $price;
            $total = $price + $tax_price;

            return response()->json([
                    'estimated_fare' => round($total,2), 
                    'distance' => $kilometer,
                    'time' => $time,
                    'tax_price' => $tax_price,
                    'base_price' => $base_price,
                    'wallet_balance' => Auth::user()->wallet_balance
                ]);

        }

        catch(Exception $e){
                return response()->json(['error' => "Something Went Wrong"], 500);
        }

    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */

    public function trip_details(Request $request) {

         $this->validate($request, [
                'request_id' => 'required|integer|exists:user_requests,id',
            ]);
    
        try{
            $UserRequests = UserRequests::UserTripDetails(Auth::user()->id,$request->request_id)->get();
            if(!empty($UserRequests)){
                $map_icon = asset('asset/marker.png');
                foreach ($UserRequests as $key => $value) {
                    $UserRequests[$key]->static_map = "https://maps.googleapis.com/maps/api/staticmap?autoscale=1&size=320x130&maptype=terrian&format=png&visual_refresh=true&markers=icon:".$map_icon."%7C".$value->s_latitude.",".$value->s_longitude."&markers=icon:".$map_icon."%7C".$value->d_latitude.",".$value->d_longitude."&path=color:0x000000|weight:3|".$value->s_latitude.",".$value->s_longitude."|".$value->d_latitude.",".$value->d_longitude."&key=".env('GOOGLE_API_KEY');
                }
            }
            return $UserRequests;
        }

        catch (Exception $e) {
            return response()->json(['error' => 'Something went wrong']);
        }
    }

}
