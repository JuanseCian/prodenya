<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Resend, Postmark, AWS, and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
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

    'mapbox' => [
        'token' => env('MAPBOX_ACCESS_TOKEN'),
    ],

    'hostinger_mailbox' => [
        'enabled' => env('HOSTINGER_MAILBOX_ENABLED', false),
        'host' => env('HOSTINGER_IMAP_HOST', 'imap.hostinger.com'),
        'port' => (int) env('HOSTINGER_IMAP_PORT', 993),
        'encryption' => env('HOSTINGER_IMAP_ENCRYPTION', 'ssl'),
        'validate_cert' => env('HOSTINGER_IMAP_VALIDATE_CERT', true),
        'username' => env('HOSTINGER_IMAP_USERNAME', 'contacto@desarrollohumanosn.com'),
        'password' => env('HOSTINGER_IMAP_PASSWORD'),
        'folder' => env('HOSTINGER_IMAP_FOLDER', 'INBOX'),
        'sync_days' => max(1, (int) env('HOSTINGER_IMAP_SYNC_DAYS', 30)),
        'to_email' => env('HOSTINGER_MAILBOX_ADDRESS', 'contacto@desarrollohumanosn.com'),
    ],

];
