<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Helpers\Helper;

use DB;
use Log;
use Auth;
use Config;
use Setting;
use Carbon\Carbon;

use App\User;
use App\Provider;
use App\ProviderService;
use App\ServiceType;
use App\UserRequests;
use App\RequestFilter;
use App\Settings;
use App\Cards;
use App\ChatMessage;

class ProviderApiController extends Controller
{
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
