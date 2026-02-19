<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    protected $commands = [];

    protected function schedule(Schedule $schedule): void
    {
        // Carregar tarefas do banco
        try {
            if (\Schema::hasTable('scheduled_tasks')) {
                $tasks = \App\Models\ScheduledTask::where('active', true)->get();
                foreach ($tasks as $task) {
                    $schedule->command($task->command)
                        ->cron($task->frequency)
                        ->withoutOverlapping()
                        ->onOneServer(); // Opcional se for multi-server
                }
            }
        } catch (\Exception $e) {
            // Log silent or fallback
        }

        // Tarefas agendadas são gerenciadas via Banco de Dados (Tabela scheduled_tasks)
        // Para adicionar novas tarefas padrão, use o ScheduledTasksSeeder.

        // Outros comandos vitais podem ser migrados para o banco via Seeder.
    }

    protected function commands(): void
    {
        $this->load(__DIR__ . '/Commands');
        require base_path('routes/console.php');
    }
}
