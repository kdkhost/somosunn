<?php

return [
    'mercadopago' => [
        /**
         * Chave pública de produção ou sandbox, obtida no painel de credenciais.
         */
        'public_key' => env('MERCADOPAGO_PUBLIC_KEY'),

        /**
         * Token de acesso privado para chamadas de API.
         */
        'access_token' => env('MERCADOPAGO_ACCESS_TOKEN'),

        /**
         * ID do Aplicativo (App ID) necessário para o fluxo OAuth.
         */
        'client_id' => env('MERCADOPAGO_CLIENT_ID'),

        /**
         * Segredo do Aplicativo usado para autenticar o OAuth.
         */
        'client_secret' => env('MERCADOPAGO_CLIENT_SECRET'),

        /**
         * URL para onde o vendedor é redirecionado após autorizar o aplicativo.
         */
        'redirect_uri' => env('MERCADOPAGO_REDIRECT_URI', env('APP_URL') . '/gateway/mercadopago/callback'),

        /**
         * Identificador do integrador para rastreamento de qualidade no painel do MP.
         * Configurável pelo admin em: Configurações > Pagamentos > MercadoPago.
         * Sobrescrito pelo AppServiceProvider com o valor do banco de dados.
         */
        'integrator_id' => env('MERCADOPAGO_INTEGRATOR_ID', ''),

        /**
         * Identificador da plataforma para rastreamento no painel do MP.
         * Configurável pelo admin em: Configurações > Pagamentos > MercadoPago.
         */
        'platform_id' => env('MERCADOPAGO_PLATFORM_ID', ''),
    ],

    'sumup' => [
        /**
         * Chave de API SumUp para autenticação nas chamadas de API.
         */
        'api_key' => env('SUMUP_API_KEY', ''),

        /**
         * Client ID do aplicativo SumUp (OAuth).
         */
        'client_id' => env('SUMUP_CLIENT_ID', ''),

        /**
         * Client Secret do aplicativo SumUp (OAuth).
         */
        'client_secret' => env('SUMUP_CLIENT_SECRET', ''),

        /**
         * Merchant Code da conta SumUp.
         */
        'merchant_code' => env('SUMUP_MERCHANT_CODE', ''),

        /**
         * Ambiente: sandbox ou production.
         */
        'env' => env('SUMUP_ENV', 'sandbox'),

        /**
         * Percentual de taxa cobrado pelo SumUp (ex: 2.75 para 2,75%).
         */
        'fee_percentage' => env('SUMUP_FEE_PERCENTAGE', 2.75),

        /**
         * Taxa fixa cobrada pelo SumUp por transação (em reais).
         */
        'fee_fixed' => env('SUMUP_FEE_FIXED', 0),

        /**
         * Se true, a taxa é repassada ao comprador; se false, absorvida pela plataforma.
         */
        'pass_fee' => env('SUMUP_PASS_FEE', false),

        /**
         * Segredo para validação de assinatura HMAC dos webhooks.
         */
        'webhook_secret' => env('SUMUP_WEBHOOK_SECRET', ''),
    ],
];
