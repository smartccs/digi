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

Route::get('/services' , 'Resources\ServiceResource@index');

// Authentication
Route::post('/register' , 'ProviderAuth\TokenController@register');
Route::post('/oauth/token' , 'ProviderAuth\TokenController@authenticate');

// Route::post('/oauth/token', 'AccessTokenController@issueToken');

// Route::post('/oauth/token/refresh', [
//     'middleware' => ['web', 'auth'],
//     'uses' => 'TransientTokenController@refresh',
// ]);

Route::group(['middleware' => ['provider.api']], function () {

    Route::group(['prefix' => 'profile'], function () {

        Route::get ('/' , 'ProviderResources\ProfileController@index');
        Route::post('/' , 'ProviderResources\ProfileController@update');
        Route::post('/password' , 'ProviderResources\ProfileController@password');
        Route::post('/location' , 'ProviderResources\ProfileController@location');
        Route::post('/available' , 'ProviderResources\ProfileController@available');

    });

    Route::group(['prefix' => 'trip'], function () {

        Route::post('/started', 'ProviderApiController@started');
        Route::post('/arrived', 'ProviderApiController@arrived');
        Route::post('/moving', 'ProviderApiController@start_service');
        Route::post('/reached', 'ProviderApiController@end_service');
        Route::post('/rating', 'ProviderApiController@rate_user');
        Route::post('/cancel', 'ProviderApiController@cancel_request');
        Route::post('/paid' , 'ProviderApiController@cod_paid');
        Route::post('/message' , 'ProviderApiController@message');

    });

    Route::group(['prefix' => 'requests'], function () {

        Route::get('/incoming', 'ProviderApiController@incoming_request');
        Route::get('/upcoming' , 'ProviderApiController@upcoming_request');
        Route::post('/accept', 'ProviderApiController@accept');
        Route::post('/reject', 'ProviderApiController@reject');

        Route::get('/status', 'ProviderApiController@request_status_check');
        Route::get('/history', 'ProviderApiController@history');
        Route::post('/show', 'ProviderApiController@request_details');

    });

});