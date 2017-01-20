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

Route::group(['prefix' => 'provider'], function () {

    Route::post('/signup' , 'ProviderApiController@signup');

    Route::post('/oauth/token' , 'ProviderApiController@authenticate');

    Route::group(['middleware' => ['ProviderApiMiddleware']], function () {

		Route::post('/change/password' , 'ProviderApiController@change_password');

		Route::get('/details' , 'ProviderApiController@details');

		Route::post('/update/location' , 'ProviderApiController@update_location');

		Route::post('/update/profile' , 'UserApiController@update_profile');

		Route::get('/available' , 'ProviderApiController@available');

		Route::post('/update/available' , 'ProviderApiController@update_available');

		Route::post('/accept' , 'ProviderApiController@accept');

		Route::post('/reject' , 'ProviderApiController@reject');

		Route::post('/started' , 'ProviderApiController@started');

		Route::post('/arrived' , 'ProviderApiController@arrived');

		Route::post('/start/service' , 'ProviderApiController@start_service');
		
		Route::post('/end/service' , 'ProviderApiController@end_service');


    });

});