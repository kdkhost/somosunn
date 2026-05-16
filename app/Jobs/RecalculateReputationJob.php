<?php

namespace App\Jobs;

use App\Models\User;
use App\Services\ReputationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * Job para recalcular o score de reputacao de um membro especifico.
 * Disparado por observers apos eventos significativos (order delivered, block, etc).
 */
class RecalculateReputationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Queue dedicada para este job (alinhada com QueueManagerService).
     * Job leve, nao se enquadra em uploads/emails/webhooks.
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
    public $timeout = 60;

    /**
     * Backoff em segundos entre tentativas.
     *
     * @var array<int, int>
     */
    public array $backoff = [10, 30, 60];

    /**
     * Criar nova instancia do job.
     */
    public function __construct(public int $userId)
    {
        // Atribuicao via metodo do trait Queueable evita FatalError
        // de redeclaracao de propriedade em PHP 8.4+.
        $this->onQueue('default');
    }

    /**
     * Executar o job.
     */
    public function handle(): void
    {
        $user = User::find($this->userId);

        if (!$user) {
            Log::warning("RecalculateReputationJob: user_id={$this->userId} nao encontrado, ignorando.");
            return;
        }

        try {
            $service = app(ReputationService::class);
            $service->recalculateFor($user);
        } catch (\Throwable $e) {
            Log::error("RecalculateReputationJob falhou para user_id={$this->userId}: {$e->getMessage()}");
            throw $e; // Re-throw para retry
        }
    }
}
