<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;
use App\Provider;

class AdminController extends Controller
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
     * Remove the specified resource from storage.
     *
     * @param  \App\Provider  $provider
     * @return \Illuminate\Http\Response
     */
    public function user_map()
    {
        $Users = User::where('latitude', '!=', 0)->where('longitude', '!=', 0)->get();
        return view('admin.map.user_map', compact('Users'));
    }

   	/**
     * Remove the specified resource from storage.
     *
     * @param  \App\Provider  $provider
     * @return \Illuminate\Http\Response
     */
    public function provider_map()
    {
        $Providers = Provider::where('latitude', '!=', 0)->where('longitude', '!=', 0)->get();
        return view('admin.map.provider_map', compact('Providers'));
    }

}
