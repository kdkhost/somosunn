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

        // Tarefas HARDCODED de BACKUP se o banco falhar ou não tiver registros
        // (Opcional: manter como fallback ou remover se quiser total controle no painel)
        if (config('internal_cron.run_queue_worker', true)) {
            // Mantém queue worker rodando se não estiver definido no banco
            // Porem o ideal é migrar tudo para o banco.
            // Vou comentar ou deixar condicional.
            // Para garantir que a fila rode mesmo sem config no banco (fail-safe):
            $schedule->command('queue:work --stop-when-empty --quiet --tries=3')
                ->everyMinute()
                ->withoutOverlapping(60);
        }

        // Limpeza diária de notificações antigas (> 30 dias)
        $schedule->command('notifications:cleanup')->daily();

        // Outros comandos vitais podem ser migrados para o banco via Seeder.
    }

    protected function commands(): void
    {
        $this->load(__DIR__ . '/Commands');
        require base_path('routes/console.php');
    }
}
