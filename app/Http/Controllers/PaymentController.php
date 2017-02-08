<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\UserRequestPayment;
use App\UserRequests;
use App\Card;

use Exception;
use Auth;

class PaymentController extends Controller
{
	/**
     * payment for user.
     *
     * @return \Illuminate\Http\Response
     */
    public function payment(Request $request){

    	$this->validate($request, [
    			'request_id' => 'required|exists:user_request_payments,request_id|exists:user_requests,id,paid,0,user_id,'.Auth::user()->id
    		]);


    	$UserRequest = UserRequests::find($request->request_id);

    	if($UserRequest->payment_mode == 'CARD'){

    		$RequestPayment = UserRequestPayment::where('request_id',$request->request_id)->first(); 

    		$StripeCharge = $RequestPayment->total * 100;


    		try{

    			$Card = Card::where('user_id',Auth::user()->id)->where('is_default',1)->first();

	    		\Stripe\Stripe::setApiKey(env('STRIPE_SECRET'));

	    		$Charge = \Stripe\Charge::create(array(
					  "amount" => $StripeCharge,
					  "currency" => "usd",
					  "customer" => Auth::user()->stripe_cust_id,
					  "card" => $Card->card_id,
					  "description" => "Charge for ".Auth::user()->email,
					  "receipt_email" => Auth::user()->email
					));

	    		$RequestPayment->payment_id = $Charge["id"];
	    		$RequestPayment->payment_mode = 'CARD';
	    		$RequestPayment->save();

	    		$UserRequest->paid = 1;
	    		$UserRequest->status = 'COMPLETED';
	    		$UserRequest->save();

            	return response()->json(['message' => 'Paid']); 

    		} catch(\Stripe\StripeInvalidRequestError $e){
    			return response()->json(['error' => $e->getMessage()], 500);
    		} 

    	}
    }

}
