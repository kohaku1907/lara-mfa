<?php

return [
    'default_code_timeout' => 600,
    'sms' => [
        'code_timeout' => 1800, // in seconds
        'notification' => Kohaku1907\LaraMfa\Notifications\MfCodeSms::class,
        'expire_time' => 1440, // in minutes
        'resend_limit' => 5, // 5 times
        'resend_interval' => 60, // in seconds
        'resend_limit_duration' => 1440, // in minutes
    ],
    'email' => [
        'code_timeout' => 1800, // in seconds
        'notification' => Kohaku1907\LaraMfa\Notifications\MfCodeEmail::class,
        'expire_time' => 1440, // in minutes
        'resend_limit' => 5, // 5 times
        'resend_interval' => 60, // in seconds
        'resend_limit_duration' => 1440, // in minutes
    ],
    'totp' => [
        'expire_time' => 1440, // in minutes
        'secret_length' => 20,
        'offset_periods' => 1,
        'label' => 'LaraMFA',
        'issuer' => 'LaraMFA',
        'qr_code' => [
            'size' => 300,
            'margin' => 2,
        ],
    ],
];
