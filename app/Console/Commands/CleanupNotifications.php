<?php

namespace App\Console\Commands;

use App\Models\Notification;
use Illuminate\Console\Command;

class CleanupNotifications extends Command
{
    protected $signature = 'notifications:cleanup {--days=90 : Dias para manter notificações}';
    protected $description = 'Remove notificações antigas em lotes para evitar travamento do banco';

    public function handle(): int
    {
        $days = max(7, (int) $this->option('days'));
        $this->info("Iniciando limpeza de notificações com mais de {$days} dias...");

        $totalDeleted = 0;
        $batchSize = 500;

        do {
            $deleted = Notification::where('created_at', '<', now()->subDays($days))
                ->limit($batchSize)
                ->delete();

            $totalDeleted += $deleted;

            if ($deleted > 0) {
                usleep(100000); // 100ms entre lotes para não sobrecarregar
            }
        } while ($deleted >= $batchSize);

        $this->info("Sucesso! {$totalDeleted} notificações removidas.");
        return self::SUCCESS;
    }
}
