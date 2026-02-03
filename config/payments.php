<?php

return [
    'mercadopago' => [
        'public_key' => env('MERCADOPAGO_PUBLIC_KEY'),
        'access_token' => env('MERCADOPAGO_ACCESS_TOKEN'),
    ],

    'pagseguro' => [
        'email' => env('PAGSEGURO_EMAIL'),
        'token' => env('PAGSEGURO_TOKEN'),
    ],

    // Add other gateway configs here (Stripe, PayPal)
];
