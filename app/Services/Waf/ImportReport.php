<?php

namespace App\Services\Waf;

use App\Models\Waf\WafRule;

/**
 * Resultado de uma importação em massa de regras via WafParser::parseMany.
 *
 * Invariante (Property 3):
 *   |accepted| + |rejected| == |input|
 *   Toda entrada rejeitada possui mensagem de erro não vazia.
 *
 * Spec: .kiro/specs/waf-e-auditoria-seguranca
 * Requisito: 20.6
 */
final class ImportReport
{
    /**
     * @param array<WafRule> $accepted  Regras parseadas com sucesso
     * @param array<array>   $rejected  Entradas rejeitadas com {index, error, ?name}
     */
    public function __construct(
        public readonly array $accepted,
        public readonly array $rejected,
    ) {}

    public function totalAccepted(): int
    {
        return count($this->accepted);
    }

    public function totalRejected(): int
    {
        return count($this->rejected);
    }

    public function total(): int
    {
        return $this->totalAccepted() + $this->totalRejected();
    }

    public function hasRejections(): bool
    {
        return ! empty($this->rejected);
    }

    public function toArray(): array
    {
        return [
            'accepted' => $this->totalAccepted(),
            'rejected' => $this->totalRejected(),
            'total'    => $this->total(),
            'errors'   => $this->rejected,
        ];
    }
}
