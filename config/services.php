<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'google' => [
        'places_api_key' => env('GOOGLE_PLACES_API_KEY'),
    ],



    'bdm' => [
        'base_url' => env('BDM_API_BASE_URL'),
        'username' => env('BDM_API_USERNAME'),
        'email'    => env('BDM_API_EMAIL'),
        'password' => env('BDM_API_PASSWORD'),
    ],

    'onemin_ai' => [
        'api_key' => env('ONEMIN_AI_API_KEY'),
        'base_url' => env('ONEMIN_AI_BASE_URL', 'https://api.1min.ai'),
    ],

    'whatsapp' => [
        'support_number' => env('WHATSAPP_SUPPORT_NUMBER', '+33612345678'),
        'support_message' => env('WHATSAPP_SUPPORT_MESSAGE', 'Bonjour, j\'ai besoin d\'aide'),
    ],

];
