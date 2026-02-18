<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    protected $commands = [];

    protected function schedule(Schedule $schedule): void
    {
        if (config('internal_cron.run_queue_worker', true)) {
            $schedule->command('queue:work --stop-when-empty --quiet')
                ->everyMinute()
                ->withoutOverlapping(55);
        }

        // Prune activity logs older than 3 months
        $schedule->call(function () {
            \App\Models\ActivityLog::where('created_at', '<', now()->subMonths(3))->delete();
        })->daily()->name('prune_activity_logs');

        // Cancel unpaid orders older than 48 hours
        $schedule->command('orders:cancel-unpaid')->hourly()->withoutOverlapping();

        // Send abandoned cart emails (> 24h)
        $schedule->command('orders:abandoned-cart')->hourly()->withoutOverlapping();
    }

    protected function commands(): void
    {
        $this->load(__DIR__ . '/Commands');
        require base_path('routes/console.php');
    }
}
