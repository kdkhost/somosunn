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
 * Value object que representa o status de um unico componente verificado
 * pelo HealthController (database, s3, disk_write, queue_health, storage_permissions).
 *
 * Campos:
 *   - name:       identificador do componente (ex: "database", "s3")
 *   - status:     "ok" | "warning" | "error"
 *   - message:    mensagem opcional descrevendo o resultado ou o erro
 *   - latencyMs:  latencia medida em milissegundos para a verificacao
 *
 * Spec: .kiro/specs/advanced-security-performance
 * Requirements: 9.4, 9.5, 9.6, 9.7
 */
class ComponentStatus
{
    public const STATUS_OK = 'ok';
    public const STATUS_WARNING = 'warning';
    public const STATUS_ERROR = 'error';

    public function __construct(
        public string $name,
        public string $status,
        public ?string $message = null,
        public float $latencyMs = 0.0,
    ) {
    }

    /**
     * Converte o status para array (formato usado na resposta JSON do healthcheck).
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'status' => $this->status,
            'message' => $this->message,
            'latency_ms' => round($this->latencyMs, 2),
        ];
    }
}
