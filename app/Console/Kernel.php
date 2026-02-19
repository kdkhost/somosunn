<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    protected $commands = [];

    protected function schedule(Schedule $schedule): void
    {
        // Heartbeat para monitoramento no painel
        $schedule->call(function () {
            \Illuminate\Support\Facades\Cache::put('cron_heartbeat', now(), 120);
        })->everyMinute();

        // Carregar tarefas dinâmicas do banco
        try {
            if (\Schema::hasTable('scheduled_tasks')) {
                $tasks = \App\Models\ScheduledTask::where('active', true)->get();
                foreach ($tasks as $task) {
                    $schedule->command($task->command)
                        ->cron($task->frequency)
                        ->withoutOverlapping()
                        ->onOneServer();
                }
            }
        } catch (\Exception $e) {
            \Log::error("Erro ao carregar scheduler do banco: " . $e->getMessage());
        }
    }

    protected function commands(): void
    {
        $this->load(__DIR__ . '/Commands');
        require base_path('routes/console.php');
    }
}
