<?php

Route::get('/home', function () {
    $users[] = Auth::user();
    $users[] = Auth::guard()->user();
    $users[] = Auth::guard('provider')->user();

    //dd($users);

    return view('provider.home');
})->name('home');

/*
|--------------------------------------------------------------------------
| Provider Auth Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::group(['prefix' => 'provider'], function () {
  Route::get('/login', 'ProviderAuth\LoginController@showLoginForm');
  Route::post('/login', 'ProviderAuth\LoginController@login');
  Route::post('/logout', 'ProviderAuth\LoginController@logout');

  Route::get('/register', 'ProviderAuth\RegisterController@showRegistrationForm');
  Route::post('/register', 'ProviderAuth\RegisterController@register');

  Route::post('/password/email', 'ProviderAuth\ForgotPasswordController@sendResetLinkEmail');
  Route::post('/password/reset', 'ProviderAuth\ResetPasswordController@reset');
  Route::get('/password/reset', 'ProviderAuth\ForgotPasswordController@showLinkRequestForm');
  Route::get('/password/reset/{token}', 'ProviderAuth\ResetPasswordController@showResetForm');
});