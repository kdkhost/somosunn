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
 * Sistema UNN - QueueManagerService
 *
 * Servico responsavel por despachar jobs em background usando o driver
 * `database` do Laravel (compativel com hospedagem compartilhada cPanel),
 * expor metricas das filas (pending, failed), reprocessar jobs falhos
 * e purgar registros antigos. Suporta as filas: uploads, emails,
 * webhooks, notifications, default.
 *
 * Configuracoes (tabela settings):
 *   - queue_retry_attempts (default 3)
 *   - queue_timeout (default 60)
 *
 * Spec: .kiro/specs/advanced-security-performance
 * Requirements: 1.1, 1.6, 1.7, 1.8
 */

namespace App\Services;

use App\Contracts\QueueManagerInterface;
use App\Models\Setting;
use Carbon\Carbon;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class QueueManagerService implements QueueManagerInterface
{
    public const QUEUE_UPLOADS = 'uploads';
    public const QUEUE_EMAILS = 'emails';
    public const QUEUE_WEBHOOKS = 'webhooks';
    public const QUEUE_NOTIFICATIONS = 'notifications';
    public const QUEUE_DEFAULT = 'default';

    /**
     * Lista de filas suportadas pelo gerenciador.
     *
     * @var array<int, string>
     */
    public const SUPPORTED_QUEUES = [
        self::QUEUE_UPLOADS,
        self::QUEUE_EMAILS,
        self::QUEUE_WEBHOOKS,
        self::QUEUE_NOTIFICATIONS,
        self::QUEUE_DEFAULT,
    ];

    private const DEFAULT_RETRY_ATTEMPTS = 3;
    private const DEFAULT_TIMEOUT = 60;

    public function dispatch(string $queue, ShouldQueue $job): void
    {
        $queueName = $this->normalizeQueue($queue);
        $config = $this->getQueueConfig($queueName);

        // Aplica configuracoes de retry/timeout no proprio job (Laravel respeita
        // public $tries e public $timeout quando definidos no job; aqui usamos
        // os setters dinamicos suportados pelo Bus para garantir o valor).
        if (property_exists($job, 'tries')) {
            $job->tries = $config['tries'];
        }
        if (property_exists($job, 'timeout')) {
            $job->timeout = $config['timeout'];
        }

        try {
            Bus::dispatch(
                $job->onQueue($queueName)
                    ->onConnection($config['connection'])
            );
        } catch (\Throwable $e) {
            Log::error('QueueManagerService.dispatch falhou', [
                'queue' => $queueName,
                'job' => get_class($job),
                'exception' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * @return array{
     *     name: string,
     *     connection: string,
     *     tries: int,
     *     timeout: int,
     *     driver: string
     * }
     */
    public function getQueueConfig(string $queue): array
    {
        $queueName = $this->normalizeQueue($queue);
        $connection = (string) Config::get('queue.default', 'database');

        // Garante uso do driver database (configuracao do projeto).
        $driver = (string) Config::get(
            "queue.connections.{$connection}.driver",
            'database'
        );

        return [
            'name' => $queueName,
            'connection' => $connection,
            'tries' => $this->getRetryAttempts(),
            'timeout' => $this->getTimeout(),
            'driver' => $driver,
        ];
    }

    public function getPendingCount(?string $queue = null): int
    {
        try {
            $query = DB::table('jobs');

            if ($queue !== null) {
                $query->where('queue', $this->normalizeQueue($queue));
            }

            return (int) $query->count();
        } catch (\Throwable $e) {
            Log::warning('QueueManagerService.getPendingCount falhou', [
                'queue' => $queue,
                'exception' => $e->getMessage(),
            ]);

            return 0;
        }
    }

    public function getFailedCount(): int
    {
        try {
            return (int) DB::table('failed_jobs')->count();
        } catch (\Throwable $e) {
            Log::warning('QueueManagerService.getFailedCount falhou', [
                'exception' => $e->getMessage(),
            ]);

            return 0;
        }
    }

    public function retryFailed(string $uuid): bool
    {
        $uuid = trim($uuid);

        if ($uuid === '') {
            return false;
        }

        try {
            $exists = DB::table('failed_jobs')
                ->where('uuid', $uuid)
                ->exists();

            if (! $exists) {
                return false;
            }

            $exitCode = Artisan::call('queue:retry', ['id' => [$uuid]]);

            return $exitCode === 0;
        } catch (\Throwable $e) {
            Log::error('QueueManagerService.retryFailed falhou', [
                'uuid' => $uuid,
                'exception' => $e->getMessage(),
            ]);

            return false;
        }
    }

    public function purgeOldFailed(int $daysOld = 30): int
    {
        $days = max(0, $daysOld);
        $cutoff = Carbon::now()->subDays($days);

        try {
            return (int) DB::table('failed_jobs')
                ->where('failed_at', '<', $cutoff)
                ->delete();
        } catch (\Throwable $e) {
            Log::error('QueueManagerService.purgeOldFailed falhou', [
                'days_old' => $days,
                'exception' => $e->getMessage(),
            ]);

            return 0;
        }
    }

    /**
     * Normaliza o nome da queue. Caso o nome nao seja suportado,
     * retorna a queue default para evitar dispatch em filas
     * desconhecidas pelo sistema.
     */
    private function normalizeQueue(string $queue): string
    {
        $normalized = strtolower(trim($queue));

        if (in_array($normalized, self::SUPPORTED_QUEUES, true)) {
            return $normalized;
        }

        return self::QUEUE_DEFAULT;
    }

    private function getRetryAttempts(): int
    {
        $value = Setting::get('queue_retry_attempts', self::DEFAULT_RETRY_ATTEMPTS);
        $value = is_numeric($value) ? (int) $value : self::DEFAULT_RETRY_ATTEMPTS;

        return max(1, $value);
    }

    private function getTimeout(): int
    {
        $value = Setting::get('queue_timeout', self::DEFAULT_TIMEOUT);
        $value = is_numeric($value) ? (int) $value : self::DEFAULT_TIMEOUT;

        return max(1, $value);
    }
}
