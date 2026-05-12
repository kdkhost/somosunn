<?php

namespace App\Services\Waf\Matchers;

use App\Models\Waf\WafRule;
use App\Services\Waf\Matchers\Contracts\RuleMatcher;
use App\Services\Waf\WafContext;
use App\Services\Waf\WafRuleMatch;

/**
 * NumericRuleMatcher - compara um campo numerico.
 *
 * Payload esperado:
 *   {
 *     "target":   "content-length|query.X|header.X",
 *     "operator": ">=|<=|>|<|==|!=",
 *     "value":    123
 *   }
 *
 * Spec: .kiro/specs/waf-e-auditoria-seguranca
 * Requisito: 10.3
 */
final class NumericRuleMatcher implements RuleMatcher
{
    public function type(): string
    {
        return WafRule::MATCHER_NUMERIC;
    }

    public function evaluate(WafRule $rule, WafContext $ctx): ?WafRuleMatch
    {
        $payload  = (array) $rule->matcher_payload;
        $target   = (string) ($payload['target']   ?? '');
        $operator = (string) ($payload['operator'] ?? '');
        $value    = $payload['value'] ?? null;

        if ($target === '' || $operator === '' || ! is_numeric($value)) {
            return null;
        }

        $actual = $this->extractNumeric($target, $ctx);
        if ($actual === null) {
            return null;
        }

        $match = match ($operator) {
            '>'  => $actual >  $value,
            '>=' => $actual >= $value,
            '<'  => $actual <  $value,
            '<=' => $actual <= $value,
            '==' => $actual == $value,
            '!=' => $actual != $value,
            default => false,
        };

        if (! $match) {
            return null;
        }

        return new WafRuleMatch(
            rule:        $rule,
            score:       (int) $rule->score,
            field:       $target,
            sample:      (string) $actual,
            matcherType: $this->type(),
        );
    }

    private function extractNumeric(string $target, WafContext $ctx): ?float
    {
        // Suporta alguns "atalhos" comuns
        if (strtolower($target) === 'content-length') {
            return isset($ctx->headers['content-length'])
                ? (float) $ctx->headers['content-length']
                : (float) strlen(is_array($ctx->body) ? (string) json_encode($ctx->body) : (string) $ctx->body);
        }

        if (str_starts_with($target, 'header.')) {
            $h = strtolower(substr($target, 7));
            return isset($ctx->headers[$h]) && is_numeric($ctx->headers[$h])
                ? (float) $ctx->headers[$h]
                : null;
        }

        if (str_starts_with($target, 'query.')) {
            $q = substr($target, 6);
            return isset($ctx->query[$q]) && is_numeric($ctx->query[$q])
                ? (float) $ctx->query[$q]
                : null;
        }

        return null;
    }
}
