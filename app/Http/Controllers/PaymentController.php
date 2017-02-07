<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;
use App\Card;
use Exception;
use Auth;
use Cartalyst\Stripe\Laravel\Facades\Stripe;

class PaymentController extends Controller
{
    /**
     * Create a card using stripe.
     *
     * @return \Illuminate\Http\Response
     */
    public function create_card(Request $request)
    {
    	$this->validate($request,[
                'stripe_token' => 'required'
    		]);

    	try{

	    	$customer_id = $this->customer_id();
	    	$card = $stripe->cards()->create($customer_id, $request->stripe_token);

	    	$create_card = new Card;
	    	$create_card->user_id = Auth::user()->id;
	    	$create_card->card_id = $card['id'];
	    	$create_card->last_four = $card['last4'];
	    	$create_card->save();

	    	return response()->json(['message' => 'Card Added']); 

    	} catch(Exception $e){
            return response()->json(['error' => $e->getMessage()], 500);
    	}	

    }


    /**
     * Get a stripe customer id.
     *
     * @return \Illuminate\Http\Response
     */
    public function customer_id()
    {
		if(Auth::user()->stripe_cust_id != null){

			return Auth::user()->stripe_cust_id;

		}else{

			try{

				$stripe = new Stripe();

				$customer = $stripe->customers()->create([
				    'email' => Auth::user()->email,
				]);

				User::where('id',Auth::user()->id)->update(['stripe_cust_id' => $customer['id']]);
				return $customer['id'];

			} catch(Exception $e){
				return $e;
			}
		}
    }


    /**
     * delete a card using stripe.
     *
     * @return \Illuminate\Http\Response
     */
    public function destory_card(Request $request)
    {
    	$this->validate($request,[
                'card_id' => 'required|integer|exists:cards,id,user_id,'.Auth::user()->id,
    		]);

    	try{

    		Cards::where('card_id',$request->card_id)->delete();

    		$stripe = new Stripe();

    		$stripe->cards()->delete(Auth::user()->stripe_cust_id, $request->card_id);

	    	return response()->json(['message' => 'Card Deleted']); 

    	} catch(Exception $e){
            return response()->json(['error' => $e->getMessage()], 500);
    	}	

    }


    /**
     * get all cards using stripe.
     *
     * @return \Illuminate\Http\Response
     */
    public function card()
    {

    	try{

    		$stripe = new Stripe();

    		$cards = $stripe->cards()->all(Auth::user()->stripe_cust_id);

	    	return response()->json(['cards' => $cards]); 

    	} catch(Exception $e){
            return response()->json(['error' => $e->getMessage()], 500);
    	}	

    }
}
