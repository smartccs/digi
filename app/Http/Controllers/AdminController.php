<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;
use App\Provider;
use App\Settings;
use App\Admin;
use App\UserRequestRating;
use App\UserPayment;
use App\UserRequests;
use App\ServiceType;
use App\UserRequestPayment;
use App\Helpers\Helper;
use Auth;
use Exception;


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
        try{
            
            $rides = UserRequests::with('user')->orderBy('id','desc')->get();
            $cancel_rides = UserRequests::where('status','CANCELLED')->count();
            $service = ServiceType::count();
            $revenue = UserRequestPayment::sum('total');
            $providers = Provider::take(10)->orderBy('rating','desc')->get();
            return view('admin.dashboard',compact('providers','service','rides','cancel_rides','revenue'));
        }
        catch(Exception $e){
            return redirect()->route('admin.user.index')->with('flash_error','Something Went Wrong with Dashboard!');
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Provider  $provider
     * @return \Illuminate\Http\Response
     */
    public function user_map()
    {
        try{

            $Users = User::where('latitude', '!=', 0)->where('longitude', '!=', 0)->get();
            return view('admin.map.user_map', compact('Users'));
        }
        catch(Exception $e){
            return redirect()->route('admin.setting')->with('flash_error','Something Went Wrong!');
        }
    }

   	/**
     * Remove the specified resource from storage.
     *
     * @param  \App\Provider  $provider
     * @return \Illuminate\Http\Response
     */
    public function provider_map()
    {
        try{
            $Providers = Provider::where('latitude', '!=', 0)->where('longitude', '!=', 0)->with('service')->get();
            return view('admin.map.provider_map', compact('Providers'));
        }
        catch(Exception $e){
            return redirect()->route('admin.setting')->with('flash_error','Something Went Wrong!');
        }
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

                }else if($temp_setting->key == 'CARD'){
                    if($request->$key == 'on')
                    {
                        $temp_setting->value = 1;
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
                Storage::delete($admin->picture);
                $admin->picture = $request->picture->store('admin/profile');  
            }
            $admin->gender = $request->gender;
            $admin->save();

            return redirect()->back()->with('flash_success','Profile Updated');
        }

        catch (Exception $e) {
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

        catch (Exception $e) {
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
             $payments = UserRequests::where('paid',1)
                    ->has('user')
                    ->has('provider')
                    ->has('payment')
                    ->orderBy('user_requests.created_at','desc')
                    ->get();
            
            return view('admin.payment.payment-history', compact('payments'));
        }

        catch (Exception $e) {
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

        catch (Exception $e) {
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

        catch (Exception $e) {
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
                ->with('provider','user','payment')
                ->first();

            return view('admin.request.request-details', compact('request'));
        }

        catch (Exception $e) {
             return back()->with('flash_error','Something Went Wrong!');
        }

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

        catch (Exception $e) {
             return back()->with('flash_error','Something Went Wrong!');
        }

    }

    /**
     * User Rating.
     *
     * @return \Illuminate\Http\Response
     */
    public function user_review()
    {
        try{
            $Reviews = UserRequestRating::where('user_id','!=',0)->with('user','provider')->get();
            return view('admin.review.user_review',compact('Reviews'));
        }
        catch(Exception $e){
            return redirect()->route('admin.setting')->with('flash_error','Something Went Wrong!');
        }
    }

    /**
     * Provider Rating.
     *
     * @return \Illuminate\Http\Response
     */
    public function provider_review()
    {
        try{
            $Reviews = UserRequestRating::where('provider_id','!=',0)->with('user','provider')->get();
            return view('admin.review.provider_review',compact('Reviews'));
        }
        catch(Exception $e){
            return redirect()->route('admin.setting')->with('flash_error','Something Went Wrong!');
        }
    }

}
