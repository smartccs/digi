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
        'client_id' => '1742363032455520',
        'client_secret' => '02a21bc6e5992575ab3eafb9a451912b',
        'redirect' => 'https://demo.tranxit.co/auth/facebook/callback',
    ],

    'google' => [
        'client_id' => '481922396339-ohem13usmmu66gmotlmrn5kq8njb0umd.apps.googleusercontent.com',
        'client_secret' => '5go6DYXJ8lXRW-LAeEYay5MO',
        'redirect' => 'https://demo.tranxit.co/auth/facebook/callback',
    ]

];
