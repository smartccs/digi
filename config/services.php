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
        'redirect' => 'http://104.131.18.141/auth/facebook/callback',
    ],

    'google' => [
        'client_id' => '1041994393323-i53c5qdtm13dvr8ao4m1agu1ek1gklg3.apps.googleusercontent.com',
        'client_secret' => 'AZn3a5njioUr8ZeuMBRfAyRA',
        'redirect' => 'http://104.131.18.141/auth/google/callback',
    ]

];
