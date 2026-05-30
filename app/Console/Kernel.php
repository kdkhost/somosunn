<?php

/**
 * ============================================================
 * PROPRIEDADE INTELECTUAL E DIREITOS AUTORAIS
 * ============================================================
 *
 * @autor marcelo-brad rj
 * @contato
 * Tel: 21 981325441
 * WhatsApp: 21 98132-5441
 * Email: contato@kdkhost.com.br
 * Telegram: @MARCELO_BRAD
 * Instagram: @marcelobradrj
 *
 * ============================================================
 *
 * Sistema UNN - Console Kernel
 *
 * Registra os scheduled commands da plataforma. Inclui agendamentos
 * de backup (database/config), rotacao de logs, processamento de
 * fila padrao, recalculo de reputacao, expiracao de carrinhos e
 * demais rotinas operacionais.
 *
 * Spec: .kiro/specs/advanced-security-performance
 * Requirements: 1.6, 7.5, 10.4
 */

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

        // Pontua semanalmente os 10 usuários no topo do ranking de pontos
        try {
            $schedule->command('points:award-top-ranking')
                ->weekly()
                ->sundays()
                ->at('00:05')
                ->withoutOverlapping()
                ->name('points-award-top-ranking');
        } catch (\Throwable $e) {
            \Log::warning('Falha ao configurar agendamento de top ranking: ' . $e->getMessage());
        }

        // Premia aniversariantes do dia com birthday_bonus
        try {
            $schedule->command('points:award-birthday-bonus')
                ->dailyAt('01:00')
                ->withoutOverlapping()
                ->name('points-award-birthday-bonus');
        } catch (\Throwable $e) {
            \Log::warning('Falha ao configurar agendamento de birthday bonus: ' . $e->getMessage());
        }

        // Expira solicitações de compartilhamento não respondidas em 7 dias
        try {
            $schedule->command('share-requests:expire')
                ->dailyAt('02:00')
                ->withoutOverlapping()
                ->name('share-requests-expire');
        } catch (\Throwable $e) {
            \Log::warning('Falha ao configurar agendamento de expiração de share requests: ' . $e->getMessage());
        }

        try {
            $schedule->command('dashboard:warm-cache')
                ->everyFiveMinutes()
                ->withoutOverlapping()
                ->name('dashboard-warm-cache');
        } catch (\Throwable $e) {
            \Log::warning('Falha ao configurar aquecimento de cache das dashboards: ' . $e->getMessage());
        }

        // Concilia SumUp antes de cancelar pendencias, cobrindo webhooks atrasados ou bloqueados.
        try {
            $schedule->command('sumup:reconcile-pending --limit=100')
                ->everyFiveMinutes()
                ->withoutOverlapping()
                ->name('sumup-reconcile-pending');
        } catch (\Throwable $e) {
            \Log::warning('Falha ao configurar reconciliacao SumUp: ' . $e->getMessage());
        }

        // Cancela pedidos não pagos após expiração do prazo (PIX, cartão, boleto)
        try {
            $schedule->command('orders:cancel-unpaid')
                ->everyFiveMinutes()
                ->withoutOverlapping()
                ->name('orders-cancel-unpaid');
        } catch (\Throwable $e) {
            \Log::warning('Falha ao configurar cancelamento automático de pedidos: ' . $e->getMessage());
        }

        // Cancela resgates nao entregues no prazo e aplica punicao ao vendedor
        try {
            $schedule->command('redemptions:check-expired')
                ->hourly()
                ->withoutOverlapping()
                ->name('redemptions-check-expired');
        } catch (\Throwable $e) {
            \Log::warning('Falha ao configurar verificacao de resgates expirados: ' . $e->getMessage());
        }

        // Limpa itens de carrinho expirados (24h padrão, configurável)
        try {
            $schedule->command('cart:cleanup-expired')
                ->hourly()
                ->withoutOverlapping()
                ->name('cart-cleanup-expired');
        } catch (\Throwable $e) {
            \Log::warning('Falha ao configurar cleanup de carrinho: ' . $e->getMessage());
        }

        // Verifica assinaturas expiradas e envia lembretes de renovação
        try {
            $schedule->command('subscriptions:check-expired')
                ->dailyAt('06:00')
                ->withoutOverlapping()
                ->name('subscriptions-check-expired');
        } catch (\Throwable $e) {
            \Log::warning('Falha ao configurar verificação de assinaturas: ' . $e->getMessage());
        }

        // Envia emails de aniversário personalizados
        try {
            $schedule->command('users:send-birthday-emails')
                ->dailyAt('07:00')
                ->withoutOverlapping()
                ->name('users-birthday-emails');
        } catch (\Throwable $e) {
            \Log::warning('Falha ao configurar emails de aniversário: ' . $e->getMessage());
        }

        // Envia lembretes de faturas em atraso
        try {
            $schedule->command('invoices:send-overdue-reminders')
                ->dailyAt('08:00')
                ->withoutOverlapping()
                ->name('invoices-overdue-reminders');
        } catch (\Throwable $e) {
            \Log::warning('Falha ao configurar lembretes de faturas: ' . $e->getMessage());
        }

        // Verifica virada de lotes de ingressos dos eventos
        try {
            $schedule->command('events:update-batches')
                ->everyFifteenMinutes()
                ->withoutOverlapping()
                ->name('events-update-batches');
        } catch (\Throwable $e) {
            \Log::warning('Falha ao configurar atualização de lotes de eventos: ' . $e->getMessage());
        }

        // WAF: verifica picos de bloqueios e dispara alertas
        try {
            $schedule->command('waf:check-spike')
                ->everyFiveMinutes()
                ->withoutOverlapping()
                ->name('waf-check-spike');
        } catch (\Throwable $e) {
            \Log::warning('Falha ao configurar verificacao de picos WAF: ' . $e->getMessage());
        }

        // WAF: limpa eventos expirados e IPs expirados (diario)
        try {
            $schedule->command('waf:purge-events')
                ->dailyAt('04:00')
                ->withoutOverlapping()
                ->name('waf-purge-events');
        } catch (\Throwable $e) {
            \Log::warning('Falha ao configurar purge de eventos WAF: ' . $e->getMessage());
        }

        try {
            $schedule->command('waf:purge-ips')
                ->dailyAt('04:15')
                ->withoutOverlapping()
                ->name('waf-purge-ips');
        } catch (\Throwable $e) {
            \Log::warning('Falha ao configurar purge de IPs WAF: ' . $e->getMessage());
        }

        // Envia emails de carrinho abandonado (marketplace)
        try {
            $schedule->command('abandoned-cart:send')
                ->everyFourHours()
                ->withoutOverlapping()
                ->name('abandoned-cart-emails');
        } catch (\Throwable $e) {
            \Log::warning('Falha ao configurar emails de carrinho abandonado: ' . $e->getMessage());
        }

        // Limpa notificações antigas (mais de 90 dias)
        try {
            $schedule->command('notifications:cleanup')
                ->dailyAt('03:00')
                ->withoutOverlapping()
                ->name('notifications-cleanup');
        } catch (\Throwable $e) {
            \Log::warning('Falha ao configurar limpeza de notificações: ' . $e->getMessage());
        }

        // Solicita feedback de compradores 14 dias após a compra
        try {
            $schedule->command('feedback:request-daily')
                ->dailyAt('09:00')
                ->withoutOverlapping()
                ->name('feedback-request-daily');
        } catch (\Throwable $e) {
            \Log::warning('Falha ao configurar solicitação de feedback: ' . $e->getMessage());
        }

        // Log rotation (advanced-security-performance, Requirement 10.4)
        try {
            $schedule->command('logs:cleanup')
                ->dailyAt('02:00')
                ->withoutOverlapping()
                ->name('logs-cleanup');
        } catch (\Throwable $e) {
            \Log::warning('Falha ao configurar rotacao de logs: ' . $e->getMessage());
        }

        // Recalcula scores de reputacao dos membros diariamente
        try {
            $schedule->command('reputation:recalculate')
                ->daily()
                ->withoutOverlapping()
                ->name('reputation-recalculate');
        } catch (\Throwable $e) {
            \Log::warning('Falha ao configurar recalculo de reputacao: ' . $e->getMessage());
        }

        // Backup automatico (advanced-security-performance, Requirement 7.5)
        try {
            $schedule->command('backup:database')
                ->dailyAt('03:00')
                ->withoutOverlapping()
                ->onOneServer()
                ->runInBackground()
                ->onFailure(function () {
                    \Illuminate\Support\Facades\Log::channel('security')->error(
                        'Scheduled backup:database falhou durante execucao.',
                        ['command' => 'backup:database', 'scheduled_at' => '03:00']
                    );
                });

            $schedule->command('backup:config')
                ->weeklyOn(0, '04:00')  // Sunday at 4am
                ->withoutOverlapping()
                ->onOneServer()
                ->runInBackground();
        } catch (\Throwable $e) {
            \Log::warning('Falha ao configurar agendamento de backups: ' . $e->getMessage());
        }

        // Worker generico de fila (advanced-security-performance, Requirement 1.6)
        // Processa filas default/uploads/webhooks/notifications. A fila `emails`
        // possui worker dedicado configurado acima via EmailQueueSettings.
        try {
            $schedule->command('queue:work', [
                '--stop-when-empty',
                '--tries=3',
                '--timeout=120',
            ])
                ->everyMinute()
                ->withoutOverlapping()
                ->runInBackground()
                ->name('queue-work-default');
        } catch (\Throwable $e) {
            \Log::warning('Falha ao configurar worker generico de fila: ' . $e->getMessage());
        }

        // Carregar tarefas dinamicas do banco
        try {
            if (\Schema::hasTable('scheduled_tasks')) {
                $tasks = \App\Models\ScheduledTask::where('active', true)->get();
                foreach ($tasks as $task) {
                    $schedule->command($task->command)
                        ->cron($task->frequency)
                        ->withoutOverlapping()
                        ->after(function () use ($task) {
                            try {
                                $task->last_run_at = now();
                                $task->save();

                                \App\Models\ScheduledTaskLog::create([
                                    'scheduled_task_id' => $task->id,
                                    'executed_at' => now(),
                                    'output' => 'Executado automaticamente pelo scheduler.',
                                    'success' => true,
                                ]);
                            } catch (\Throwable $e) {
                                \Log::warning('Falha ao registrar log de tarefa agendada: ' . $e->getMessage());
                            }
                        });
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
