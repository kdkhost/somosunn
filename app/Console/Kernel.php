<?php

namespace App\Console;

use App\Models\ScheduledTask;
use App\Models\ScheduledTaskLog;
use Cron\CronExpression;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class Kernel extends ConsoleKernel
{
    protected $commands = [];

    protected function schedule(Schedule $schedule): void
    {
        $timezone = $this->schedulerTimezone();
        date_default_timezone_set($timezone);
        config(['app.timezone' => $timezone]);

        try {
            if (!Schema::hasTable('scheduled_tasks')) {
                return;
            }

            ScheduledTask::query()
                ->where('active', true)
                ->orderBy('id')
                ->get()
                ->each(function (ScheduledTask $task) use ($schedule, $timezone): void {
                    $frequency = trim((string) $task->frequency);
                    if (!CronExpression::isValidExpression($frequency)) {
                        $this->recordScheduledTaskRun(
                            (int) $task->id,
                            false,
                            'Expressao cron invalida: ' . $frequency
                        );

                        return;
                    }

                    $event = $schedule->command((string) $task->command)
                        ->cron($frequency)
                        ->timezone($timezone)
                        ->withoutOverlapping()
                        ->name('scheduled-task-' . $task->id);

                    $event->onSuccess(function () use ($task): void {
                        $this->recordScheduledTaskRun(
                            (int) $task->id,
                            true,
                            'Executado automaticamente pelo scheduler.'
                        );
                    });

                    $event->onFailure(function () use ($task): void {
                        $this->recordScheduledTaskRun(
                            (int) $task->id,
                            false,
                            'Falha na execucao automatica pelo scheduler.'
                        );
                    });
                });
        } catch (\Throwable $e) {
            Log::error('Erro ao carregar scheduler central do banco: ' . $e->getMessage());
        }
    }

    protected function commands(): void
    {
        $this->load(__DIR__ . '/Commands');
        require base_path('routes/console.php');
    }

    private function schedulerTimezone(): string
    {
        try {
            if (Schema::hasTable('settings')) {
                $timezone = trim((string) \App\Models\Setting::get('system_timezone', ''));
                if ($timezone !== '') {
                    return $timezone;
                }
            }
        } catch (\Throwable $e) {
            Log::warning('Falha ao resolver timezone do scheduler: ' . $e->getMessage());
        }

        return config('app.timezone', 'America/Sao_Paulo') ?: 'America/Sao_Paulo';
    }

    private function recordScheduledTaskRun(int $taskId, bool $success, string $output): void
    {
        try {
            $task = ScheduledTask::query()->find($taskId);
            if (!$task) {
                return;
            }

            $task->forceFill(['last_run_at' => now()])->save();

            if ((string) $task->command === 'cron:heartbeat') {
                return;
            }

            ScheduledTaskLog::create([
                'scheduled_task_id' => $task->id,
                'executed_at' => now(),
                'output' => $output,
                'success' => $success,
            ]);
        } catch (\Throwable $e) {
            Log::warning('Falha ao registrar log de tarefa agendada: ' . $e->getMessage(), [
                'scheduled_task_id' => $taskId,
            ]);
        }
    }
}
