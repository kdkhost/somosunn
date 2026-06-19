<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class CronHeartbeat extends Command
{
    protected $signature = 'cron:heartbeat';

    protected $description = 'Atualiza o heartbeat do scheduler para monitoramento no painel de cron';

    public function handle(): int
    {
        Cache::put('cron_heartbeat', now(), 120);
        $this->info('Heartbeat atualizado em ' . now()->format('d/m/Y H:i:s'));

        return self::SUCCESS;
    }
}
