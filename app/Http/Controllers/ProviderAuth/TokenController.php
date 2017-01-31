<?php

namespace App\Http\Controllers\ProviderAuth;

use Illuminate\Http\Request;
use Illuminate\Database\QueryException;
use App\Http\Controllers\Controller;

use Tymon\JWTAuth\Exceptions\JWTException;

use Config;
use JWTAuth;

use App\Provider;

class TokenController extends Controller
{
    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */

    public function register(Request $request)
    {
        $this->validate($request, [
                'social_unique_id' => ['required_if:login_by,facebook,google','unique:providers'],
                'device_id' => 'required',
                'device_type' => 'required|in:android,ios',
                'device_token' => 'required',
                'first_name' => 'required|max:255',
                'last_name' => 'required|max:255',
                'email' => 'required|email|max:255|unique:providers',
                'mobile' => 'required|digits_between:6,13',
                'password' => 'required|min:6|confirmed',
            ]);

        try{

            $Provider = $request->all();
            $Provider['password'] = bcrypt($request->password);

            $Provider = Provider::create($Provider);
            
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

            return $Provider;

        } catch (QueryException $e) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['error' => 'Something went wrong, Please try again later!'], 500);
            }
            return abort(500);
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

        $User = \Auth::user();
        $User->access_token = $token;

        // all good so return the token
        return response()->json($User);
    }
}
