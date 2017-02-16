<?php

return array(

    'IOSUser'     => array(
        'environment' =>'development',
        'certificate' => app_path().'/apns/user/tranxit_user.pem',
        'passPhrase'  =>'appoets123$',
        'service'     =>'apns'
    ),
    'IOSProvider' => array(
        'environment' =>'production',
        'certificate' => app_path().'/apns/provider/tranxit_provider.pem',
        'passPhrase'  =>'appoets123$',
        'service'     =>'apns'
    ),
    'AndroidUser' => array(
        'environment' =>'development',
        'apiKey'      =>'yourAPIKey',
        'service'     =>'gcm'
    ),
    'AndroidProvider' => array(
        'environment' =>'development',
        'apiKey'      =>'yourAPIKey',
        'service'     =>'gcm'
    )

);