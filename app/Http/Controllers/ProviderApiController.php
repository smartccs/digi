<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;

use Exception;
use Auth;

use App\UserRequests;

class ProviderApiController extends Controller
{

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */


    public function upcoming_request() {

        try{

            $Jobs = UserRequests::where('provider_id',\Auth::user()->id)
                    ->where('status','SCHEDULED')
                    ->with('service_type')
                    ->get();
            if(!empty($Jobs)){
                $map_icon = asset('asset/marker.png');
                foreach ($Jobs as $key => $value) {
                    $Jobs[$key]->static_map = "https://maps.googleapis.com/maps/api/staticmap?autoscale=1&size=320x130&maptype=terrian&format=png&visual_refresh=true&markers=icon:".$map_icon."%7C".$value->s_latitude.",".$value->s_longitude."&markers=icon:".$map_icon."%7C".$value->d_latitude.",".$value->d_longitude."&path=color:0x000000|weight:3|".$value->s_latitude.",".$value->s_longitude."|".$value->d_latitude.",".$value->d_longitude."&key=".env('GOOGLE_API_KEY');
                }
            }

            return $Jobs;
            
        }

        catch(Exception $e) {
            return response()->json(['error' => "Something Went Wrong"]);
        }

    }


    public function target(){

        try{

            $rides = UserRequests::where('provider_id',\Auth::guard('provider')->user()->id)
                        ->where('status','COMPLETED')
                        ->where('created_at', '>=', Carbon::today())
                        ->with('payment','service_type')
                        ->get();

            return response()->json(['rides' => $rides, 'rides_count' => $rides->count(), 'target' => Setting::get('daily_target','0')]);
        }   
        catch(Exception $e) {
            return response()->json(['error' => "Something Went Wrong"]);
        }
    }

}
