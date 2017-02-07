<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;
use App\Card;
use Exception;
use Auth;

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
	    	$this->set_stripe();
	    	$customer = \Stripe\Customer::retrieve($customer_id);
	    	$card = $customer->sources->create(["source" => $request->stripe_token]);

	    	$create_card = new Card;
	    	$create_card->user_id = Auth::user()->id;
	    	$create_card->card_id = $card['id'];
	    	$create_card->last_four = $card['last4'];
	    	$create_card->brand = $card['brand'];
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

				$stripe = $this->set_stripe();

				$customer = \Stripe\Customer::create([
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
     * setting stripe.
     *
     * @return \Illuminate\Http\Response
     */
    public function set_stripe(){
    	return \Stripe\Stripe::setApiKey(env('STRIPE_SECRET'));
    }

    /**
     * delete a card using stripe.
     *
     * @return \Illuminate\Http\Response
     */
    public function destory_card(Request $request)
    {
    	$this->validate($request,[
                'card_id' => 'required|integer|exists:cards,card_id,user_id,'.Auth::user()->id,
    		]);

    	try{

    		$this->set_stripe();

    		$customer = \Stripe\Customer::retrieve(Auth::user()->stripe_cust_id);
    		$customer->sources->retrieve($request->card_id)->delete();

    		Cards::where('card_id',$request->card_id)->delete();

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

    		$this->set_stripe();

    		$cards = \Stripe\Customer::retrieve(Auth::user()->stripe_cust_id)->sources->all(array('object' => 'card'));

	    	return response()->json(['cards' => $cards]); 

    	} catch(Exception $e){
            return response()->json(['error' => $e->getMessage()], 500);
    	}	

    }
}
