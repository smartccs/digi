<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;
use App\UserRating;
use App\ProviderRating;
use App\Provider;
use App\Settings;
use App\Helpers\Helper;


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

                }else if($request->$key != ''){

                    $temp_setting->value = $request->$key;
                
                }
                
                $temp_setting->save();
                  
            }
        
        return back()->with('flash_success','Settings Updated Successfully');
    }


}
