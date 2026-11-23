<?php

namespace App\Console\Commands;

use App\Services\PointsService;
use Illuminate\Console\Command;

class ProcessPointsRecurrence extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'points:process-recurrence';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Processa regras recorrentes de pontuação positiva (consistência semanal) e negativa (decaimento por inatividade de login e social).';

    /**
     * Execute the console command.
     */
    public function handle(PointsService $pointsService): int
    {
        $this->info('Iniciando processamento recorrente de pontuação e decaimento por inatividade...');

        $stats = $pointsService->processRecurrenceDecay();

        $this->table(
            ['Métrica', 'Total'],
            [
                ['Membros Verificados', $stats['total_users_checked']],
                ['Penalidades Login 7d (-10 pts)', $stats['penalties_login_7d']],
                ['Penalidades Login 15d (-25 pts)', $stats['penalties_login_15d']],
                ['Penalidades Login 30d (-50 pts)', $stats['penalties_login_30d']],
                ['Penalidades Inatividade Social (-15 pts)', $stats['penalties_social']],
                ['Bônus Membro Ativo Semanal (+20 pts)', $stats['bonuses_active']],
                ['Rankings Sincronizados', $stats['rankings_synced']],
            ]
        );

        $this->info('Processamento recorrente de pontuação concluído com sucesso!');

        return self::SUCCESS;
    }
}
