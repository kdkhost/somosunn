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
     * Numero maximo de tentativas.
     */
    public int $tries = 3;

    /**
     * Timeout em segundos.
     */
    public int $timeout = 60;

    /**
     * Criar nova instancia do job.
     */
    public function __construct(public int $userId)
    {
        //
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
