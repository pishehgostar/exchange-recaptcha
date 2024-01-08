<?php

return [
    'default' => 'alpha_numeric',

    'google_enterprise' => [
        'project_name' => env('EXCHANGE_RECAPTCHA_PROJECT_NAME',''),
        'site_key' => env('EXCHANGE_RECAPTCHA_SITE_KEY', ''),
        'secret_key' => env('EXCHANGE_RECAPTCHA_SECRET_KEY', ''),
        'credentials_path'=>storage_path('google_application_credentials.json'),
        'input_name'=>'g-recaptcha-response',
    ],

    'hcaptcha'=>[
        'site_key' => env('EXCHANGE_HCAPTCHA_SITE_KEY', ''),
        'secret_key' => env('EXCHANGE_HCAPTCHA_SECRET_KEY', ''),
        'input_name'=>'h-captcha-response',
    ],

    'alpha_numeric'=>[
        'input_name'=>'alpha-numeric-response',
        'secret_key'=>env('EXCHANGE_ALPHANUMERIC_SECRET_KEY', '')
    ]
];
