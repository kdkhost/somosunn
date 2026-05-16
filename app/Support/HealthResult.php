<?php

/**
 * ============================================================
 * PROPRIEDADE INTELECTUAL E DIREITOS AUTORAIS
 * ============================================================
 *
 * @autor marcelo-brad rj
 * @contato
 * Tel: 21 981325441
 * WhatsApp: 21 98132-5441
 * Email: contato@kdkhost.com.br
 * Telegram: @MARCELO_BRAD
 * Instagram: @marcelobradrj
 *
 * ============================================================
 */

namespace App\Support;

/**
 * Value object que agrega o resultado consolidado de um healthcheck.
 *
 * Campos:
 *   - status:           "healthy" | "degraded" | "unhealthy"
 *                        - healthy: todos os componentes "ok"
 *                        - degraded: pelo menos um componente "warning" e nenhum "error"
 *                        - unhealthy: pelo menos um componente "error"
 *   - components:       lista de ComponentStatus (um por componente verificado)
 *   - responseTimeMs:   tempo total da verificacao em milissegundos
 *   - timestamp:        timestamp ISO 8601 (UTC) em que a verificacao foi executada
 *
 * Spec: .kiro/specs/advanced-security-performance
 * Requirements: 9.4, 9.5, 9.6, 9.7
 */
class HealthResult
{
    public const STATUS_HEALTHY = 'healthy';
    public const STATUS_DEGRADED = 'degraded';
    public const STATUS_UNHEALTHY = 'unhealthy';

    /**
     * @param array<int, ComponentStatus> $components
     */
    public function __construct(
        public string $status = self::STATUS_HEALTHY,
        public array $components = [],
        public float $responseTimeMs = 0.0,
        public string $timestamp = '',
    ) {
    }

    /**
     * Recalcula o status agregado a partir dos componentes ja registrados.
     * Regras: error -> unhealthy; warning -> degraded; senao healthy.
     */
    public function recomputeStatus(): void
    {
        $hasError = false;
        $hasWarning = false;
        foreach ($this->components as $component) {
            if (!$component instanceof ComponentStatus) {
                continue;
            }
            if ($component->status === ComponentStatus::STATUS_ERROR) {
                $hasError = true;
            } elseif ($component->status === ComponentStatus::STATUS_WARNING) {
                $hasWarning = true;
            }
        }
        if ($hasError) {
            $this->status = self::STATUS_UNHEALTHY;
        } elseif ($hasWarning) {
            $this->status = self::STATUS_DEGRADED;
        } else {
            $this->status = self::STATUS_HEALTHY;
        }
    }

    /**
     * Converte o resultado para array (formato usado na resposta JSON do endpoint).
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $components = [];
        foreach ($this->components as $component) {
            if ($component instanceof ComponentStatus) {
                $components[$component->name] = $component->toArray();
            }
        }

        return [
            'status' => $this->status,
            'components' => $components,
            'response_time_ms' => round($this->responseTimeMs, 2),
            'timestamp' => $this->timestamp,
        ];
    }
}
