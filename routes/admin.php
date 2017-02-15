<?php


/*
|--------------------------------------------------------------------------
| Admin Auth Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', 'AdminController@dashboard')->name('index');
Route::get('/dashboard', 'AdminController@dashboard')->name('dashboard');

Route::resource('user', 'Resource\UserResource');
Route::resource('provider', 'Resource\ProviderResource');
Route::resource('document', 'Resource\DocumentResource');
Route::resource('service', 'Resource\ServiceResource');
Route::resource('promocode', 'Resource\PromocodeResource');
Route::resource('user-review', 'Resource\UserReviewResource');
Route::resource('provider-review', 'Resource\ProviderReviewResource');
Route::get('provider/{id}/approve', 'Resource\ProviderResource@approve')->name('provider.approve');
Route::get('provider/{id}/decline', 'Resource\ProviderResource@decline')->name('provider.decline');
Route::get('provider/{id}/request', 'Resource\ProviderResource@request')->name('provider.request');
Route::get('provider/{id}/document', 'Resource\ProviderResource@document')->name('provider.document');
Route::get('user/{id}/request', 'Resource\UserResource@request')->name('user.request');

Route::get('user-map', 'AdminController@user_map')->name('user.map');
Route::get('provider-map', 'AdminController@provider_map')->name('provider.map');

Route::get('setting', 'AdminController@setting')->name('setting');
Route::post('setting/store', 'AdminController@setting_store')->name('setting.store');

Route::get('profile', 'AdminController@profile')->name('profile');
Route::post('profile/update', 'AdminController@profile_update')->name('profile.update');

Route::get('password', 'AdminController@password')->name('password');
Route::post('password/update', 'AdminController@password_update')->name('password.update');

Route::get('payment', 'AdminController@payment')->name('payment');
Route::get('payment/setting', 'AdminController@payment_setting')->name('payment.setting');

Route::get('help', 'AdminController@help')->name('help');

Route::get('request', 'AdminController@request_history')->name('request.history');

Route::get('scheduled/request', 'AdminController@scheduled_request')->name('scheduled.request');

Route::get('request/{id}/details', 'AdminController@request_details')->name('request.details');

Route::get('chat/{id}', 'AdminController@chat')->name('chat');


