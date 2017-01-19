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

    	// Route::get('/sample' , 'ProviderApiController@sample');

    });

});