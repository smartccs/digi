<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\User;
use App\Provider;
use App\UserRequests;

class DispatcherController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('admin');
    }

    /**
     * Dispatcher Panel.
     *
     * @param  \App\Provider  $provider
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('admin.dispatcher');
    }

    /**
     * Display a listing of the active trips in the application.
     *
     * @return \Illuminate\Http\Response
     */
    public function trips(Request $request)
    {
        $Trips = UserRequests::orderBy('id','desc')->paginate(10);
        return $Trips;
    }

    /**
     * Display a listing of the users in the application.
     *
     * @return \Illuminate\Http\Response
     */
    public function users(Request $request)
    {
        $Users = new User;

        $if($request->has('mobile')) {
            $Users->where('mobile', 'like', $request->mobile."%");
        }

        $if($request->has('first_name')) {
            $Users->where('first_name', 'like', $request->first_name."%");
        }

        $if($request->has('last_name')) {
            $Users->where('last_name', 'like', $request->last_name."%");
        }

        $if($request->has('email')) {
            $Users->where('email', 'like', $request->email."%");
        }

        return $Users->paginate(10);
    }

    /**
     * Display a listing of the active trips in the application.
     *
     * @return \Illuminate\Http\Response
     */
    public function providers(Request $request)
    {
        $Providers = Provider::paginate(10);
        return $Providers;
    }
}
