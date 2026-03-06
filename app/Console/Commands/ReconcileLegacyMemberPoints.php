<?php

namespace App\Console\Commands;

use App\Services\LegacyMemberPointsBackfillService;
use Illuminate\Console\Command;

class ReconcileLegacyMemberPoints extends Command
{
    protected $signature = 'points:reconcile-legacy-members
        {--dry-run : Simula a reconciliacao sem gravar pontos}
        {--user=* : Reprocessa apenas IDs especificos}
        {--chunk=200 : Quantidade de usuarios por lote}';

    protected $description = 'Reconcilia pontos historicos de cadastro, perfil completo, primeiro curso e mentor para membros antigos.';

    public function handle(LegacyMemberPointsBackfillService $service): int
    {
        $summary = $service->run(
            (bool) $this->option('dry-run'),
            max(1, (int) $this->option('chunk')),
            array_map('intval', (array) $this->option('user'))
        );

        if (($summary['skipped'] ?? false) === true) {
            $this->warn('Estrutura minima de pontos nao encontrada. Nada foi processado.');

            return self::SUCCESS;
        }

        $mode = $summary['dry_run'] ? 'SIMULACAO' : 'EXECUCAO';
        $this->info("Reconciliação concluída ({$mode}).");
        $this->line('Usuários analisados: ' . $summary['users_scanned']);
        $this->line('Usuários afetados: ' . $summary['users_affected']);
        $this->line('Pontos adicionados: ' . $summary['points_added']);

        foreach ($summary['actions_awarded'] as $actionKey => $count) {
            $this->line("- {$actionKey}: {$count}");
        }

        return self::SUCCESS;
    }
}
