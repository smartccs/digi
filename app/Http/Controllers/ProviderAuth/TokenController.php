<?php

namespace App\Http\Controllers\ProviderAuth;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class TokenController extends Controller
{
    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */

    public function signup(Request $request)
    {
        $this->validate($request, [
                'social_unique_id' => ['required_if:login_by,facebook,google','unique:providers'],
                'device_type' => 'required|in:android,ios',
                'device_token' => 'required',
                'login_by' => 'required|in:manual,facebook,google',
                'first_name' => 'required|max:255',
                'last_name' => 'required|max:255',
                'email' => 'required|email|max:255|unique:providers',
                'mobile' => 'required|digits_between:6,13',
                'password' => 'required|min:6',
                'picture' => 'required|mimes:jpeg,jpg,bmp,png',
            ]);

        try{

            $Provider = $request->all();

            $Provider['is_available'] = 1;
            $Provider['is_activated'] = 1;
            $Provider['is_email_activated'] = 1;
            $Provider['email_activation_code'] = uniqid();

            $Provider['password'] = bcrypt($request->password);
            if($request->hasFile('picture')) {
                $Provider['picture'] = Helper::upload_picture($request->avatar);
            }

            $Provider = Provider::create($Provider);

                if($Provider) {

                    if($request->has('service_type')) {

                        $provider_services = ProviderService::where('provider_id' , $Provider->id)->get();

                        ProviderService::where('provider_id' , $Provider->id)->update(['is_available' => 0]);

                        $services =  array($request->service_type);

                        if(!is_array($request->service_type)) {
                            $services = explode(',',$request->service_type );
                        }

                        if($services) {
                            foreach ($services as $key => $service) {
                                $check_provider_service = ProviderService::where('provider_id' , $Provider->id)->where('service_type_id' , $service)->count();

                                if($check_provider_service) {
                                    Helper::save_provider_service($Provider->id,$service , 1);    
                                } else {
                                    Helper::save_provider_service($Provider->id,$service);
                                }
                            }    
                        
                        }
                    }
                }

            return $Provider;

        } catch (\Exception $e) {
             return response()->json(['error' => 'Something Went Wrong!']);
        }
        
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */

    public function authenticate(Request $request)
    {
        $this->validate($request, [
                'email' => 'required|email',
                'password' => 'required|min:6',
            ]);

        Config::set('auth.providers.users.model','App\Provider');

        // grab credentials from the request
        $credentials = $request->only('email', 'password');

        try {
            // attempt to verify the credentials and create a token for the user
            if (! $token = JWTAuth::attempt($credentials)) {
                return response()->json(['error' => 'invalid_credentials'], 401);
            }
        } catch (JWTException $e) {
            // something went wrong whilst attempting to encode the token
            return response()->json(['error' => 'could_not_create_token'], 500);
        }

        // all good so return the token
        return response()->json(compact('token'));
    }
}
