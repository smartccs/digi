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

Route::group(['prefix' => 'user'], function () {

    Route::post('/signup' , 'UserApiController@signup');

	Route::group(['middleware' => ['auth:api']], function () {

		Route::post('/change/password' , 'UserApiController@change_password');

		Route::post('/update/location' , 'UserApiController@update_location');

		Route::get('/details' , 'UserApiController@details');

		Route::get('/services' , 'UserApiController@services');

		Route::get('/guest/provider/list' , 'UserApiController@guest_provider_list');

		Route::get('/guest/provider/availability' , 'UserApiController@guest_provider_availability');

		Route::get('/provider/details' , 'UserApiController@provider_details');

		Route::post('/update/profile' , 'UserApiController@update_profile');

		Route::post('/send/request' , 'UserApiController@send_request');

		Route::post('/cancel/request' , 'UserApiController@cancel_request');
		
		Route::post('/later/request' , 'UserApiController@request_later');

		Route::post('/manual/request' , 'UserApiController@manual_create_request');

		Route::post('/manual/scheduled/request' , 'UserApiController@manual_scheduled_request');

		Route::get('/request/check' , 'UserApiController@request_status_check');

		Route::post('/pay/now' , 'UserApiController@paynow');

		Route::post('/rate/provider' , 'UserApiController@rate_provider');

		Route::post('/add/provider' , 'UserApiController@add_fav_provider');

		Route::get('/fav/provider' , 'UserApiController@fav_providers');

		Route::get('/delete/provider' , 'UserApiController@delete_fav_provider');

		Route::get('/history' , 'UserApiController@history');

		Route::get('/request' , 'UserApiController@single_request');

		Route::get('/payment/modes' , 'UserApiController@get_payment_modes');

		Route::post('/change/modes' , 'UserApiController@payment_mode_update');

		Route::post('/add/card' , 'UserApiController@add_card');

		Route::post('/delete/card' , 'UserApiController@delete_card');

		Route::post('/default/card' , 'UserApiController@default_card');

		Route::get('/message' , 'UserApiController@message');
		
		Route::get('/upcoming' , 'UserApiController@get_upcoming_request');

		Route::post('/add/money' , 'UserApiController@add_money');


	});
});