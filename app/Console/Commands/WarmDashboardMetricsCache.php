<?php

namespace App\Console\Commands;

use App\Services\DashboardMetricsService;
use Illuminate\Console\Command;

class WarmDashboardMetricsCache extends Command
{
    protected $signature = 'dashboard:warm-cache {--fresh : Limpa e recria o cache antes de aquecer} {--user= : Limita a um usuário específico}';

    protected $description = 'Aquece o cache das métricas das dashboards de membro, admin e superadmin.';

    public function __construct(private DashboardMetricsService $metrics)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $userId = $this->option('user');
        $fresh = (bool) $this->option('fresh');
        $summary = $this->metrics->warmAllCaches($fresh, $userId !== null ? (int) $userId : null);

        $this->info(sprintf(
            'Cache aquecido para %d usuário(s); payload admin gerado para %d conta(s).',
            $summary['users_warmed'],
            $summary['admin_caches_warmed']
        ));

        return self::SUCCESS;
    }
}
