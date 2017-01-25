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
Route::get('provider/{id}/approve', 'Resource\ProviderResource@approve')->name('provider.approve');
Route::get('provider/{id}/decline', 'Resource\ProviderResource@decline')->name('provider.decline');

Route::get('users/map', 'AdminController@user_map')->name('user.map');
Route::get('providers/map', 'AdminController@provider_map')->name('provider.map');


