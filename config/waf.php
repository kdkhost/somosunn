<?php

/*
|--------------------------------------------------------------------------
| Configuração do WAF (Web Application Firewall) da Unn
|--------------------------------------------------------------------------
|
| Este arquivo concentra os parâmetros operacionais do WAF próprio da
| Unn. A maioria dos valores pode ser sobrescrita em tempo real pelo
| painel do superadmin (tabela `waf_settings`), mas os defaults aqui
| servem como base inicial e como fallback quando o banco está
| indisponível.
|
| Documentação completa: .kiro/specs/waf-e-auditoria-seguranca/design.md
|
| Requisitos cobertos: 9.1, 22.1, 22.4, 22.5
|
*/

return [

    /*
    |--------------------------------------------------------------------------
    | Liga ou desliga o WAF globalmente
    |--------------------------------------------------------------------------
    |
    | Quando `false`, o WafMiddleware curto-circuita e a requisição segue
    | para o pipeline Laravel sem nenhuma inspeção. Útil para rollout e
    | para rollback emergencial (Requisito 22.4).
    |
    */
    'enabled' => env('WAF_ENABLED', false),

    /*
    |--------------------------------------------------------------------------
    | Modo de operação
    |--------------------------------------------------------------------------
    |
    | - `detection-only`: inspeciona, pontua e registra WAF_Events, mas
    |   NÃO bloqueia requisições por Risk_Score. Apenas IP_Blocklist
    |   explícita bloqueia.
    | - `enforce`: aplica bloqueios, desafios e rate limit integralmente.
    |
    | Requisitos: 9.10, 22.1
    |
    */
    'mode' => env('WAF_MODE', 'detection-only'),

    /*
    |--------------------------------------------------------------------------
    | Limiares de Risk_Score
    |--------------------------------------------------------------------------
    |
    | Faixas inteiras de 0..100 que definem a decisão do WAF a partir do
    | score somado das WAF_Rules disparadas:
    |
    |   score < monitor              -> allowed (sem log por padrão)
    |   monitor   <= score < challenge -> monitored
    |   challenge <= score < block     -> challenged
    |   score >= block                 -> blocked
    |
    | Requisitos: 9.5, 9.6, 9.7, 2.3, 3.5
    |
    */
    'thresholds' => [
        'monitor'   => (int) env('WAF_THRESHOLD_MONITOR', 20),
        'challenge' => (int) env('WAF_THRESHOLD_CHALLENGE', 50),
        'block'     => (int) env('WAF_THRESHOLD_BLOCK', 80),
    ],

    /*
    |--------------------------------------------------------------------------
    | Política em caso de exceção interna do engine
    |--------------------------------------------------------------------------
    |
    | - `open`: se o engine falhar, a requisição prossegue (`allowed`).
    | - `closed`: se o engine falhar, a requisição é bloqueada (HTTP 503).
    |
    | Ambas registram log estruturado no canal `waf` em nível `error`.
    |
    | Requisitos: 9.12, 22.3
    |
    */
    'fail_policy' => env('WAF_FAIL_POLICY', 'open'),

    /*
    |--------------------------------------------------------------------------
    | Retenção de WAF_Events (em dias)
    |--------------------------------------------------------------------------
    |
    | Cada decisão tem seu próprio prazo de retenção. Eventos mais antigos
    | que o prazo são removidos pelo job agendado diariamente.
    |
    | Requisitos: 12.4, 12.5
    |
    */
    'retention' => [
        'allowed'    => (int) env('WAF_RETENTION_ALLOWED', 7),
        'monitored'  => (int) env('WAF_RETENTION_MONITORED', 30),
        'challenged' => (int) env('WAF_RETENTION_CHALLENGED', 90),
        'blocked'    => (int) env('WAF_RETENTION_BLOCKED', 180),
    ],

    /*
    |--------------------------------------------------------------------------
    | Rotas isentas
    |--------------------------------------------------------------------------
    |
    | Lista de padrões (regex PCRE) de rotas que NÃO são inspecionadas
    | pelo WAF. Usar com parcimônia. Exemplos comuns: healthcheck,
    | assets estáticos servidos via PHP.
    |
    | Requisitos: 22.5
    |
    */
    'exempt_routes' => [
        '#^/healthz$#',
        '#^/health$#',
        '#^/_debugbar(/|$)#',
        '#^/livewire(/|$)#',
    ],

    /*
    |--------------------------------------------------------------------------
    | Rate limit por escopo
    |--------------------------------------------------------------------------
    |
    | Cada escopo (login, api, webhook, default) define um limite de
    | requisições por janela em segundos. A chave de contagem é
    | `waf:rl:{scope}:{identity}` onde `identity` depende do escopo
    | (IP, usuário, IP+e-mail).
    |
    | Requisitos: 7.1, 7.2, 11.1, 11.2
    |
    */
    'rate_limits' => [
        'default' => [
            'limit'  => (int) env('WAF_RL_DEFAULT_LIMIT', 300),
            'window' => (int) env('WAF_RL_DEFAULT_WINDOW', 60),
        ],
        'login' => [
            'limit'  => (int) env('WAF_RL_LOGIN_LIMIT', 10),
            'window' => (int) env('WAF_RL_LOGIN_WINDOW', 60),
        ],
        'api' => [
            'limit'  => (int) env('WAF_RL_API_LIMIT', 120),
            'window' => (int) env('WAF_RL_API_WINDOW', 60),
        ],
        'webhook' => [
            'limit'  => (int) env('WAF_RL_WEBHOOK_LIMIT', 600),
            'window' => (int) env('WAF_RL_WEBHOOK_WINDOW', 60),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Auto-bloqueio de IPs por acúmulo de eventos
    |--------------------------------------------------------------------------
    |
    | Quando um mesmo IP acumula `threshold` WAF_Events com decisão
    | `blocked` ou `challenged` dentro de `window_minutes`, o IpListService
    | adiciona automaticamente o IP à IP_Blocklist com `expires_at =
    | now + duration_hours`.
    |
    | Requisitos: 11.6, 16.3
    |
    */
    'auto_block' => [
        'enabled'         => env('WAF_AUTO_BLOCK_ENABLED', false),
        'window_minutes'  => (int) env('WAF_AUTO_BLOCK_WINDOW_MINUTES', 15),
        'threshold'       => (int) env('WAF_AUTO_BLOCK_THRESHOLD', 100),
        'duration_hours'  => (int) env('WAF_AUTO_BLOCK_DURATION_HOURS', 24),
    ],

    /*
    |--------------------------------------------------------------------------
    | Amostragem de WAF_Events "allowed"
    |--------------------------------------------------------------------------
    |
    | Fração (0..1) de requisições classificadas como `allowed` que ainda
    | assim geram WAF_Event, para diagnóstico. Padrão 0 (não amostra).
    |
    | Requisitos: 9.7
    |
    */
    'allowed_sampling_ratio' => (float) env('WAF_ALLOWED_SAMPLING_RATIO', 0.0),

    /*
    |--------------------------------------------------------------------------
    | Amostras de payload
    |--------------------------------------------------------------------------
    |
    | Limite em bytes de cada amostra registrada em `waf_events.samples`.
    | Amostras são mascaradas pelo SensitiveDataMasker antes de salvar.
    |
    */
    'sample_max_bytes' => (int) env('WAF_SAMPLE_MAX_BYTES', 2048),

    /*
    |--------------------------------------------------------------------------
    | Timeout por regra regex (ms)
    |--------------------------------------------------------------------------
    |
    | Regras regex que excedem esse tempo são colocadas em quarentena
    | automaticamente e geram alerta ao superadmin.
    |
    */
    'regex_timeout_ms' => (int) env('WAF_REGEX_TIMEOUT_MS', 20),

    /*
    |--------------------------------------------------------------------------
    | Mascaramento de dados sensíveis
    |--------------------------------------------------------------------------
    |
    | Ativação de máscaras opcionais.
    |
    | Requisitos: 12.2
    |
    */
    'masking' => [
        'mask_emails' => env('WAF_MASK_EMAILS', false),
        'mask_pans'   => env('WAF_MASK_PANS', true),
        'mask_cpf'    => env('WAF_MASK_CPF', true),
        'mask_cnpj'   => env('WAF_MASK_CNPJ', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Proxies / cabeçalhos confiáveis para IP real
    |--------------------------------------------------------------------------
    |
    | O Laravel já trata `X-Forwarded-For` via TrustProxies. Esta seção
    | só documenta a dependência — o WAF consome `$request->ip()` após
    | TrustProxies ter agido.
    |
    */
    'trust_proxy_for_ip' => true,

];
