<?php

namespace Database\Seeders;

use App\Models\Waf\WafRule;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Seed de regras iniciais do WAF cobrindo todos os Attack_Patterns.
 *
 * Spec: .kiro/specs/waf-e-auditoria-seguranca
 * Requisitos: 10.1, 10.2
 */
class WafRuleSeeder extends Seeder
{
    public function run(): void
    {
        $rules = $this->seedRules();

        foreach ($rules as $data) {
            WafRule::query()->updateOrCreate(
                ['name' => $data['name']],
                array_merge($data, ['uid' => strtoupper(Str::ulid()->toBase32())])
            );
        }
    }

    private function seedRules(): array
    {
        return [
            // === SQLi ===
            [
                'name'            => 'SQLi - UNION SELECT',
                'description'     => 'Detecta tentativas de UNION-based SQL injection',
                'attack_pattern'  => 'SQLi',
                'scope'           => ['fields' => ['query', 'body', 'path']],
                'matcher_type'    => 'regex',
                'matcher_payload' => ['pattern' => 'union\\s+(all\\s+)?select', 'flags' => 'i', 'target' => 'all'],
                'score'           => 40,
                'action'          => 'block',
                'severity'        => 'critical',
                'is_active'       => true,
            ],
            [
                'name'            => 'SQLi - Keywords perigosas',
                'description'     => 'Detecta SLEEP, BENCHMARK, INTO OUTFILE, LOAD_FILE',
                'attack_pattern'  => 'SQLi',
                'scope'           => ['fields' => ['query', 'body']],
                'matcher_type'    => 'function',
                'matcher_payload' => ['function' => 'contains_sql_keyword', 'args' => ['target' => 'all']],
                'score'           => 35,
                'action'          => 'block',
                'severity'        => 'high',
                'is_active'       => true,
            ],
            [
                'name'            => 'SQLi - Comentarios SQL',
                'description'     => 'Detecta -- e /* em contextos suspeitos',
                'attack_pattern'  => 'SQLi',
                'scope'           => ['fields' => ['query', 'body']],
                'matcher_type'    => 'regex',
                'matcher_payload' => ['pattern' => '(--|/\\*|\\*/|;\\s*drop|;\\s*delete|;\\s*insert|;\\s*update)', 'flags' => 'i', 'target' => 'all'],
                'score'           => 25,
                'action'          => 'monitor',
                'severity'        => 'medium',
                'is_active'       => true,
            ],

            // === XSS ===
            [
                'name'            => 'XSS - Script tags e event handlers',
                'description'     => 'Detecta <script>, onerror=, javascript:, etc.',
                'attack_pattern'  => 'XSS',
                'scope'           => ['fields' => ['query', 'body', 'headers']],
                'matcher_type'    => 'function',
                'matcher_payload' => ['function' => 'has_xss_signature', 'args' => ['target' => 'all']],
                'score'           => 35,
                'action'          => 'block',
                'severity'        => 'high',
                'is_active'       => true,
            ],

            // === Path Traversal ===
            [
                'name'            => 'Path Traversal - ../ e variantes',
                'description'     => 'Detecta ../, ..\\, %2e%2e, null bytes em paths',
                'attack_pattern'  => 'Path_Traversal',
                'scope'           => ['fields' => ['query', 'body', 'path']],
                'matcher_type'    => 'function',
                'matcher_payload' => ['function' => 'has_path_traversal', 'args' => ['target' => 'all']],
                'score'           => 40,
                'action'          => 'block',
                'severity'        => 'critical',
                'is_active'       => true,
            ],

            // === LFI ===
            [
                'name'            => 'LFI - Inclusao de arquivo local',
                'description'     => 'Detecta /etc/passwd, /proc/self, php://filter',
                'attack_pattern'  => 'LFI',
                'scope'           => ['fields' => ['query', 'body', 'path']],
                'matcher_type'    => 'regex',
                'matcher_payload' => ['pattern' => '(/etc/(passwd|shadow|hosts)|/proc/self|php://(filter|input|data))', 'flags' => 'i', 'target' => 'all'],
                'score'           => 40,
                'action'          => 'block',
                'severity'        => 'critical',
                'is_active'       => true,
            ],

            // === RFI ===
            [
                'name'            => 'RFI - Inclusao de arquivo remoto',
                'description'     => 'Detecta URLs externas em parametros de include',
                'attack_pattern'  => 'RFI',
                'scope'           => ['fields' => ['query', 'body']],
                'matcher_type'    => 'regex',
                'matcher_payload' => ['pattern' => '(https?://|ftp://|data:text/html).*\\.(php|phtml|phar|txt)', 'flags' => 'i', 'target' => 'all'],
                'score'           => 35,
                'action'          => 'block',
                'severity'        => 'high',
                'is_active'       => true,
            ],

            // === SSRF ===
            [
                'name'            => 'SSRF - IP privado em URL',
                'description'     => 'Detecta IPs privados/loopback em parametros de URL',
                'attack_pattern'  => 'SSRF',
                'scope'           => ['fields' => ['query', 'body']],
                'matcher_type'    => 'regex',
                'matcher_payload' => ['pattern' => '(127\\.0\\.0\\.1|10\\.\\d+\\.\\d+\\.\\d+|172\\.(1[6-9]|2\\d|3[01])\\.\\d+\\.\\d+|192\\.168\\.\\d+\\.\\d+|169\\.254\\.\\d+\\.\\d+|\\[::1\\]|0\\.0\\.0\\.0)', 'flags' => 'i', 'target' => 'all'],
                'score'           => 40,
                'action'          => 'block',
                'severity'        => 'critical',
                'is_active'       => true,
            ],

            // === XXE ===
            [
                'name'            => 'XXE - Entidades externas XML',
                'description'     => 'Detecta <!ENTITY e SYSTEM em payloads XML',
                'attack_pattern'  => 'XXE',
                'scope'           => ['fields' => ['body', 'headers']],
                'matcher_type'    => 'regex',
                'matcher_payload' => ['pattern' => '<!\\s*ENTITY|SYSTEM\\s+["\']', 'flags' => 'i', 'target' => 'body'],
                'score'           => 40,
                'action'          => 'block',
                'severity'        => 'critical',
                'is_active'       => true,
            ],

            // === RCE ===
            [
                'name'            => 'RCE - Comandos de sistema',
                'description'     => 'Detecta tentativas de execucao de comandos',
                'attack_pattern'  => 'RCE',
                'scope'           => ['fields' => ['query', 'body', 'headers']],
                'matcher_type'    => 'function',
                'matcher_payload' => ['function' => 'has_rce_signature', 'args' => ['target' => 'all']],
                'score'           => 45,
                'action'          => 'block',
                'severity'        => 'critical',
                'is_active'       => true,
            ],

            // === Malicious Upload ===
            [
                'name'            => 'Upload - Extensao perigosa',
                'description'     => 'Detecta extensoes executaveis em nomes de arquivo',
                'attack_pattern'  => 'Malicious_Upload',
                'scope'           => ['fields' => ['body', 'headers']],
                'matcher_type'    => 'regex',
                'matcher_payload' => ['pattern' => '\\.(php|phtml|phar|phps|php[3-8]|pl|py|jsp|asp|aspx|exe|sh|bat|cmd|htaccess)([\\s;,"\']|$)', 'flags' => 'i', 'target' => 'all'],
                'score'           => 40,
                'action'          => 'block',
                'severity'        => 'critical',
                'is_active'       => true,
            ],

            // === Brute Force ===
            [
                'name'            => 'Brute Force - Rate limit login',
                'description'     => 'Complementa rate limit do engine para login',
                'attack_pattern'  => 'Brute_Force',
                'scope'           => ['fields' => ['path'], 'scopes' => ['login']],
                'matcher_type'    => 'regex',
                'matcher_payload' => ['pattern' => '^/login$', 'flags' => 'i', 'target' => 'path'],
                'score'           => 5,
                'action'          => 'monitor',
                'severity'        => 'low',
                'is_active'       => true,
            ],

            // === Credential Stuffing ===
            [
                'name'            => 'Credential Stuffing - User-Agent suspeito',
                'description'     => 'Detecta user-agents de ferramentas de ataque',
                'attack_pattern'  => 'Credential_Stuffing',
                'scope'           => ['fields' => ['user_agent']],
                'matcher_type'    => 'function',
                'matcher_payload' => ['function' => 'is_blacklisted_user_agent', 'args' => ['target' => 'user_agent']],
                'score'           => 30,
                'action'          => 'challenge',
                'severity'        => 'medium',
                'is_active'       => true,
            ],

            // === User Enumeration ===
            [
                'name'            => 'User Enumeration - Tentativa em massa',
                'description'     => 'Monitora tentativas repetidas em /forgot-password',
                'attack_pattern'  => 'User_Enumeration',
                'scope'           => ['fields' => ['path'], 'scopes' => ['login']],
                'matcher_type'    => 'regex',
                'matcher_payload' => ['pattern' => '(forgot-password|reset-password|password/email)', 'flags' => 'i', 'target' => 'path'],
                'score'           => 5,
                'action'          => 'monitor',
                'severity'        => 'low',
                'is_active'       => true,
            ],

            // === Scraping ===
            [
                'name'            => 'Scraping - Acesso rapido sem referrer',
                'description'     => 'Monitora requisicoes sem referrer em rotas de conteudo',
                'attack_pattern'  => 'Scraping',
                'scope'           => ['fields' => ['headers']],
                'matcher_type'    => 'numeric',
                'matcher_payload' => ['target' => 'content-length', 'operator' => '==', 'value' => 0],
                'score'           => 5,
                'action'          => 'monitor',
                'severity'        => 'info',
                'is_active'       => false, // desativada por padrao (muitos falsos positivos)
            ],

            // === Bot ===
            [
                'name'            => 'Bot - User-Agent vazio',
                'description'     => 'Detecta requisicoes sem User-Agent',
                'attack_pattern'  => 'Bot',
                'scope'           => ['fields' => ['user_agent']],
                'matcher_type'    => 'regex',
                'matcher_payload' => ['pattern' => '^$', 'flags' => '', 'target' => 'user_agent'],
                'score'           => 15,
                'action'          => 'challenge',
                'severity'        => 'low',
                'is_active'       => true,
            ],

            // === CSRF Missing ===
            [
                'name'            => 'CSRF - Token ausente em POST',
                'description'     => 'Monitora POSTs sem _token (complementa VerifyCsrfToken)',
                'attack_pattern'  => 'CSRF_Missing',
                'scope'           => ['fields' => ['body'], 'scopes' => ['default', 'admin']],
                'matcher_type'    => 'regex',
                'matcher_payload' => ['pattern' => 'placeholder_never_matches', 'flags' => '', 'target' => 'body'],
                'score'           => 0,
                'action'          => 'monitor',
                'severity'        => 'info',
                'is_active'       => false, // Laravel ja trata CSRF nativamente
            ],

            // === Webhook Invalid Signature ===
            [
                'name'            => 'Webhook - Assinatura invalida',
                'description'     => 'Monitora webhooks que chegam sem header de assinatura',
                'attack_pattern'  => 'Webhook_Invalid_Signature',
                'scope'           => ['fields' => ['headers'], 'scopes' => ['webhook']],
                'matcher_type'    => 'regex',
                'matcher_payload' => ['pattern' => 'placeholder_webhook_check', 'flags' => '', 'target' => 'headers'],
                'score'           => 0,
                'action'          => 'monitor',
                'severity'        => 'info',
                'is_active'       => false, // Ativado quando HMAC for implementado nos controllers
            ],

            // === Null Byte ===
            [
                'name'            => 'Null Byte Injection',
                'description'     => 'Detecta bytes nulos em qualquer parametro',
                'attack_pattern'  => 'Path_Traversal',
                'scope'           => ['fields' => ['query', 'body', 'path']],
                'matcher_type'    => 'function',
                'matcher_payload' => ['function' => 'has_null_byte', 'args' => ['target' => 'all']],
                'score'           => 40,
                'action'          => 'block',
                'severity'        => 'critical',
                'is_active'       => true,
            ],
        ];
    }
}
