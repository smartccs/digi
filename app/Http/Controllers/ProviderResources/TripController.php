<?php

namespace App\Http\Controllers\ProviderResources;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Database\Eloquent\ModelNotFoundException;

use Auth;
use Setting;
use Carbon\Carbon;

use App\Helpers\Helper;
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

            $IncomingRequests = RequestFilter::IncomingRequest(Auth::user()->id)->get();

            $Timeout = Setting::get('provider_select_timeout', 180);

            for ($i=0; $i < sizeof($IncomingRequests); $i++) {
                $IncomingRequests[$i]->time_left_to_respond = $Timeout - (time() - strtotime($IncomingRequests[$i]->request->assigned_at));
                if($IncomingRequests[$i]->request->status == 'SEARCHING' && $IncomingRequests[$i]->time_left_to_respond < 0) {
                    $this->assign_next_provider($IncomingRequests[$i]->id);
                    return $this->index();
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
     * Cancel given request.
     *
     * @return \Illuminate\Http\Response
     */
    public function cancel($id)
    {
        $Cancellable = ['SEARCHING', 'ACCEPTED', 'ARRIVED', 'STARTED', 'CREATED'];

        if(!in_array($UserRequest->status, $Cancellable)) {
            return response()->json(['error' => 'Cannot cancel request at this stage!']);
        }

        

    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function rate($id)
    {

        $this->validate($request, [
                'rating' => 'required|integer|in:1,2,3,4,5',
                'comment' => 'max:255',
            ]);
    
        $request = UserRequests::where('id', $request->request_id)
                ->where('status', 'COMPLETED')
                ->where('paid', 0)
                ->first();

        if ($request) {
             return response()->json(['error' => 'Not Paid!'], 500);
        }

        try{

            $rating = new UserRequestRating();
            $rating->provider_id = $request->provider_id;
            $rating->user_id = $request->user_id;
            $rating->request_id = $request->id;
            $rating->user_rating = $request->rating;
            $rating->user_comment = $request->comment ?: '';
            $rating->save();

            $average = UserRequestRating::where('provider_id',$request->rating)->avg('user_rating');

            Provider::where('id',$request->provider_id)->update(['rating' => $average]);

            // Send Push Notification to Provider 

            return response()->json(['message' => 'Provider Rated Successfully']); 
        } catch (Exception $e) {
            return response()->json(['error' => 'Something went wrong'], 500);
        }
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
        try {

            $UserRequest = UserRequests::findOrFail($id);

            if($UserRequest->status != "SEARCHING") {
                return response()->json(['error' => 'Request already under progress!']);
            }

            $UserRequest->provider_id = Auth::user()->id;
            $UserRequest->status = "STARTED";
            // dd($UserRequest->toArray());
            $UserRequest->save();

            $Filters = RequestFilter::where('request_id', $UserRequest->id)->where('provider_id', '!=', Auth::user()->id)->get();
            // dd($Filters->toArray());
            foreach ($Filters as $Filter) {
                $Filter->delete();
            }

            // Send Push Notification to User

            return $UserRequest->with('user')->get();

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
              'status' => 'required|in:ACCEPTED,STARTED,ARRIVED,PICKEDUP,DROPPED,PAYMENT,COMPLETED',
           ]);

        try{

            $UserRequest = UserRequests::findOrFail($id);
            $UserRequest->status = $request->status;
            $UserRequest->save();

            // Send Push Notification to User
       
            return $UserRequest->with('user')->get();

        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Unable to update, Please try again later']);
        } catch (Exception $e) {
            return response()->json(['error' => 'Connection Error']);
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

        try {

            // Send Push Notification to User
            RequestFilter::where('request_id', $UserRequest->id)->where('provider_id', Auth::user()->id)->delete();
            return $UserRequest->with('user')->get();

        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Unable to reject, Please try again later']);
        } catch (Exception $e) {
            return response()->json(['error' => 'Connection Error']);
        }
    }

    public function assign_next_provider($request_id) {

        try {
            $UserRequest = UserRequests::findOrFail($request_id);
        } catch (ModelNotFoundException $e) {
            // Cancelled between update.
            return false;
        }

        RequestFilter::where('provider_id', Auth::user()->id)
            ->where('request_id', $UserRequest->id)
            ->delete();

        try {

            $next_provider = RequestFilter::where('request_id', $UserRequest->id)
                ->orderBy('id')
                ->firstOrFail();

            $UserRequest->current_provider_id = $next_provider->provider_id;
            $UserRequest->assigned_at = Carbon::now();
            $UserRequest->save();
            
        } catch (ModelNotFoundException $e) {
            UserRequests::where('id', $UserRequest->id)->update(['status' => 'CANCELLED']);

            // No longer need request specific rows from RequestMeta
            RequestFilter::where('request_id', $UserRequest->id)->delete();
        }
    }

}
