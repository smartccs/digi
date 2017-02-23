<?php

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;

class SettingsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('settings')->delete();
        DB::table('settings')->insert([
            [
                'key' => 'site_title',
                'value' => 'Tranxit'
            ],
            [
                'key' => 'site_logo',
                'value' => asset('asset/img/logo.png'),
            ],
            [
                'key' => 'site_mail_icon',
                'value' => asset('asset/img/logo.png'),
            ],
            [
                'key' => 'site_icon',
                'value' => asset('logo.png'),
            ],
            [
                'key' => 'provider_select_timeout',
                'value' => 60
            ],
            [
                'key' => 'search_radius',
                'value' => 100
            ],
            [
                'key' => 'base_price',
                'value' => 50
            ],
            [
                'key' => 'price_per_minute',
                'value' => 50
            ],
            [
                'key' => 'tax_percentage',
                'value' => 0
            ],  
            [
                'key' => 'stripe_secret_key',
                'value' => ''
            ], 
             [
                'key' => 'stripe_publishable_key',
                'value' => ''
            ], 
            [
                'key' => 'CASH',
                'value' => 1
            ], 
            [
                'key' => 'PAYPAL',
                'value' => 1
            ], 
            [
                'key' => 'CARD',
                'value' => 1
            ],
            [
                'key' => 'manual_request',
                'value' => 0
            ],  
            [
                'key' => 'paypal_email',
                'value' => ''
            ], 
            [
                'key' => 'default_lang',
                'value' => 'en'
            ], 
            [
                'key' => 'currency',
                'value' => '$'
            ], 
            [
                'key' => 'scheduled_cancel_time_exceed',
                'value' => '10'
            ],
            [
                'key' => 'price_per_kilometer',
                'value' => 10
            ],
            [
                'key' => 'commission_percentage',
                'value' => 0
            ],
            [
                'key' => 'email_logo',
                'value' => ''
            ],
            [
                'key' => 'play_store_link',
                'value' => ''
            ],
            [
                'key' => 'app_store_link',
                'value' => ''
            ],
        ]);
    }
}
