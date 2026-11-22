<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Configuracao de Subdominios e CDN (fallbacks estaticos)
    |--------------------------------------------------------------------------
    | Os valores definidos aqui funcionam como fallback caso as chaves
    | equivalentes na tabela `settings` nao estejam preenchidas.
    | O roteamento estrito por subdominio permanece desativado por padrao
    | para garantir retrocompatibilidade em ambientes locais e de migracao.
    */
    'routing_enabled' => filter_var(env('DOMAIN_ROUTING_ENABLED', false), FILTER_VALIDATE_BOOL),

    'base_domain' => env('DOMAIN_BASE', 'somosunn.com.br'),

    'main' => env('DOMAIN_MAIN', 'somosunn.com.br'),
    'app' => env('DOMAIN_APP', 'app.somosunn.com.br'),
    'api' => env('DOMAIN_API', 'api.somosunn.com.br'),
    'cdn' => env('DOMAIN_CDN', 'cdn.somosunn.com.br'),

    'cdn_enabled' => filter_var(env('CDN_ENABLED', false), FILTER_VALIDATE_BOOL),
    'cdn_url' => env('CDN_URL', ''),

    'public_page_cache_enabled' => filter_var(env('PUBLIC_PAGE_CACHE_ENABLED', true), FILTER_VALIDATE_BOOL),
    'public_cache_max_age' => (int) env('PUBLIC_CACHE_MAX_AGE', 300),
    'public_cache_s_maxage' => (int) env('PUBLIC_CACHE_S_MAXAGE', 600),
];
