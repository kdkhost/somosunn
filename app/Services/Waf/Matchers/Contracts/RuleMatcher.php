<?php

namespace App\Services\Waf\Matchers\Contracts;

use App\Models\Waf\WafRule;
use App\Services\Waf\WafContext;
use App\Services\Waf\WafRuleMatch;

/**
 * Contrato para matchers de regra do WAF (Strategy pattern).
 *
 * Spec: .kiro/specs/waf-e-auditoria-seguranca
 * Requisito: 10.3
 */
interface RuleMatcher
{
    /**
     * Identificador do matcher: regex | list | numeric | function.
     */
    public function type(): string;

    /**
     * Avalia a regra contra o contexto. Retorna WafRuleMatch em caso
     * de disparo, ou null caso contrario.
     */
    public function evaluate(WafRule $rule, WafContext $ctx): ?WafRuleMatch;
}
