<?php

return [
    'stateful' => explode(',', env('SANCTUM_STATEFUL_DOMAINS', '')),

    'guard' => ['web'],

    'expiration' => env('SANCTUM_TOKEN_EXPIRATION', null),

    'token_prefix' => env('SANCTUM_TOKEN_PREFIX', ''),

    'middleware' => [
        'verify_csrf_token' => \Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class,
        'encrypt_cookies' => \Illuminate\Cookie\Middleware\EncryptCookies::class,
    ],
];
