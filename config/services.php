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
    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],
    'resend' => [
        'key' => env('RESEND_KEY'),
    ],
    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],
    'stripe' => [
        'key' => env('STRIPE_KEY'),
        'secret' => env('STRIPE_SECRET'),
        'webhook_secret' => env('STRIPE_WEBHOOK_SECRET'),
    ],
    'staples' => [
        'production' => env('STAPLE_PRODUCTION'),
    ],
    'fedex' => [
        'account_number' => env('FEDEX_ACCOUNT_NUMBER'),
        'client_id' => env('FEDEX_CLIENT_ID'),
        'client_secret' => env('FEDEX_CLIENT_SECRET')
    ],
    'hs_sftp' => [
        'host' => env('HS_SFTP_HOST'),
        'port' => env('HS_SFTP_PORT'),
        'username' => env('HS_SFTP_USERNAME'),
        'password' => env('HS_SFTP_PASSWORD'),
        'address' => env('HS_SFTP_ADDRESS'),
    ],
    'cah_sftp' => [
        'host' => env('CAH_SFTP_HOST'),
        'port' => env('CAH_SFTP_PORT'),
        'username' => env('CAH_SFTP_USERNAME'),
        'password' => env('CAH_SFTP_PASSWORD'),
        'address' => env('CAH_SFTP_ADDRESS'),
    ],
    'mck_sftp' => [
        'host' => env('MCK_SFTP_HOST'),
        'port' => env('MCK_SFTP_PORT'),
        'username' => env('MCK_SFTP_USERNAME'),
        'password' => env('MCK_SFTP_PASSWORD'),
        'address' => env('MCK_SFTP_ADDRESS'),
    ],
    'med_sftp' => [
        'host' => env('MED_SFTP_HOST'),
        'port' => env('MED_SFTP_PORT'),
        'username' => env('MED_SFTP_USERNAME'),
        'password' => env('MED_SFTP_PASSWORD'),
        'address' => env('MED_SFTP_ADDRESS'),
    ],

    'ups' => [
        'access_key' => env('UPS_ACCESS_KEY'),
        'username' => env('UPS_USERNAME'),
        'password' => env('UPS_PASSWORD'),
        'account_number' => env('UPS_ACCOUNT_NUMBER'),
        'sandbox' => env('UPS_SANDBOX', true),
    ],
];
