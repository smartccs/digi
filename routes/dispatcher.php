<?php

Route::get('/home', function () {
    $users[] = Auth::user();
    $users[] = Auth::guard()->user();
    $users[] = Auth::guard('dispatcher')->user();

    //dd($users);

    return view('dispatcher.home');
})->name('home');

