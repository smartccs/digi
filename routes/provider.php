<?php

Route::get('/home', function () {
    $users[] = Auth::user();
    $users[] = Auth::guard()->user();
    $users[] = Auth::guard('provider')->user();

    //dd($users);

    return view('provider.home');
})->name('home');

