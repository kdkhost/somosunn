<?php

namespace App\Console;

use App\Support\EmailQueueSettings;
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

        // Processamento de fila de e-mails (configuravel no painel SMTP)
        try {
            if (
                \Schema::hasTable('settings')
                && EmailQueueSettings::shouldQueue()
                && EmailQueueSettings::scheduleEnabled()
                && config('internal_cron.run_queue_worker', true)
            ) {
                $connection = EmailQueueSettings::connection();
                $queue = EmailQueueSettings::queueName();
                $tries = EmailQueueSettings::tries();
                $timeout = EmailQueueSettings::timeout();
                $sleep = EmailQueueSettings::sleep();

                if ($connection !== 'sync') {
                    $schedule->call(function () use ($connection, $queue, $tries, $timeout, $sleep) {
                        \Artisan::call('queue:work', [
                            'connection' => $connection,
                            '--queue' => $queue,
                            '--stop-when-empty' => true,
                            '--tries' => $tries,
                            '--timeout' => $timeout,
                            '--sleep' => $sleep,
                        ]);
                    })->everyMinute()->withoutOverlapping()->name('email-queue-worker');
                }
            }
        } catch (\Throwable $e) {
            \Log::warning('Falha ao configurar worker de fila de e-mails: ' . $e->getMessage());
        }

        // Processamento interno de conversao de videos de aulas (HLS)
        try {
            if ((bool) config('uploads.video_hls_enabled', true)) {
                $limite = max(1, (int) config('uploads.video_hls_scheduler_limit', 2));

                $schedule->command('lessons:process-pending-videos --limit=' . $limite)
                    ->everyMinute()
                    ->withoutOverlapping()
                    ->name('lessons-video-worker');
            }
        } catch (\Throwable $e) {
            \Log::warning('Falha ao configurar worker de videos de aulas: ' . $e->getMessage());
        }

        // Carregar tarefas dinamicas do banco
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
            \Log::error('Erro ao carregar scheduler do banco: ' . $e->getMessage());
        }
    }

    protected function commands(): void
    {
        $this->load(__DIR__ . '/Commands');
        require base_path('routes/console.php');
    }
}
