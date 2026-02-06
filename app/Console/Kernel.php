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
    }

    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');
        require base_path('routes/console.php');
    }
}
