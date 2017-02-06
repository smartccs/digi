<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class RideController extends Controller
{
    protected $UserAPI;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(UserApiController $UserAPI)
    {
        $this->middleware('auth');
        $this->UserAPI = $UserAPI;
    }


    /**
     * Ride Confirmation.
     *
     * @return \Illuminate\Http\Response
     */
    public function confirm_ride(Request $request)
    {
        $fare = $this->UserAPI->estimated_fare($request)->getData();
        return view('user.ride.confirm_ride',compact('request','fare'));
    }
}
