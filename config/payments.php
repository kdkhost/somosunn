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

    'pagseguro' => [
        /**
         * E-mail principal da conta PagSeguro.
         */
        'email' => env('PAGSEGURO_EMAIL'),

        /**
         * Token de integração gerado no painel da conta.
         */
        'token' => env('PAGSEGURO_TOKEN'),
    ],
];
