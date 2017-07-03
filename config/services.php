<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Stripe, Mailgun, SparkPost and others. This file provides a sane
    | default location for this type of information, allowing packages
    | to have a conventional place to find your various credentials.
    |
    */

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
    ],

    'ses' => [
        'key' => env('SES_KEY'),
        'secret' => env('SES_SECRET'),
        'region' => 'us-east-1',
    ],

    'sparkpost' => [
        'secret' => env('SPARKPOST_SECRET'),
    ],

    'stripe' => [
        'key' => env('STRIPE_KEY'),
        'secret' => env('STRIPE_SECRET'),
    ],

    'facebook' => [
        'client_id' => '227306957774573',
        'client_secret' => 'eae1e333047be8d92d8556b6af2ff917',
        'redirect' => 'http://schedule.tranxit.co/auth/facebook/callback',
    ],

    'google' => [
        'client_id' => '770330255573-bd4ncoksl3clqeiherhv636r6k69folh.apps.googleusercontent.com',
        'client_secret' => '9uh3U0-nLzD0E6UsizWE5beW',
        'redirect' => 'http://schedule.tranxit.co/auth/google/callback',
    ]

];
