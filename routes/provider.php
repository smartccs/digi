<?php

/*
|--------------------------------------------------------------------------
| Provider Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', 'ProviderController@index')->name('index');
Route::get('/trips', 'ProviderResources\TripController@history')->name('trips');

Route::get('/profile', 'ProviderResources\ProfileController@show')->name('profile.show');
Route::get('/profile/edit', 'ProviderResources\ProfileController@show')->name('profile.edit');
Route::post('/profile', 'ProviderResources\ProfileController@update')->name('profile.update');

Route::get('/profile/password', 'ProviderController@index')->name('password');