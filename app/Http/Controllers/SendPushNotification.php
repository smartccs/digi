<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;
use App\ProviderDevice;
use Exception;

class SendPushNotification extends Controller
{
	/**
     * New Ride Accepted by a Driver.
     *
     * @return void
     */
    public function RideAccepted($request){

    	return $this->sendPushToUser($request->user_id);
    }

    /**
     * Sending Push to a user Device.
     *
     * @return void
     */
    public function sendPushToUser($user_id){

    	try{

	    	$user = User::findOrFail($user_id);

	    	if($user->device_type == 'ios'){

	    		return PushNotification::app('IOSUser')
		            ->to($user->device_token)
		            ->send('Hello World, i`m a push message');

	    	}elseif($user->device_type == 'android'){
	    		
	    		return PushNotification::app('AndroidUser')
		            ->to($user->device_token)
		            ->send('Hello World, i`m a push message');

	    	}

    	} catch(Exception $e){
    		return $e;
    	}

    }

    /**
     * Sending Push to a user Device.
     *
     * @return void
     */
    public function sendPushToProvider($provider_id){

    	try{

	    	$provider = ProviderDevice::where('provider_id',$provider_id)->first();

	    	if($provider->type == 'ios'){
	    		
	    		return PushNotification::app('IOSProvider')
		            ->to($user->device_token)
		            ->send('Hello World, i`m a push message');

	    	}elseif($provider->type == 'android'){
	    		
	    		return PushNotification::app('AndroidProvider')
		            ->to($user->device_token)
		            ->send('Hello World, i`m a push message');

	    	}

    	} catch(Exception $e){
    		return $e;
    	}

    }

}
