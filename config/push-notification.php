<?php

return array(

    'appNameIOSUser'     => array(
        'environment' =>'development',
        'certificate' => app_path().'/apns/user/tranxit_user.pem',
        'passPhrase'  =>'appoets123$',
        'service'     =>'apns'
    ),
    'appNameIOSProvider' => array(
        'environment' =>'production',
        'certificate' => app_path().'/apns/provider/tranxit_provider.pem',
        'passPhrase'  =>'appoets123$',
        'service'     =>'apns'
    ),
    'appNameAndroidUser' => array(
        'environment' =>'development',
        'apiKey'      =>'yourAPIKey',
        'service'     =>'gcm'
    ),
    'appNameAndroidProvider' => array(
        'environment' =>'development',
        'apiKey'      =>'yourAPIKey',
        'service'     =>'gcm'
    )

);