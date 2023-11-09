<?php

return [
    'default_code_timeout' => 600,
    'sms' => [
        'code_timeout' => 300, // in seconds
        'notification' => Kohaku1907\LaraMfa\Notifications\MfCodeSms::class,
        'expire_time' => 1440, // in minutes
    ],
    'email' => [
        'code_timeout' => 300, // in seconds
        'notification' => Kohaku1907\LaraMfa\Notifications\MfCodeEmail::class,
        'expire_time' => 1440, // in minutes
    ],
    'totp' => [
        'expire_time' => 1440, // in minutes
        'secret_length' => 20,
        'offset' => 1, // in seconds
        'label' => 'LaraMFA',
        'issuer' => 'LaraMFA',
        'qr_code' => [
            'size' => 400,
            'margin' => 2,
        ],
    ],
];
