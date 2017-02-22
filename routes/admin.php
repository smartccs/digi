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

Route::group(['as' => 'provider.'], function () {
    Route::get('review/provider', 'AdminController@provider_review')->name('review');
    Route::get('provider/{id}/approve', 'Resource\ProviderResource@approve')->name('approve');
    Route::get('provider/{id}/disapprove', 'Resource\ProviderResource@disapprove')->name('disapprove');
    Route::get('provider/{id}/request', 'Resource\ProviderResource@request')->name('request');
    Route::resource('provider/{provider}/document', 'Resource\ProviderDocumentResource');
});

Route::get('review/user', 'AdminController@user_review')->name('user.review');
Route::get('user/{id}/request', 'Resource\UserResource@request')->name('user.request');

Route::get('map/user', 'AdminController@user_map')->name('user.map');
Route::get('map/provider', 'AdminController@provider_map')->name('provider.map');

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
