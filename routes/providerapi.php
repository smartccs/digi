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

    Route::resource('trip', 'ProviderResources\TripController');

    Route::group(['prefix' => 'trip'], function () {

        Route::post('{id}', 'ProviderResources\TripController@accept');
        Route::post('{id}/rate', 'ProviderResources\TripController@rate');
        Route::post('{id}/cancel', 'ProviderResources\TripController@cancel');
        Route::post('{id}/message' , 'ProviderResources\TripController@message');

    });

    Route::group(['prefix' => 'requests'], function () {

        Route::get('/upcoming' , 'ProviderApiController@upcoming_request');
        Route::post('/accept', 'ProviderApiController@accept');
        Route::post('/reject', 'ProviderApiController@reject');

        Route::get('/history', 'ProviderResources\TripController@history');
        Route::get('/history/details', 'ProviderResources\TripController@history_details');
        Route::post('/show', 'ProviderApiController@request_details');

    });

});