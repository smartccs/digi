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
            return UserRequests::RequestDetails($request->request_id)->firstOrFail();
        } catch(Exception $e) {
            return response()->json(['error' => "Something Went Wrong"]);
        }
    
    }
}
