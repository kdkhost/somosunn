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
 * Sistema UNN - Job WriteAuditLogJob
 *
 * Job assincrono responsavel por persistir registros de auditoria
 * (audit_logs) sem bloquear o response time da request original.
 *
 * Padrao fail-safe: falhas na persistencia sao logadas no canal
 * `stack` mas NUNCA propagadas, pois a falha de auditoria nao deve
 * derrubar a operacao do usuario nem ficar reagendando indefinidamente.
 *
 * Spec: .kiro/specs/advanced-security-performance
 * Requirements: 6.1, 6.4, 6.7
 */

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class WriteAuditLogJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Numero maximo de tentativas.
     *
     * @var int
     */
    public $tries = 3;

    /**
     * Timeout em segundos.
     *
     * @var int
     */
    public $timeout = 30;

    /**
     * Em caso de exhaustao das tentativas, NAO falhamos a operacao
     * original. O job e marcado como falho e o erro registrado.
     */
    public bool $failOnTimeout = false;

    /**
     * Payload pronto para insercao em audit_logs.
     *
     * @var array<string, mixed>
     */
    public array $payload;

    /**
     * @param array<string, mixed> $payload
     */
    public function __construct(array $payload)
    {
        $this->payload = $payload;
        // Atribuicao via metodo do trait Queueable evita FatalError
        // de redeclaracao de propriedade com default diferente em PHP 8.4+.
        $this->onQueue('default');
    }

    /**
     * Executa a persistencia do registro de auditoria via DB::table.
     *
     * Usa DB::table em vez do model para evitar overhead de eventos
     * Eloquent e garantir que mesmo sem o boot completo da aplicacao
     * o registro seja gravado. Falhas internas sao tratadas (fail-safe)
     * e logadas no canal stack; NAO sao re-propagadas pois auditoria
     * nao deve quebrar o fluxo do usuario.
     */
    public function handle(): void
    {
        try {
            DB::table('audit_logs')->insert($this->normalizePayload($this->payload));
        } catch (\Throwable $e) {
            Log::channel('stack')->error('WriteAuditLogJob: falha ao persistir audit log', [
                'exception' => $e->getMessage(),
                'action' => $this->payload['action'] ?? null,
                'user_id' => $this->payload['user_id'] ?? null,
                'request_id' => $this->payload['request_id'] ?? null,
            ]);
            // NAO re-lanca: auditoria e fail-safe.
        }
    }

    /**
     * Hook chamado pelo Laravel quando o job falha definitivamente
     * (apos esgotar $tries). Apenas loga; nao propaga.
     */
    public function failed(?\Throwable $exception): void
    {
        Log::channel('stack')->error('WriteAuditLogJob: job falhou definitivamente', [
            'exception' => $exception?->getMessage(),
            'action' => $this->payload['action'] ?? null,
            'user_id' => $this->payload['user_id'] ?? null,
            'request_id' => $this->payload['request_id'] ?? null,
        ]);
    }

    /**
     * Converte campos array para JSON e datetimes para string,
     * formato adequado ao DB::table()->insert().
     *
     * @param  array<string, mixed> $payload
     * @return array<string, mixed>
     */
    private function normalizePayload(array $payload): array
    {
        foreach (['old_values', 'new_values', 'metadata'] as $jsonField) {
            if (! array_key_exists($jsonField, $payload)) {
                continue;
            }
            $value = $payload[$jsonField];
            if ($value === null || $value === '') {
                $payload[$jsonField] = null;
                continue;
            }
            if (is_array($value)) {
                $payload[$jsonField] = json_encode(
                    $value,
                    JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
                );
            }
        }

        if (isset($payload['created_at']) && $payload['created_at'] instanceof \DateTimeInterface) {
            $payload['created_at'] = $payload['created_at']->format('Y-m-d H:i:s');
        }

        return $payload;
    }
}
