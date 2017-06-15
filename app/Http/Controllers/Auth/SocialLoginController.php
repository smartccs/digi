<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests;

use Socialite;
use App\User;
use App\Provider;
use Exception;
use Validator;

class SocialLoginController extends Controller
{
	
    public function redirectToFaceBook(){
        return Socialite::driver('facebook')->redirect();
    }
    
    public function providerToFaceBook(){
        return Socialite::driver('facebook')->with(['state' => 'provider'])->redirect();
    }

    public function handleFacebookCallback(Request $request){
        $AccessToken = Socialite::driver('facebook')->getAccessTokenResponse($request->code);
        $AccessParam = array_keys($AccessToken);
        $AccessField = array_shift($AccessParam);
        $FacebookToken = json_decode($AccessField);
        $token=$FacebookToken->access_token;
        if($token){
            $facebook = Socialite::driver('facebook')->userFromToken($token);
            $guard = request()->input('state');
            if($guard == 'provider') {
                if($facebook->id){
                    $FacebookSql = Provider::where('social_unique_id',$facebook->id);
                    if($facebook->email !=""){
                        $FacebookSql->orWhere('email',$facebook->email);
                    }
                    $AuthUser = $FacebookSql->first();
                    if($AuthUser){
                        $AuthUser->social_unique_id=$facebook->id;
                        $AuthUser->save();
                        Auth::guard('provider')->loginUsingId($AuthUser->id);
                        return redirect('provider');
                    }else{   
                        $new=new Provider();
                        $new->email=$facebook->email;
                        $new->first_name=$facebook->name;
                        $new->last_name='';
                        $new->password=$facebook->id;
                        $new->social_unique_id=$facebook->id;
                        //$new->mobile=$facebook->mobile;
                        $new->avatar=$facebook->avatar;
                        $new->login_by="facebook";
                        $new->save();
                        Auth::guard('provider')->loginUsingId($new->id);
                        return redirect('provider');
                    }
                } else {
                    return redirect('provider');
                }
            } else {
                if($facebook->id){
                    $FacebookSql = User::where('social_unique_id',$facebook->id);
                    if($facebook->email !=""){
                        $FacebookSql->orWhere('email',$facebook->email);
                    }
                    $AuthUser = $FacebookSql->first();
                    if($AuthUser){
                        $AuthUser->social_unique_id=$facebook->id;
                        $AuthUser->save();
                        Auth::loginUsingId($AuthUser->id);
                        return redirect('dashboard');
                    }else{   
                        $new=new User();
                        $new->email=$facebook->email;
                        $new->first_name=$facebook->name;
                        $new->last_name='';
                        $new->password=$facebook->id;
                        $new->social_unique_id=$facebook->id;
                        //$new->mobile=$facebook->mobile;
                        $new->picture=$facebook->avatar;
                        $new->login_by="facebook";
                        $new->save();
                        Auth::loginUsingId($new->id);
                        return redirect('dashboard');
                    }
                }else{
                    return redirect('dashboard');
                }
            }
        }else{
           return redirect()->route('/register');
        }
    }


    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function facebookViaAPI(Request $request) { 

        $validator = Validator::make(
            $request->all(),
            [
                'device_type' => 'required|in:android,ios',
                'device_token' => 'required',
                'accessToken'=>'required',
                'device_id' => 'required',
                'login_by' => 'required|in:manual,facebook,google'
            ]
        );
    	
        if($validator->fails()) {
            return response()->json(['status'=>false,'message' => $validator->messages()->all()]);
        }

        $user = Socialite::driver('facebook')->stateless();
        $FacebookDrive = $user->userFromToken( $request->accessToken);
       
        try{

        	$FacebookSql = User::where('social_unique_id',$FacebookDrive->id);
            if($FacebookDrive->email !=""){
                $FacebookSql->orWhere('email',$FacebookDrive->email);
            }
            $AuthUser = $FacebookSql->first();
            if($AuthUser){
                $AuthUser->social_unique_id=$FacebookDrive->id; 
            	$AuthUser->device_type=$request->device_type;
                $AuthUser->device_token=$request->device_token;
                $AuthUser->device_id=$request->device_id;
                $AuthUser->login_by="facebook";
                $AuthUser->save();  
            }else{   
                $AuthUser=new User();
                $AuthUser->email=$FacebookDrive->email;
                $AuthUser->first_name=$FacebookDrive->name;
                $AuthUser->last_name='';
                $AuthUser->password=$FacebookDrive->id;
                $AuthUser->social_unique_id=$FacebookDrive->id;
                $AuthUser->device_type=$request->device_type;
                $AuthUser->device_token=$request->device_token;
                $AuthUser->device_id=$request->device_id;
                //$new->mobile=$facebook->mobile;
                $AuthUser->picture=$FacebookDrive->avatar;
                $AuthUser->login_by="facebook";
                $AuthUser->save();
            }    
            if($AuthUser){ 
                $userToken = $AuthUser->token()?:$AuthUser->createToken('socialLogin');
                return response()->json([
                        "status" => true,
                        "token_type" => "Bearer",
                        "access_token" => $userToken->accessToken
                        ]);
            }else{
                return response()->json(['status'=>false,'message' => "Invalid credentials!"]);
            }  
        } catch (Exception $e) {
            return response()->json(['status'=>false,'message' => trans('api.something_went_wrong')]);
        }
    }

    public function redirectToGoogle(){
        return Socialite::driver('google')->redirect();
    }

    public function providerToGoogle(){
        return Socialite::driver('google')->with(['state' => 'provider'])->redirect();
    }

    public function handleGoogleCallback(){

        try{
            $google = Socialite::driver('google')->user();
            if($google){
                $guard = request()->input('state');
                if($guard == 'provider') {
                    if($google->id){
                        $GoogleSql = Provider::where('social_unique_id',$google->id);
                        if($google->email !=""){
                            $GoogleSql->orWhere('email',$google->email);
                        }
                        $AuthUser = $GoogleSql->first();
                        if($AuthUser){ 
                            $AuthUser->social_unique_id=$google->id;
                            $AuthUser->save();  
                            Auth::guard('provider')->loginUsingId($AuthUser->id);
                            return redirect()->to('provider');
                        }else{   
                            $new=new Provider();
                            $new->email=$google->email;
                            $new->first_name=$google->name;
                            $new->last_name='';
                            $new->password=$google->id;
                            $new->social_unique_id=$google->id;
                            //$new->mobile=$google->mobile;
                            $new->avatar=$google->avatar;
                            $new->login_by="google";
                            $new->save();
                            Auth::guard('provider')->loginUsingId($new->id);
                            return redirect()->route('provider');
                        }
                    }else{
                        return redirect()->route('provider');
                    }
                } else {
                    if($google->id){
                        $GoogleSql = User::where('social_unique_id',$google->id);
                        if($google->email !=""){
                            $GoogleSql->orWhere('email',$google->email);
                        }
                        $AuthUser = $GoogleSql->first();
                        if($AuthUser){ 
                            $AuthUser->social_unique_id=$google->id;
                            $AuthUser->save();  
                             Auth::loginUsingId($AuthUser->id);
                             return redirect()->to('dashboard');
                        }else{   
                            $new=new User();
                            $new->email=$google->email;
                            $new->first_name=$google->name;
                            $new->last_name='';
                            $new->password=$google->id;
                            $new->social_unique_id=$google->id;
                            //$new->mobile=$google->mobile;
                            $new->picture=$google->avatar;
                            $new->login_by="google";
                            $new->save();
                            return redirect()->route('dashboard');
                        }
                    }else{
                        return redirect()->route('dashboard');
                    }
                }
            }else{
               return redirect()->route('/register');
            }

        } catch (Exception $e) {
            return back()->with('flash_errors', 'Google driver not found');
        }
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function googleViaAPI(Request $request) { 

    	$validator = Validator::make(
            $request->all(),
            [
                'device_type' => 'required|in:android,ios',
                'device_token' => 'required',
                'accessToken'=>'required',
                'device_id' => 'required',
                'login_by' => 'required|in:manual,facebook,google'
            ]
        );
        
        if($validator->fails()) {
            return response()->json(['status'=>false,'message' => $validator->messages()->all()]);
        }
        $user = Socialite::driver('google')->stateless();
        $GoogleDrive = $user->userFromToken( $request->accessToken);
       
        try{

        	
            $GoogleSql = User::where('social_unique_id',$GoogleDrive->id);
            if($GoogleDrive->email !=""){
                $GoogleSql->orWhere('email',$GoogleDrive->email);
            }
            $AuthUser = $GoogleSql->first();
            if($AuthUser){
                $AuthUser->social_unique_id=$GoogleDrive->id; 
              	$AuthUser->device_type=$request->device_type;
                $AuthUser->device_token=$request->device_token;
                $AuthUser->device_id=$request->device_id;
                $AuthUser->login_by="google";
                $AuthUser->save();
            }else{   
                $AuthUser=new User();
                $AuthUser->email=$GoogleDrive->email;
                $AuthUser->first_name=$GoogleDrive->name;
                $AuthUser->last_name='';
                $AuthUser->password=$GoogleDrive->id;
                $AuthUser->social_unique_id=$GoogleDrive->id;
                $AuthUser->device_type=$request->device_type;
                $AuthUser->device_token=$request->device_token;
                $AuthUser->device_id=$request->device_id;
                //$new->mobile=$facebook->mobile;
                $AuthUser->picture=$GoogleDrive->avatar;
                $AuthUser->login_by="google";
                $AuthUser->save();
            }    
            if($AuthUser){ 
                $userToken = $AuthUser->token()?:$AuthUser->createToken('socialLogin');
                return response()->json([
                        "status" => true,
                        "token_type" => "Bearer",
                        "access_token" => $userToken->accessToken
                        ]);
            }else{
                return response()->json(['status'=>false,'message' => "Invalid credentials!"]);
            }  
        } catch (Exception $e) {
            return response()->json(['status'=>false,'message' => trans('api.something_went_wrong')]);
        }
    }

}
