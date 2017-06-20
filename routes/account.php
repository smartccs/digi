<?php

Route::get('/home', function () {
    $users[] = Auth::user();
    $users[] = Auth::guard()->user();
    $users[] = Auth::guard('account')->user();

    //dd($users);

    return view('account.home');
})->name('home');

