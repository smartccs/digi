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


/*
|--------------------------------------------------------------------------
| User Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/dashboard', 'HomeController@index');
// user profiles
Route::get('/profile', 'HomeController@profile');
Route::get('/edit/profile', 'HomeController@edit_profile');
Route::post('/update/profile', 'HomeController@update_profile');
// update password
Route::get('/change/password', 'HomeController@change_password');
Route::post('/change/password', 'HomeController@update_password');

