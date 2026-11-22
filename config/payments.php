<?php

/**
 * ============================================================
 * PROPRIEDADE INTELECTUAL E DIREITOS AUTORAIS
 * ============================================================
 *
 * @autor marcelo-brad rj
 * @contato
 * Tel: 21 981325441
 * WhatsApp: 21 98132-5441
 * Email: contato@kdkhost.com.br
 * Telegram: @MARCELO_BRAD
 * Instagram: @marcelobradrj
 *
 * ============================================================
 */

return [
    'mercadopago' => [
        'enabled' => env('PAYMENT_MERCADOPAGO_ENABLED', true),
        'public_key' => env('MERCADOPAGO_PUBLIC_KEY'),
        'access_token' => env('MERCADOPAGO_ACCESS_TOKEN'),
        'client_id' => env('MERCADOPAGO_CLIENT_ID'),
        'client_secret' => env('MERCADOPAGO_CLIENT_SECRET'),
        'redirect_uri' => env('MERCADOPAGO_REDIRECT_URI', env('APP_URL') . '/gateway/mercadopago/callback'),
        'integrator_id' => env('MERCADOPAGO_INTEGRATOR_ID', ''),
        'platform_id' => env('MERCADOPAGO_PLATFORM_ID', ''),
        'webhook_secret' => env('MERCADOPAGO_WEBHOOK_SECRET', ''),
        'webhook_signature_required' => env('MERCADOPAGO_WEBHOOK_SIGNATURE_REQUIRED', env('APP_ENV') === 'production'),
        'allow_unsigned_webhooks' => env('MERCADOPAGO_WEBHOOK_ALLOW_UNSIGNED', env('APP_ENV') !== 'production'),
    ],

    'sumup' => [
        'enabled' => env('PAYMENT_SUMUP_ENABLED', false),
        'api_key' => env('SUMUP_API_KEY', ''),
        'client_id' => env('SUMUP_CLIENT_ID', ''),
        'client_secret' => env('SUMUP_CLIENT_SECRET', ''),
        'merchant_code' => env('SUMUP_MERCHANT_CODE', ''),
        'env' => env('SUMUP_ENV', 'sandbox'),
        'fee_percentage' => env('SUMUP_FEE_PERCENTAGE', 2.75),
        'fee_fixed' => env('SUMUP_FEE_FIXED', 0),
        'pass_fee' => env('SUMUP_PASS_FEE', false),
        'webhook_secret' => env('SUMUP_WEBHOOK_SECRET', ''),
    ],

    'efi' => [
        'enabled' => env('PAYMENT_EFI_ENABLED', false),
        'env' => env('EFI_ENV', 'sandbox'),
        'client_id' => env('EFI_CLIENT_ID', ''),
        'client_secret' => env('EFI_CLIENT_SECRET', ''),
        'pix_key' => env('EFI_PIX_KEY', ''),
        'certificate_path' => env('EFI_CERTIFICATE_PATH', ''),
        'webhook_secret' => env('EFI_WEBHOOK_SECRET', ''),
        'mtls_enabled' => env('EFI_MTLS_ENABLED', true),
    ],
];
