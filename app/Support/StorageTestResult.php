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
 *
 * Sistema UNN - Value object com o resultado de um teste de
 * conexao a um provedor S3, incluindo detalhamento step-by-step.
 *
 * Spec: .kiro/specs/multi-provider-s3-storage (task 2.2)
 * Requirements: 5.2, 5.3, 5.4, 5.6
 */

namespace App\Support;

final class StorageTestResult
{
    public const STATUS_PENDING = 'pending';
    public const STATUS_SUCCESS = 'success';
    public const STATUS_FAILED = 'failed';
    public const STATUS_TIMEOUT = 'timeout';

    /**
     * Status global do teste. Comeca como pending, vira success
     * quando todos os steps passarem, ou failed/timeout em erro.
     */
    public string $status = self::STATUS_PENDING;

    /**
     * Lista ordenada de steps executados.
     *
     * Cada step e um array:
     *   [
     *     'name'       => 'upload',          // upload | exists | url | http_get | compare | delete
     *     'status'     => 'success'|'failed',
     *     'detail'     => 'mensagem ou metadado curto',
     *     'latency_ms' => 12.34,             // float
     *   ]
     *
     * @var array<int, array{name: string, status: string, detail: string, latency_ms: float}>
     */
    public array $steps = [];

    /**
     * Provedor sob teste (idrive | wasabi | aws). Util para logging.
     */
    public string $provider = '';

    /**
     * Tempo total decorrido no teste, em milissegundos.
     */
    public float $totalLatencyMs = 0.0;

    /**
     * Mensagem de erro de alto nivel quando o teste falha. Mantem
     * um motivo curto e seguro para exibir na UI sem vazar detalhes
     * tecnicos sensiveis (creds, paths internos, etc.).
     */
    public ?string $errorMessage = null;

    public function __construct(string $provider = '')
    {
        $this->provider = $provider;
    }

    /**
     * Adiciona um step bem-sucedido ao resultado.
     */
    public function addSuccess(string $name, string $detail = '', float $latencyMs = 0.0): self
    {
        $this->steps[] = [
            'name' => $name,
            'status' => self::STATUS_SUCCESS,
            'detail' => $detail,
            'latency_ms' => round($latencyMs, 2),
        ];

        return $this;
    }

    /**
     * Adiciona um step que falhou. NAO marca o teste como failed
     * automaticamente - chame markFailed() em seguida para isso.
     */
    public function addFailure(string $name, string $detail, float $latencyMs = 0.0): self
    {
        $this->steps[] = [
            'name' => $name,
            'status' => self::STATUS_FAILED,
            'detail' => $detail,
            'latency_ms' => round($latencyMs, 2),
        ];

        return $this;
    }

    /**
     * Marca o teste como concluido com sucesso. Soma a latencia
     * total a partir da soma das latencias dos steps.
     */
    public function markSuccess(): self
    {
        $this->status = self::STATUS_SUCCESS;
        $this->errorMessage = null;
        $this->recomputeTotalLatency();

        return $this;
    }

    /**
     * Marca o teste como falho com uma mensagem de erro curta.
     */
    public function markFailed(string $errorMessage): self
    {
        $this->status = self::STATUS_FAILED;
        $this->errorMessage = $errorMessage;
        $this->recomputeTotalLatency();

        return $this;
    }

    /**
     * Marca o teste como timeout (Req 5.6). A latencia total sera
     * o limite (30s) caso ainda nao tenha sido computada.
     */
    public function markTimeout(string $detail = 'connection test exceeded 30 second budget'): self
    {
        $this->status = self::STATUS_TIMEOUT;
        $this->errorMessage = $detail;
        $this->recomputeTotalLatency();

        return $this;
    }

    /**
     * Indica se o ultimo step adicionado falhou. Permite ao caller
     * decidir abortar a execucao dos demais (Req 5.4).
     */
    public function lastStepFailed(): bool
    {
        if ($this->steps === []) {
            return false;
        }

        $last = end($this->steps);

        return ($last['status'] ?? '') === self::STATUS_FAILED;
    }

    /**
     * Indica se o teste como um todo terminou com sucesso.
     */
    public function isSuccess(): bool
    {
        return $this->status === self::STATUS_SUCCESS;
    }

    /**
     * Serializacao para JSON (usado pelo controller no endpoint AJAX
     * de teste de conexao).
     *
     * @return array{
     *   provider: string,
     *   status: string,
     *   error_message: ?string,
     *   total_latency_ms: float,
     *   steps: array<int, array{name: string, status: string, detail: string, latency_ms: float}>
     * }
     */
    public function toArray(): array
    {
        return [
            'provider' => $this->provider,
            'status' => $this->status,
            'error_message' => $this->errorMessage,
            'total_latency_ms' => $this->totalLatencyMs,
            'steps' => $this->steps,
        ];
    }

    /**
     * Recalcula a latencia total como soma das latencias dos steps.
     * Util para garantir consistencia apos mudancas no array de steps.
     */
    private function recomputeTotalLatency(): void
    {
        $sum = 0.0;
        foreach ($this->steps as $step) {
            $sum += (float) ($step['latency_ms'] ?? 0.0);
        }
        $this->totalLatencyMs = round($sum, 2);
    }
}
