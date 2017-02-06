<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;
use App\Provider;
use App\Settings;
use App\Admin;
use App\UserPayment;
use App\UserRequests;
use App\Helpers\Helper;
use Auth;


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
    public function dashboard()
    {
        return view('admin.dashboard');
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

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Provider  $provider
     * @return \Illuminate\Http\Response
     */
    public function setting()
    {
        return view('admin.setting.site-setting');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Provider  $provider
     * @return \Illuminate\Http\Response
     */
    public function setting_store(Request $request)
    {
        $this->validate($request,[
                'site_icon' => 'mimes:jpeg,jpg,bmp,png||max:5242880',
                'site_logo' => 'mimes:jpeg,jpg,bmp,png||max:5242880',
            ]);

        $settings = Settings::all();

            foreach ($settings as $setting) {

                $key = $setting->key;
               
                $temp_setting = Settings::find($setting->id);

                if($temp_setting->key == 'site_icon'){
                    
                    if($request->file('site_icon') == null){
                        $icon = $temp_setting->value;
                    } else {
                        if($temp_setting->value) {
                            Helper::delete_picture($temp_setting->value);
                        }
                        $icon = Helper::upload_picture($request->file('site_icon'));
                    }

                    $temp_setting->value = $icon;

                }else if($temp_setting->key == 'site_logo'){

                    if($request->file('site_logo') == null){
                        $logo = $temp_setting->value;
                    } else {
                        if($temp_setting->value) {
                            Helper::delete_picture($temp_setting->value);
                        }
                        $logo = Helper::upload_picture($request->file('site_logo'));
                    }

                    $temp_setting->value = $logo;

                }else if($temp_setting->key == 'email_logo'){

                    if($request->file('email_logo') == null){
                        $logo = $temp_setting->value;
                    } else {
                        if($temp_setting->value) {
                            Helper::delete_picture($temp_setting->value);
                        }
                        $logo = Helper::upload_picture($request->file('email_logo'));
                    }

                    $temp_setting->value = $logo;

                }else if($temp_setting->key == 'manual_request'){

                    if($request->$key==1)
                    {
                        $temp_setting->value   = 1;
                    }

                }else if($temp_setting->key == 'card'){
                    if($request->$key == 'on')
                    {
                        $temp_setting->value   = 1;
                    }
                    else
                    {
                        $temp_setting->value = 0;
                    }
                }else if($temp_setting->key == 'paypal'){
                    if($request->$key == 'on')
                    {
                        $temp_setting->value   = 1;
                    }
                    else
                    {
                        $temp_setting->value = 0;
                    }
                }else if($request->$key != ''){

                    $temp_setting->value = $request->$key;
                
                }
                
                $temp_setting->save();
                  
            }
        
        return back()->with('flash_success','Settings Updated Successfully');
    }


    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Provider  $provider
     * @return \Illuminate\Http\Response
     */
    public function profile()
    {
        return view('admin.account.profile');
    }



    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Provider  $provider
     * @return \Illuminate\Http\Response
     */
    public function profile_update(Request $request)
    {
        $this->validate($request,[
            'name' => 'required|max:255',
            'paypal_email' => 'required|max:255|email',
            'email' => 'required|email|max:255',
            'mobile' => 'required|digits_between:6,13',
            'picture' => 'mimes:jpeg,jpg,bmp,png|max:5242880',
                       
        ]);

        try{
            $admin = Admin::find(Auth::guard('admin')->user()->id);
            $admin->name = $request->name;
            $admin->email = $request->email;
            $admin->mobile = $request->mobile;
            if($request->hasFile('picture')){
                if($admin->picture != ""){
                    Helper::delete_avatar($admin->picture);
                }
                $admin->picture = Helper::upload_avatar($request->picture);
            }
            $admin->gender = $request->gender;
            $admin->paypal_email = $request->paypal_email;
            $admin->save();

            return redirect()->back()->with('flash_success','Profile Updated');
        }

        catch (ModelNotFoundException $e) {
             return back()->with('flash_error','Something Went Wrong!');
        }
        
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Provider  $provider
     * @return \Illuminate\Http\Response
     */
    public function password()
    {
        return view('admin.account.change-password');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Provider  $provider
     * @return \Illuminate\Http\Response
     */
    public function password_update(Request $request)
    {
        $this->validate($request,[
            'old_password' => 'required',
            'password' => 'required|min:6|confirmed',
        ]);

        try{

           $Admin = Admin::find(Auth::guard('admin')->user()->id);

            if(password_verify($request->old_password, $Admin->password))
            {
                $Admin->password = bcrypt($request->password);
                $Admin->save();

                return redirect()->back()->with('flash_success','Password Updated');
            }
        }

        catch (ModelNotFoundException $e) {
             return back()->with('flash_error','Something Went Wrong!');
        }
        
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Provider  $provider
     * @return \Illuminate\Http\Response
     */
    public function payment()
    {
        try{
             $payments = UserPayment::leftJoin('user_requests','user_requests.id','=','user_payments.request_id')
                        ->leftJoin('users','users.id','=','user_requests.user_id')
                        ->leftJoin('providers','providers.id','=','user_requests.confirmed_provider')
                        ->select('user_payments.*','users.first_name as user_first_name','users.last_name as user_last_name',
                                'providers.first_name as provider_first_name','providers.last_name as provider_last_name')
                        ->orderBy('user_payments.created_at','desc')
                        ->get();
            
            return view('admin.payment.payment-history', compact('payments'));
        }

        catch (ModelNotFoundException $e) {
             return back()->with('flash_error','Something Went Wrong!');
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Provider  $provider
     * @return \Illuminate\Http\Response
     */
    public function payment_setting()
    {
        return view('admin.payment.payment-setting');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Provider  $provider
     * @return \Illuminate\Http\Response
     */
    public function help(){

        try{
            $str = file_get_contents('http://appoets.com/help.json');
            $Data = json_decode($str, true);
            return view('admin.help', compact('Data'));
        }

        catch (ModelNotFoundException $e) {
             return back()->with('flash_error','Something Went Wrong!');
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Provider  $provider
     * @return \Illuminate\Http\Response
     */
    public function request_history(){

        try{

            $requests = UserRequests::RequestHistory()->get();

            return view('admin.request.request-history', compact('requests'));

        }

        catch (ModelNotFoundException $e) {
             return back()->with('flash_error','Something Went Wrong!');
        }

    }


    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Provider  $provider
     * @return \Illuminate\Http\Response
     */
    public function request_details($id){

        try{

            $request = UserRequests::where('user_requests.id',$id)
                ->leftJoin('providers', 'user_requests.confirmed_provider', '=', 'providers.id')
                ->leftJoin('users', 'user_requests.user_id', '=', 'users.id')
                ->leftJoin('user_payments', 'user_requests.id', '=', 'user_payments.request_id')
                ->select('users.first_name as user_first_name', 'users.last_name as user_last_name', 
                        'providers.first_name as provider_first_name', 'providers.last_name as provider_last_name',
                        'users.id as user_id', 'providers.id as provider_id', 'user_requests.*', 
                        'user_payments.payment_mode as payment_mode', 'user_payments.status as payment_status', 
                        'user_payments.total_time as total_time','user_payments.base_price as base_price', 
                        'user_payments.time_price as time_price', 'user_payments.tax_price as tax', 
                        'user_payments.total as total_amount')
                ->first();

            return view('admin.request.request-details', compact('request'));
        }

        catch (ModelNotFoundException $e) {
             return back()->with('flash_error','Something Went Wrong!');
        }

    }


    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Provider  $provider
     * @return \Illuminate\Http\Response
     */
    public function chat($id){

        //

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Provider  $provider
     * @return \Illuminate\Http\Response
     */
    public function scheduled_request(){

        try{

            $requests = UserRequests::where('later' , DEFAULT_TRUE)
                ->RequestHistory()
                ->get();

            return view('admin.request.scheduled-request', compact('requests'));
        }

        catch (ModelNotFoundException $e) {
             return back()->with('flash_error','Something Went Wrong!');
        }

    }

}
