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
    "firebase" => [
        "key_path" => base_path(env("FIREBASE_CREDENTIALS_PATH")),
        "project_id" => env("FCM_PROJECT_ID"),
    ],

    'nass' => [
        'base_url' => env('NASS_BASE_URL', 'https://uat-gateway.nass.iq:9746/'),
        'username' => env('NASS_USERNAME'),
        'password' => env('NASS_PASSWORD'),
        'back_ref' => env('NASS_BACK_REF'),
        'notify_url' => env('NASS_NOTIFY_URL'),
    ],

];
