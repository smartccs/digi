<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Helpers\Helper;
use Illuminate\Database\Eloquent\ModelNotFoundException;

use DB;
use Log;
use Auth;
use Config;
use Setting;
use Carbon\Carbon;

use App\Admin;
use App\User;
use App\Provider;
use App\ProviderService;
use App\ServiceType;
use App\UserRequests;
use App\RequestFilter;
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

    public function history(){

    	try {
	        $requests = UserRequests::GetProviderHistory(Auth::user()->id)->get()->toArray();
	        return $requests;
	    } catch (ModelNotFoundException $e) {
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
                        ->select('user_ratings.*','users.first_name','users.last_name')
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
                        $response_array = array('success' => false);
                    }
                } else {
                    $response_array = array('success' => false);
                }

            } else {
                $response_array = array('success' => false);
            }

        return response()->json($response_array,200);

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
            return response()->json(['error' => 'Services Not Found!']);
        }

    }



}
