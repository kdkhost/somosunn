<?php

namespace App\Services\Waf;

/**
 * WafDecision - value object imutavel que o engine retorna apos inspecao.
 *
 * Spec: .kiro/specs/waf-e-auditoria-seguranca
 * Requisitos: 9.4, 9.5, 9.6, 9.7
 */
final class WafDecision
{
    public const ALLOWED    = 'allowed';
    public const MONITORED  = 'monitored';
    public const CHALLENGED = 'challenged';
    public const BLOCKED    = 'blocked';

    /**
     * @param string              $decision  allowed|monitored|challenged|blocked
     * @param int                 $status    Status HTTP sugerido (200|403|429|503)
     * @param int                 $riskScore Score final [0,100]
     * @param array<WafRuleMatch> $rules     Regras que dispararam
     */
    public function __construct(
        public readonly string $decision,
        public readonly int    $status,
        public readonly int    $riskScore,
        public readonly array  $rules,
        public readonly string $reason,
        public readonly ?string $eventId = null,
        public readonly ?string $originalDecision = null, // usado quando detection-only rebaixa
    ) {}

    public static function allowed(string $reason = 'below_monitor_threshold'): self
    {
        return new self(self::ALLOWED, 200, 0, [], $reason);
    }

    public static function blocked(int $status, int $riskScore, array $rules, string $reason): self
    {
        return new self(self::BLOCKED, $status, $riskScore, $rules, $reason);
    }

    public static function challenged(int $riskScore, array $rules, string $reason): self
    {
        return new self(self::CHALLENGED, 200, $riskScore, $rules, $reason);
    }

    public static function monitored(int $riskScore, array $rules, string $reason, ?string $originalDecision = null): self
    {
        return new self(self::MONITORED, 200, $riskScore, $rules, $reason, null, $originalDecision);
    }

    public function isBlocked(): bool   { return $this->decision === self::BLOCKED; }
    public function isAllowed(): bool   { return $this->decision === self::ALLOWED; }
    public function isChallenged(): bool { return $this->decision === self::CHALLENGED; }
    public function isMonitored(): bool { return $this->decision === self::MONITORED; }

    public function withEventId(string $eventId): self
    {
        return new self(
            $this->decision,
            $this->status,
            $this->riskScore,
            $this->rules,
            $this->reason,
            $eventId,
            $this->originalDecision,
        );
    }
}
