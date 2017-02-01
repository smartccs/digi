<?php

namespace App\Http\Controllers\ProviderResources;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use Auth;
use Storage;

class ProfileController extends Controller
{
    /**
     * Create a new user instance.
     *
     * @return void
     */

    public function __construct()
    {
        $this->middleware('provider.api');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $User = Auth::user();
        $User->avatar = asset($User->avatar);
        return $User;
    }

    /**
     * Display the specified resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function show()
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function edit()
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        $this->validate($request, [
                'first_name' => 'required|max:255',
                'last_name' => 'required|max:255',
                'mobile' => 'required|digits_between:6,13',
                'avatar' => 'mimes:jpeg,bmp,png',
            ]);

        try {

            $provider = Auth::user();

            if($request->has('first_name')) 
                $provider->first_name = $request->first_name;

            if($request->has('last_name')) 
                $provider->last_name = $request->last_name;

            if ($request->has('mobile'))
                $provider->mobile = $request->mobile;

            // if ($request->has('address')) 
            //     $provider->address = $request->address;

            // if ($request->has('city')) 
            //     $provider->city = $request->city;

            // if ($request->has('state')) 
            //     $provider->state = $request->state;

            // if ($request->has('pincode')) 
            //     $provider->pincode = $request->pincode;

            if ($request->hasFile('avatar')) {
                Storage::delete($provider->avatar);
                $provider->avatar = 'storage/'.$request->avatar->store('provider/profile');
            }

            $provider->save();
            $provider->avatar = asset($provider->avatar);

            return $provider;
        }

        catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Provider Not Found!'], 404);
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
        //
    }

    /**
     * Update latitude and longitude of the user.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function location($id)
    {
        $this->validate($request, [
                'latitude' => 'required|numeric',
                'longitude' => 'required|numeric',
            ]);

        if($provider = \Auth::user()){

            $provider->latitude = $request->latitude;
            $provider->longitude = $request->longitude;
            $provider->save();

            return response()->json(['message' => 'Location Updated successfully!']);

        } else {
            return response()->json(['error' => 'Provider Not Found!']);
        }
    }

    /**
     * Toggle service availability of the provider.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function available(Request $request)
    {
        $this->validate($request, [
                'service_status' => 'required|in:active,offline',
            ]);

        $User = Auth::user();
        $User->service_status = $request->service_status;
        $User->save();

        return $User;
    }

    /**
     * Update password of the provider.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function password(Request $request)
    {
        $this->validate($request, [
                'password' => 'required|confirmed',
                'password_old' => 'required',
            ]);

        $Provider = \Auth::user();

        dd($Provider);

        if(password_verify($request->old_password, $Provider->password))
        {
            $Provider->password = bcrypt($request->password);
            $Provider->save();

            return response()->json(['message' => 'Password changed successfully!']);
        } else {
            return response()->json(['error' => 'Please enter correct password'], 422);
        }
    }
}
