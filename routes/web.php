<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('index');
});

Route::get('/ride', function () {
    return view('ride');
});

Route::get('/drive', function () {
    return view('drive');
});

Auth::routes();

Route::get('/dashboard', 'HomeController@index');

Route::group(['prefix' => 'admin'], function () {
  Route::get('/login', 'AdminAuth\LoginController@showLoginForm');
  Route::post('/login', 'AdminAuth\LoginController@login');
  Route::post('/logout', 'AdminAuth\LoginController@logout');

  Route::get('/register', 'AdminAuth\RegisterController@showRegistrationForm');
  Route::post('/register', 'AdminAuth\RegisterController@register');

  Route::post('/password/email', 'AdminAuth\ForgotPasswordController@sendResetLinkEmail');
  Route::post('/password/reset', 'AdminAuth\ResetPasswordController@reset');
  Route::get('/password/reset', 'AdminAuth\ForgotPasswordController@showLinkRequestForm');
  Route::get('/password/reset/{token}', 'AdminAuth\ResetPasswordController@showResetForm');
});

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
