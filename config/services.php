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

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
        'scheme' => 'https',
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'qontak' => [
        'base_url' => env('QONTAK_BASE_URL', 'https://api.mekari.com/qontak/chat'),
        'api_token' => env('QONTAK_API_TOKEN'),
        'client_id' => env('QONTAK_CLIENT_ID'),
        'client_secret' => env('QONTAK_CLIENT_SECRET'),
        'channel_integration_id' => env('QONTAK_CHANNEL_INTEGRATION_ID'),
        'template_id' => env('QONTAK_TEMPLATE_ID'),
        'otp_template_id' => env('QONTAK_OTP_TEMPLATE_ID'),
        'enabled' => env('QONTAK_ENABLED', true),
        'oauth_url' => env('QONTAK_OAUTH_URL', 'https://api.mekari.com/oauth/token'),
    ],

    'cio_finance' => [
        'base_url'   => env('CIO_FINANCE_BASE_URL', 'http://localhost:8000'),
        'client_id'  => env('CIO_FINANCE_CLIENT_ID'),
        'key_id'     => env('CIO_FINANCE_KEY_ID'),
        'secret_key' => env('CIO_FINANCE_SECRET_KEY'),
        'timeout'    => env('CIO_FINANCE_TIMEOUT', 5),
    ],

    'xendit' => [
        'secret_key'    => env('XENDIT_SECRET_KEY'),
        'webhook_token' => env('XENDIT_WEBHOOK_TOKEN'),
    ],

];

