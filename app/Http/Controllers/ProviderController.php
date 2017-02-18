<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\UserRequests;
use App\Provider;
use Carbon\Carbon;
use App\Http\Controllers\ProviderResources\TripController;

class ProviderController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(Request $request)
    {
        $this->middleware('provider');  
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('provider.index');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */

    public function incoming(Request $request) {
        $API = new TripController(\Auth::guard('provider')->user());
        return $API->index($request);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function earnings()
    {

        $provider = Provider::where('id',\Auth::guard('provider')->user()->id)
                    ->with('service','accepted','cancelled')
                    ->get();

        $weekly = UserRequests::where('provider_id',\Auth::guard('provider')->user()->id)
                    ->with('payment')
                    ->where('created_at', '>=', Carbon::now()->subWeekdays(7))
                    ->get();

        $fully = UserRequests::where('provider_id',\Auth::guard('provider')->user()->id)
                    ->with('payment','service_type')
                    ->get();

        return view('provider.payment.earnings',compact('provider','weekly','fully'));
    }
}
