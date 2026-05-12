<?php

namespace App\Services\Waf\Matchers;

use App\Models\Waf\WafRule;
use App\Services\Waf\Matchers\Contracts\RuleMatcher;
use App\Services\Waf\WafContext;
use App\Services\Waf\WafRuleMatch;

/**
 * FunctionRuleMatcher - chama funcoes pre-definidas para decidir.
 *
 * Payload esperado:
 *   {
 *     "function": "contains_sql_keyword | is_private_ip | has_null_byte |
 *                  is_blacklisted_user_agent | has_path_traversal | has_xss_signature |
 *                  has_rce_signature",
 *     "args":     { "target": "query|body|headers|path|user_agent|all", "extra": "..." }
 *   }
 *
 * Invariantes validadas por property tests:
 *   - Property 13: has_path_traversal detecta ../ ..\\ %00 etc.
 *   - Property 14: is_private_ip detecta 10/8 172.16/12 192.168/16 127/8 169.254/16 ::1 fc00::/7
 *
 * Spec: .kiro/specs/waf-e-auditoria-seguranca
 * Requisitos: 2.1, 3.1, 5.1, 5.4, 10.3
 */
final class FunctionRuleMatcher implements RuleMatcher
{
    private const SQL_KEYWORDS = [
        'union select', 'select from', 'insert into', 'update set',
        'delete from', 'drop table', 'alter table', 'truncate table',
        'information_schema', '@@version', 'sleep(', 'benchmark(',
        'into outfile', 'load_file(', 'xp_cmdshell', '0x7e7e7e',
    ];

    private const XSS_SIGNATURES = [
        '<script', '</script', 'javascript:', 'data:text/html',
        'onerror=', 'onload=', 'onclick=', 'onmouseover=', 'onfocus=',
        '<iframe', '<svg', '<img src=x',
    ];

    private const RCE_SIGNATURES = [
        ';ls ', ';cat ', '| curl ', '| wget ', '&&curl', '&&wget',
        '`id`', '`whoami`', '$(id)', '$(whoami)', '/bin/sh', '/bin/bash',
        'phpinfo(', 'base64_decode(', 'eval(', 'shell_exec(',
    ];

    private const BAD_USER_AGENTS = [
        'sqlmap', 'nikto', 'nessus', 'arachni', 'dirbuster',
        'acunetix', 'nmap', 'masscan', 'wpscan', 'zgrab',
    ];

    public function type(): string
    {
        return WafRule::MATCHER_FUNCTION;
    }

    public function evaluate(WafRule $rule, WafContext $ctx): ?WafRuleMatch
    {
        $payload  = (array) $rule->matcher_payload;
        $function = (string) ($payload['function'] ?? '');
        $args     = (array)  ($payload['args']     ?? []);
        $target   = (string) ($args['target']      ?? 'all');

        $result = match ($function) {
            'contains_sql_keyword'      => self::containsSqlKeyword($ctx->targetString($target)),
            'is_private_ip'              => self::isPrivateIp($args['ip'] ?? $ctx->ip),
            'has_null_byte'              => self::hasNullByte($ctx->targetString($target)),
            'is_blacklisted_user_agent'  => self::isBlacklistedUserAgent((string) $ctx->userAgent),
            'has_path_traversal'         => self::hasPathTraversal($ctx->targetString($target)),
            'has_xss_signature'          => self::hasXssSignature($ctx->targetString($target)),
            'has_rce_signature'          => self::hasRceSignature($ctx->targetString($target)),
            default                      => null,
        };

        if ($result === null) {
            return null;
        }

        return new WafRuleMatch(
            rule:        $rule,
            score:       (int) $rule->score,
            field:       $target,
            sample:      substr($result, 0, 200),
            matcherType: $this->type(),
        );
    }

    /* ============================================================
     *  Funcoes publicas estaticas (reutilizadas por outros services)
     * ============================================================ */

    public static function containsSqlKeyword(string $haystack): ?string
    {
        if ($haystack === '') return null;
        $l = strtolower($haystack);

        foreach (self::SQL_KEYWORDS as $kw) {
            if (str_contains($l, $kw)) {
                return $kw;
            }
        }

        return null;
    }

    /**
     * Verifica se o IP pertence a faixas privadas/loopback (IPv4 e IPv6).
     * Retorna o proprio IP em string em caso positivo, null caso contrario.
     */
    public static function isPrivateIp(?string $ip): ?string
    {
        if (empty($ip)) return null;

        // Filtro do PHP trata automaticamente todas as faixas privadas e loopback
        if (filter_var(
                $ip,
                FILTER_VALIDATE_IP,
                FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE
            ) === false
            && filter_var($ip, FILTER_VALIDATE_IP) !== false
        ) {
            return $ip;
        }

        // Faixas adicionais nao cobertas: link-local IPv6 fe80::/10
        if (str_starts_with(strtolower($ip), 'fe80:')) {
            return $ip;
        }

        return null;
    }

    public static function hasNullByte(string $haystack): ?string
    {
        if ($haystack === '') return null;

        return (str_contains($haystack, "\x00") || str_contains($haystack, '%00'))
            ? '\x00'
            : null;
    }

    public static function isBlacklistedUserAgent(string $userAgent): ?string
    {
        if ($userAgent === '') return null;
        $l = strtolower($userAgent);

        foreach (self::BAD_USER_AGENTS as $ua) {
            if (str_contains($l, $ua)) {
                return $ua;
            }
        }

        return null;
    }

    public static function hasPathTraversal(string $haystack): ?string
    {
        if ($haystack === '') return null;
        $l = strtolower($haystack);

        $patterns = [
            '../', '..\\', '..%2f', '..%5c', '%2e%2e/', '%2e%2e\\',
            '/etc/passwd', '/etc/shadow', '/proc/self',
            'c:\\windows', 'c:/windows', '\\\\?\\',
        ];

        foreach ($patterns as $p) {
            if (str_contains($l, $p)) {
                return $p;
            }
        }

        // Byte nulo tambem e forma de path traversal
        if (str_contains($haystack, "\x00") || str_contains($l, '%00')) {
            return '\x00';
        }

        return null;
    }

    public static function hasXssSignature(string $haystack): ?string
    {
        if ($haystack === '') return null;
        $l = strtolower($haystack);

        foreach (self::XSS_SIGNATURES as $sig) {
            if (str_contains($l, $sig)) {
                return $sig;
            }
        }

        return null;
    }

    public static function hasRceSignature(string $haystack): ?string
    {
        if ($haystack === '') return null;
        $l = strtolower($haystack);

        foreach (self::RCE_SIGNATURES as $sig) {
            if (str_contains($l, $sig)) {
                return $sig;
            }
        }

        return null;
    }
}
