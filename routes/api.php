<?php

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::post('/signup' , 'UserApiController@signup');

Route::group(['middleware' => ['auth:api']], function () {

	// user profile

	Route::post('/change/password' , 'UserApiController@change_password');

	Route::post('/update/location' , 'UserApiController@update_location');

	Route::get('/details' , 'UserApiController@details');

	Route::post('/update/profile' , 'UserApiController@update_profile');

	// services

	Route::get('/services' , 'UserApiController@services');

	// provider

	Route::post('/rate/provider' , 'UserApiController@rate_provider');

	// request

	Route::post('/send/request' , 'UserApiController@send_request');

	Route::post('/cancel/request' , 'UserApiController@cancel_request');
	
	Route::get('/request/check' , 'UserApiController@request_status_check');

	Route::get('/trips' , 'UserApiController@trips');
	
	Route::get('/trip/details' , 'UserApiController@trip_details');

	// payment

	Route::post('/pay/now' , 'UserApiController@paynow');

	Route::get('/payment/modes' , 'UserApiController@payment_modes');

	Route::post('/change/mode' , 'UserApiController@payment_mode_update');

	Route::post('/delete/card' , 'UserApiController@delete_card');

	Route::post('/default/card' , 'UserApiController@default_card');

	Route::post('/add/money' , 'UserApiController@add_money');

	// chat

	Route::get('/message' , 'UserApiController@message');

	Route::get('/estimated/fare' , 'UserApiController@estimated_fare');


	Route::group(['prefix' => 'card'], function () {

		Route::post('/add' , 'PaymentController@create_card');

	});
	
});
