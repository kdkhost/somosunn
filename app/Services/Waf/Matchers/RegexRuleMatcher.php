<?php

namespace App\Services\Waf\Matchers;

use App\Models\Waf\WafRule;
use App\Services\Waf\Matchers\Contracts\RuleMatcher;
use App\Services\Waf\WafContext;
use App\Services\Waf\WafRuleMatch;

/**
 * RegexRuleMatcher - regra baseada em PCRE.
 *
 * Payload esperado:
 *   {
 *     "pattern": "foo.*bar",     // obrigatorio
 *     "flags":   "i",            // opcional
 *     "target":  "query|body|headers|path|user_agent|all"  // opcional, default "all"
 *   }
 *
 * Timeout: suportado via `pcre.backtrack_limit` global do PHP. Em caso
 * de estouro, a regra retorna null (nao bloqueia) e deveria ser posta
 * em quarentena por um watchdog externo.
 *
 * Spec: .kiro/specs/waf-e-auditoria-seguranca
 * Requisito: 10.3
 */
final class RegexRuleMatcher implements RuleMatcher
{
    public function type(): string
    {
        return WafRule::MATCHER_REGEX;
    }

    public function evaluate(WafRule $rule, WafContext $ctx): ?WafRuleMatch
    {
        $payload = (array) $rule->matcher_payload;
        $pattern = (string) ($payload['pattern'] ?? '');

        if ($pattern === '') {
            return null;
        }

        $flags  = (string) ($payload['flags']  ?? '');
        $target = (string) ($payload['target'] ?? 'all');

        $haystack = $ctx->targetString($target);

        if ($haystack === '') {
            return null;
        }

        $full = '/' . str_replace('/', '\/', $pattern) . '/' . $flags . 'u';

        // Executa com supressao para capturar erros de compilacao PCRE
        $matched = @preg_match($full, $haystack, $m);

        if ($matched === false || $matched === 0) {
            return null;
        }

        $sample = (string) ($m[0] ?? '');
        if (strlen($sample) > 200) {
            $sample = substr($sample, 0, 197) . '...';
        }

        return new WafRuleMatch(
            rule:        $rule,
            score:       (int) $rule->score,
            field:       $target,
            sample:      $sample,
            matcherType: $this->type(),
        );
    }
}
