<?php

namespace App\Services\Waf;

use App\Models\Waf\WafRule;

/**
 * Representa uma WafRule que disparou contra um WafContext.
 *
 * Spec: .kiro/specs/waf-e-auditoria-seguranca
 * Requisitos: 9.4, 12.1
 */
final class WafRuleMatch
{
    /**
     * @param WafRule $rule          Regra disparada
     * @param int     $score         Pontuacao aplicada (pode vir do payload dinamico)
     * @param string  $field         Campo/target onde bateu (query|body|headers|path|user_agent|all)
     * @param string  $sample        Amostra mascarada do trecho que deu match (truncada)
     * @param string  $matcherType   regex|list|numeric|function
     */
    public function __construct(
        public readonly WafRule $rule,
        public readonly int     $score,
        public readonly string  $field,
        public readonly string  $sample,
        public readonly string  $matcherType,
    ) {}

    public function toArray(): array
    {
        return [
            'rule_id'        => $this->rule->id,
            'uid'            => $this->rule->uid,
            'name'           => $this->rule->name,
            'attack_pattern' => $this->rule->attack_pattern,
            'severity'       => $this->rule->severity,
            'action'         => $this->rule->action,
            'score'          => $this->score,
            'field'          => $this->field,
            'matcher_type'   => $this->matcherType,
            'sample'         => $this->sample,
        ];
    }
}
