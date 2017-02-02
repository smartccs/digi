<?php

namespace App\Http\Controllers\ProviderResources;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use Auth;
use Setting;

use App\UserRequests;
use App\RequestFilter;

class TripController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        try{

            $IncomingRequests = RequestFilter::with('request.user')->where('provider_id', Auth::user()->id)->get();

            $Timeout = Setting::get('provider_select_timeout', 180);

            for ($i=0; $i < sizeof($IncomingRequests); $i++) {
                $IncomingRequests[$i]->time_left_to_respond = $Timeout - (time() - strtotime($IncomingRequests[$i]->request->assigned_at));
                if($IncomingRequests[$i]->time_left_to_respond < 0) {
                    Helper::assign_next_provider($IncomingRequests[$i]->request_id, Auth::user()->id);
                }
            }

            $Response = [
                    'account_status' => \Auth::user()->status,
                    'service_status' => \Auth::user()->service->status,
                    'requests' => $IncomingRequests,
                ];

            return $Response;
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Something went wrong']);
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function accept($id)
    {
        $this->validate($request, [
              'request_id' => 'required|integer|exists:user_requests,id'
          ]);

        $provider = Provider::find(Auth::user()->id);
        $requests = UserRequests::find($request->request_id);

        if($requests->status == REQUEST_CANCELLED) {
            return response()->json(['error' => 'Request has not been offered to this provider. Abort.']);
        }


        $request_filter = RequestFilter::CheckWaitingFilter($request->request_id,$provider->id)->first();

        if (!$request_filter) {
            return response()->json(['error' => 'Request has not been offered to this provider. Abort.']);
        } 

        try {

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
            RequestFilter::where('request_id', '=', $request->request_id)->delete();

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
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $this->validate($request, [
              'status' => 'required|in:ACCEPTED,STARTED,ARRIVED,PICKEDUP,DROPPED,PAID,COMPLETED',
           ]);

        try{

            $UserRequest = UserRequests::findOrFail($id);
            $UserRequest->status = REQUEST_INPROGRESS;
            $UserRequest->save();

            // Send Push Notification to User
            // $title = Helper::tr('provider_started_title');
            // $message = Helper::tr('provider_started_message');

            // $this->dispatch( new sendPushNotification($requests->user_id, USER,$requests->id,$title, $message));     
       
            return response()->json(['message' => 'Provider Started', 'current_status' => 'PROVIDER_STARTED' ]);

        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Unable to make the request, Please try again later']);
        } catch (Exception $e) {
            return response()->json(['error' => 'Unable to Update, Please try again later']);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $this->validate($request, [
                'request_id' => 'required|integer|exists:user_requests,id'
            ]);

        $provider = Auth::user();
        $requests = UserRequests::find($request->request_id);

        if($requests->status == REQUEST_CANCELLED) {
            return response()->json(['error' => 'Request has not been offered to this provider. Abort.']);
        }


        $request_filter = RequestFilter::CheckOfferedFilter($request->request_id, $provider->id)->first();

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

            $FindNextProvider = RequestFilter::FindNextProvider($request->request_id)->first();

            if($FindNextProvider){

                //assigning to next provider
                Provider::where('id',$FindNextProvider->provider_id)
                ->update(['waiting_to_respond', WAITING_TO_RESPOND_NORMAL]);

                UserRequests::where('id', '=', $request->request_id)->update(['request_start_time' => date("Y-m-d H:i:s")]);

            } else {
                
                // Change status as no providers available in request table
                UserRequests::where('id', '=', $requests->id)->update( ['status' => REQUEST_CANCELLED] );
                RequestFilter::where('request_id', '=', $requests->id)->delete();

            }

            return response()->json(['error' => 'Request has been Rejected.']);

        }
        
        catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Unable to make the request, Please try again later']);
        }

    }
}
