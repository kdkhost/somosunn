<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class CleanupUnverifiedMembersCommand extends Command
{
    protected $signature = 'members:cleanup-unverified {--hours=72 : Prazo de carência em horas para validação do e-mail} {--dry-run : Apenas listar sem remover}';
    protected $description = 'Remove contas de novos membros que não validaram o e-mail após o prazo de carência';

    // Marco inicial da regra: válido apenas para novos membros cadastrados a partir desta data
    public const NEW_MEMBERS_THRESHOLD_DATE = '2026-11-22 00:00:00';

    public function handle(): int
    {
        $hours = max(24, (int) $this->option('hours'));
        $dryRun = (bool) $this->option('dry-run');

        $cutoff = Carbon::now()->subHours($hours);
        $thresholdDate = Carbon::parse(self::NEW_MEMBERS_THRESHOLD_DATE);

        $query = User::query()
            ->where('created_at', '>=', $thresholdDate)
            ->where('created_at', '<=', $cutoff)
            ->whereNull('email_verified_at')
            ->where(function ($q) {
                $q->whereNull('role')
                    ->orWhereNotIn('role', ['admin', 'superadmin']);
            });

        $count = $query->count();

        if ($count === 0) {
            $this->info("Nenhum novo membro não-validado pendente de limpeza (carência: {$hours}h).");
            return 0;
        }

        $this->warn("Encontradas {$count} conta(s) de novos membros sem validação de e-mail criada(s) há mais de {$hours}h.");

        if ($dryRun) {
            $this->info("[SIMULAÇÃO] Nenhuma conta foi removida.");
            return 0;
        }

        $deletedCount = 0;
        $query->chunk(100, function ($users) use (&$deletedCount) {
            foreach ($users as $user) {
                Log::channel('security')->info('Conta de novo membro não-validada removida automaticamente por expiração', [
                    'user_id' => $user->id,
                    'email' => $user->email,
                    'created_at' => (string) $user->created_at,
                ]);

                $user->delete();
                $deletedCount++;
            }
        });

        $this->info("Sucesso: {$deletedCount} conta(s) não-validadas removidas permanentemente.");
        return 0;
    }
}
