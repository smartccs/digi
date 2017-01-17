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

		Route::post('/manual/request' , 'UserApiController@manual_create_request');


	});
});