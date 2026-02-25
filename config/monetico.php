<?php

return [
    'base_url' => env('MONETICO_BASE_URL', 'https://api.gateway.monetico-retail.com/api-payment/V4'),
    'public_key' => env('MONETICO_PUBLIC_KEY'),
    'secret_key' => env('MONETICO_SECRET_KEY'),
    'login' => env('MONETICO_LOGIN'),
    'mode' => env('MONETICO_MODE', 'test'), // 'test' ou 'production'
];
