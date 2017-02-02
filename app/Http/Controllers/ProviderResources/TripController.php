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
        $UserRequest = UserRequests::find($id);

        if($UserRequest->status == "ACCEPTED") {
            return response()->json(['error' => 'Request already accepted!']);
        }

        try {
            
            $UserRequest->provider_id = Auth::user()->id;
            $UserRequest->status = "ACCEPTED";
            $UserRequest->save();

            // Send Push Notification to User

            RequestFilter::where('request_id', $UserRequest->id)->where('provider_id', Auth::user()->id)->delete();

            return $UserRequest->with('user', 'service_type')->get();
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Unable to accept, Please try again later']);
        } catch (Exception $e) {
            return response()->json(['error' => 'Connection Error']);
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
        $UserRequest = UserRequests::find($id);

        $Cancellable = ['ACCEPTED', 'ARRIVED', 'SEARCHING', 'STARTED', 'CREATED'];

        if(!in_array($UserRequest->status, $Cancellable)) {
            return response()->json(['error' => 'Cannot cancel request at this stage!']);
        }

        try {

            $UserRequest->provider_id = 0;
            $UserRequest->status = 'SEARCHING';
            $UserRequest->save();

            // Send Push Notification to User

            RequestFilter::where('request_id', $UserRequest->id)->where('provider_id', Auth::user()->id)->delete();

            return $UserRequest->with('user', 'service_type')->get();
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Unable to accept, Please try again later']);
        } catch (Exception $e) {
            return response()->json(['error' => 'Connection Error']);
        }
    }
}
