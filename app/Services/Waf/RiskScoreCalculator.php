<?php

namespace App\Services\Waf;

/**
 * Calcula e classifica o Risk_Score de uma requisicao.
 *
 * Determinismo e monotonia sao garantidos:
 *   - Determinismo (Property 4): mesmas matches => mesmo score
 *   - Monotonia   (Property 5): R subset R'  => calculate(R) <= calculate(R')
 *
 * Spec: .kiro/specs/waf-e-auditoria-seguranca
 * Requisitos: 2.2, 2.3, 3.5, 9.4, 9.5, 9.6, 9.7
 */
final class RiskScoreCalculator
{
    public const MAX_SCORE = 100;
    public const MIN_SCORE = 0;

    /**
     * Soma as pontuacoes das WafRuleMatches e clampa em [0, MAX_SCORE].
     *
     * @param array<WafRuleMatch> $matches
     */
    public function calculate(array $matches): int
    {
        $sum = 0;

        foreach ($matches as $match) {
            if (! $match instanceof WafRuleMatch) {
                continue;
            }
            $sum += max(0, $match->score);
        }

        if ($sum < self::MIN_SCORE) {
            return self::MIN_SCORE;
        }
        if ($sum > self::MAX_SCORE) {
            return self::MAX_SCORE;
        }

        return $sum;
    }

    /**
     * Classifica o score em allowed | monitored | challenged | blocked
     * conforme limiares configurados.
     */
    public function classify(int $score, WafSettings $settings): string
    {
        if ($score >= $settings->thresholdBlock) {
            return WafDecision::BLOCKED;
        }
        if ($score >= $settings->thresholdChallenge) {
            return WafDecision::CHALLENGED;
        }
        if ($score >= $settings->thresholdMonitor) {
            return WafDecision::MONITORED;
        }

        return WafDecision::ALLOWED;
    }
}
