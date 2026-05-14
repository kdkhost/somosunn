<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\ReputationService;
use Illuminate\Console\Command;

/**
 * Command para recalcular scores de reputacao de todos os membros ativos.
 * Roda diariamente via scheduler ou manualmente para um user especifico.
 */
class RecalculateReputationScores extends Command
{
    protected $signature = 'reputation:recalculate {--user= : Recalcular para um user_id especifico}';

    protected $description = 'Recalcula os scores de reputacao de todos os membros ativos';

    public function handle(): int
    {
        $service = app(ReputationService::class);
        $specificUserId = $this->option('user');

        if ($specificUserId) {
            return $this->recalculateForUser($service, (int) $specificUserId);
        }

        return $this->recalculateAll($service);
    }

    /**
     * Recalcula para um usuario especifico.
     */
    private function recalculateForUser(ReputationService $service, int $userId): int
    {
        $user = User::find($userId);

        if (!$user) {
            $this->error("Usuario com ID {$userId} nao encontrado.");
            return Command::FAILURE;
        }

        $this->info("Recalculando reputacao para: {$user->name} (ID: {$user->id})...");

        try {
            $result = $service->recalculateFor($user);
            $this->info("Score calculado: {$result['score']} ({$result['badge']['label']})");
            return Command::SUCCESS;
        } catch (\Throwable $e) {
            $this->error("Erro ao recalcular: {$e->getMessage()}");
            return Command::FAILURE;
        }
    }

    /**
     * Recalcula para todos os membros ativos em chunks de 100.
     */
    private function recalculateAll(ReputationService $service): int
    {
        $query = User::query()
            ->whereNotNull('email_verified_at')
            ->whereNull('deleted_at');

        // Excluir usuarios bloqueados permanentemente (sem blocked_until = bloqueio permanente nao existe neste sistema)
        $totalUsers = $query->count();

        if ($totalUsers === 0) {
            $this->info('Nenhum membro ativo encontrado.');
            return Command::SUCCESS;
        }

        $this->info("Recalculando reputacao para {$totalUsers} membros...");
        $bar = $this->output->createProgressBar($totalUsers);
        $bar->start();

        $processed = 0;
        $errors = 0;

        $query->chunk(100, function ($users) use ($service, &$processed, &$errors, $bar) {
            foreach ($users as $user) {
                try {
                    $service->recalculateFor($user);
                    $processed++;
                } catch (\Throwable $e) {
                    $errors++;
                    \Illuminate\Support\Facades\Log::warning(
                        "reputation:recalculate erro para user_id={$user->id}: {$e->getMessage()}"
                    );
                }

                $bar->advance();
            }
        });

        $bar->finish();
        $this->newLine(2);
        $this->info("Concluido: {$processed} processados, {$errors} erros.");

        return $errors > 0 ? Command::FAILURE : Command::SUCCESS;
    }
}
