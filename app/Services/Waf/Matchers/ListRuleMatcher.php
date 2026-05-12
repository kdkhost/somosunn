<?php

namespace App\Services\Waf\Matchers;

use App\Models\Waf\WafRule;
use App\Services\Waf\Matchers\Contracts\RuleMatcher;
use App\Services\Waf\WafContext;
use App\Services\Waf\WafRuleMatch;

/**
 * ListRuleMatcher - regra baseada em pertinencia a uma lista.
 *
 * Payload esperado:
 *   {
 *     "values": ["str1", "str2", "..."],   // obrigatorio
 *     "case_insensitive": true,            // opcional (default: true)
 *     "target": "user_agent|path|..."      // opcional, default "all"
 *   }
 *
 * Spec: .kiro/specs/waf-e-auditoria-seguranca
 * Requisito: 10.3
 */
final class ListRuleMatcher implements RuleMatcher
{
    public function type(): string
    {
        return WafRule::MATCHER_LIST;
    }

    public function evaluate(WafRule $rule, WafContext $ctx): ?WafRuleMatch
    {
        $payload = (array) $rule->matcher_payload;

        $values = $payload['values'] ?? null;
        if (! is_array($values) || empty($values)) {
            return null;
        }

        $ci       = (bool) ($payload['case_insensitive'] ?? true);
        $target   = (string) ($payload['target'] ?? 'all');
        $haystack = $ctx->targetString($target);

        if ($haystack === '') {
            return null;
        }

        $needle = $ci ? strtolower($haystack) : $haystack;

        foreach ($values as $v) {
            $v = (string) $v;
            $cmp = $ci ? strtolower($v) : $v;

            if ($cmp !== '' && str_contains($needle, $cmp)) {
                $sample = substr($v, 0, 200);

                return new WafRuleMatch(
                    rule:        $rule,
                    score:       (int) $rule->score,
                    field:       $target,
                    sample:      $sample,
                    matcherType: $this->type(),
                );
            }
        }

        return null;
    }
}
