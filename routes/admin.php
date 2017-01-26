<?php

Route::get('/home', function () {
    $users[] = Auth::user();
    $users[] = Auth::guard()->user();
    $users[] = Auth::guard('admin')->user();

    //dd($users);

    return view('admin.home');
})->name('home');

Route::resource('user', 'Resource\UserResource');
Route::resource('provider', 'Resource\ProviderResource');
Route::resource('document', 'Resource\DocumentResource');
Route::resource('service', 'Resource\ServiceResource');
Route::resource('promocode', 'Resource\PromocodeResource');
Route::resource('user-review', 'Resource\UserReviewResource');
Route::resource('provider-review', 'Resource\ProviderReviewResource');
Route::get('provider/{id}/approve', 'Resource\ProviderResource@approve')->name('provider.approve');
Route::get('provider/{id}/decline', 'Resource\ProviderResource@decline')->name('provider.decline');

Route::get('user-map', 'AdminController@user_map')->name('user.map');
Route::get('provider-map', 'AdminController@provider_map')->name('provider.map');
Route::get('setting', 'AdminController@setting')->name('setting');
Route::post('setting/store', 'AdminController@setting_store')->name('setting.store');


