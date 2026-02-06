<?php

return [
    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    // Socialite
    'google' => [
        'client_id' => env('GOOGLE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
        'redirect' => env('APP_URL').'/auth/callback/google',
    ],

    'facebook' => [
        'client_id' => env('FACEBOOK_CLIENT_ID'),
        'client_secret' => env('FACEBOOK_CLIENT_SECRET'),
        'redirect' => env('APP_URL').'/auth/callback/facebook',
    ],

    'linkedin' => [
        'client_id' => env('LINKEDIN_CLIENT_ID'),
        'client_secret' => env('LINKEDIN_CLIENT_SECRET'),
        'redirect' => env('APP_URL').'/auth/callback/linkedin',
    ],

    // reCAPTCHA v3
    'recaptcha' => [
        'site_key' => env('RECAPTCHA_V3_SITE_KEY'),
        'v3_secret' => env('RECAPTCHA_V3_SECRET_KEY'),
        'v3_min_score' => (float) env('RECAPTCHA_V3_MIN_SCORE', 0.5),
    ],

    // Geolocalização (opcional) para Analytics
    'ipinfo' => [
        'token' => env('IPINFO_TOKEN'),
    ],
];
