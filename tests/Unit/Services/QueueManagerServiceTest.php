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
 * Sistema UNN - Unit tests para QueueManagerService
 *
 * Cobre:
 *  - dispatch() para cada queue suportada (uploads, emails, webhooks,
 *    notifications, default) usando Bus::fake();
 *  - normalizacao de queue desconhecida para "default";
 *  - getPendingCount() (com e sem filtro por queue);
 *  - getFailedCount() (count na tabela failed_jobs);
 *  - retryFailed() (uuid vazio, inexistente e existente);
 *  - purgeOldFailed() (apenas registros mais antigos sao removidos);
 *  - getQueueConfig() (le retry/timeout das settings).
 *
 * Spec: .kiro/specs/advanced-security-performance (task 3.3)
 * Requirements: 1.1, 1.6, 1.7, 1.8
 */

namespace Tests\Unit\Services;

use App\Models\Setting;
use App\Services\QueueManagerService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class QueueManagerServiceTest extends TestCase
{
    use RefreshDatabase;

    private QueueManagerService $service;

    protected function setUp(): void
    {
        parent::setUp();

        Setting::flushRuntimeCache();

        $this->service = new QueueManagerService();
    }

    /* ---------- dispatch() ---------- */

    public function test_it_dispatches_to_uploads_queue(): void
    {
        $this->assertDispatchUsesQueue(QueueManagerService::QUEUE_UPLOADS);
    }

    public function test_it_dispatches_to_emails_queue(): void
    {
        $this->assertDispatchUsesQueue(QueueManagerService::QUEUE_EMAILS);
    }

    public function test_it_dispatches_to_webhooks_queue(): void
    {
        $this->assertDispatchUsesQueue(QueueManagerService::QUEUE_WEBHOOKS);
    }

    public function test_it_dispatches_to_notifications_queue(): void
    {
        $this->assertDispatchUsesQueue(QueueManagerService::QUEUE_NOTIFICATIONS);
    }

    public function test_it_dispatches_to_default_queue(): void
    {
        $this->assertDispatchUsesQueue(QueueManagerService::QUEUE_DEFAULT);
    }

    public function test_it_falls_back_to_default_queue_for_unknown_queue_name(): void
    {
        Bus::fake();

        $this->service->dispatch('queue-inexistente', new QueueManagerStubJob());

        Bus::assertDispatched(QueueManagerStubJob::class, function (QueueManagerStubJob $job) {
            return $job->queue === QueueManagerService::QUEUE_DEFAULT;
        });
    }

    public function test_it_applies_tries_and_timeout_from_settings_on_dispatched_job(): void
    {
        Setting::query()->updateOrInsert(['key' => 'queue_retry_attempts'], [
            'key' => 'queue_retry_attempts',
            'value' => '7',
            'group' => 'queue',
            'updated_at' => now(),
            'created_at' => now(),
        ]);
        Setting::query()->updateOrInsert(['key' => 'queue_timeout'], [
            'key' => 'queue_timeout',
            'value' => '123',
            'group' => 'queue',
            'updated_at' => now(),
            'created_at' => now(),
        ]);
        Setting::flushRuntimeCache();

        Bus::fake();

        $this->service->dispatch(QueueManagerService::QUEUE_EMAILS, new QueueManagerStubJob());

        Bus::assertDispatched(QueueManagerStubJob::class, function (QueueManagerStubJob $job) {
            return $job->tries === 7
                && $job->timeout === 123
                && $job->queue === QueueManagerService::QUEUE_EMAILS;
        });
    }

    /* ---------- getQueueConfig() ---------- */

    public function test_get_queue_config_returns_expected_keys_with_default_settings(): void
    {
        $config = $this->service->getQueueConfig(QueueManagerService::QUEUE_UPLOADS);

        $this->assertArrayHasKey('name', $config);
        $this->assertArrayHasKey('connection', $config);
        $this->assertArrayHasKey('tries', $config);
        $this->assertArrayHasKey('timeout', $config);
        $this->assertArrayHasKey('driver', $config);

        $this->assertSame(QueueManagerService::QUEUE_UPLOADS, $config['name']);
        // Defaults aplicados quando nao ha settings explicitas.
        $this->assertSame(3, $config['tries']);
        $this->assertSame(60, $config['timeout']);
        $this->assertIsString($config['connection']);
        $this->assertIsString($config['driver']);
    }

    public function test_get_queue_config_normalizes_unknown_queue_to_default(): void
    {
        $config = $this->service->getQueueConfig('queue-fantasia');

        $this->assertSame(QueueManagerService::QUEUE_DEFAULT, $config['name']);
    }

    public function test_get_queue_config_reads_overrides_from_settings(): void
    {
        Setting::query()->updateOrInsert(['key' => 'queue_retry_attempts'], [
            'key' => 'queue_retry_attempts',
            'value' => '5',
            'group' => 'queue',
            'updated_at' => now(),
            'created_at' => now(),
        ]);
        Setting::query()->updateOrInsert(['key' => 'queue_timeout'], [
            'key' => 'queue_timeout',
            'value' => '90',
            'group' => 'queue',
            'updated_at' => now(),
            'created_at' => now(),
        ]);
        Setting::flushRuntimeCache();

        $config = $this->service->getQueueConfig(QueueManagerService::QUEUE_WEBHOOKS);

        $this->assertSame(5, $config['tries']);
        $this->assertSame(90, $config['timeout']);
    }

    /* ---------- getPendingCount() ---------- */

    public function test_get_pending_count_returns_zero_when_jobs_table_empty(): void
    {
        $this->assertSame(0, $this->service->getPendingCount());
        $this->assertSame(0, $this->service->getPendingCount(QueueManagerService::QUEUE_UPLOADS));
    }

    public function test_get_pending_count_returns_total_and_filters_by_queue(): void
    {
        $this->insertJob(QueueManagerService::QUEUE_UPLOADS);
        $this->insertJob(QueueManagerService::QUEUE_UPLOADS);
        $this->insertJob(QueueManagerService::QUEUE_EMAILS);

        $this->assertSame(3, $this->service->getPendingCount());
        $this->assertSame(2, $this->service->getPendingCount(QueueManagerService::QUEUE_UPLOADS));
        $this->assertSame(1, $this->service->getPendingCount(QueueManagerService::QUEUE_EMAILS));
        $this->assertSame(0, $this->service->getPendingCount(QueueManagerService::QUEUE_WEBHOOKS));
    }

    /* ---------- getFailedCount() ---------- */

    public function test_get_failed_count_returns_zero_when_no_failures(): void
    {
        $this->assertSame(0, $this->service->getFailedCount());
    }

    public function test_get_failed_count_counts_records_in_failed_jobs(): void
    {
        $this->insertFailedJob('uuid-1', now()->subDay());
        $this->insertFailedJob('uuid-2', now()->subHour());

        $this->assertSame(2, $this->service->getFailedCount());
    }

    /* ---------- retryFailed() ---------- */

    public function test_retry_failed_returns_false_when_uuid_is_empty(): void
    {
        $this->assertFalse($this->service->retryFailed(''));
        $this->assertFalse($this->service->retryFailed('   '));
    }

    public function test_retry_failed_returns_false_when_uuid_not_found(): void
    {
        // Sem registros em failed_jobs: nao deve nem chamar Artisan.
        Artisan::shouldReceive('call')->never();

        $this->assertFalse($this->service->retryFailed('uuid-inexistente'));
    }

    public function test_retry_failed_calls_artisan_when_uuid_exists(): void
    {
        $this->insertFailedJob('uuid-abc', now()->subHour());

        Artisan::shouldReceive('call')
            ->once()
            ->with('queue:retry', ['id' => ['uuid-abc']])
            ->andReturn(0);

        $this->assertTrue($this->service->retryFailed('uuid-abc'));
    }

    public function test_retry_failed_returns_false_when_artisan_returns_non_zero(): void
    {
        $this->insertFailedJob('uuid-fail', now()->subHour());

        Artisan::shouldReceive('call')
            ->once()
            ->with('queue:retry', ['id' => ['uuid-fail']])
            ->andReturn(1);

        $this->assertFalse($this->service->retryFailed('uuid-fail'));
    }

    /* ---------- purgeOldFailed() ---------- */

    public function test_purge_old_failed_removes_only_records_older_than_cutoff(): void
    {
        // Antigo (deve ser removido)
        $this->insertFailedJob('old-1', now()->subDays(40));
        $this->insertFailedJob('old-2', now()->subDays(31));
        // Recente (deve permanecer)
        $this->insertFailedJob('recent-1', now()->subDays(10));
        $this->insertFailedJob('recent-2', now()->subDays(1));

        $deleted = $this->service->purgeOldFailed(30);

        $this->assertSame(2, $deleted);
        $this->assertSame(2, DB::table('failed_jobs')->count());
        $this->assertTrue(
            DB::table('failed_jobs')->where('uuid', 'recent-1')->exists()
        );
        $this->assertTrue(
            DB::table('failed_jobs')->where('uuid', 'recent-2')->exists()
        );
    }

    public function test_purge_old_failed_with_zero_days_removes_records_with_past_failed_at(): void
    {
        $this->insertFailedJob('past', now()->subSecond());

        $deleted = $this->service->purgeOldFailed(0);

        $this->assertSame(1, $deleted);
        $this->assertSame(0, DB::table('failed_jobs')->count());
    }

    public function test_purge_old_failed_returns_zero_when_no_old_records(): void
    {
        $this->insertFailedJob('recent', now()->subDay());

        $this->assertSame(0, $this->service->purgeOldFailed(30));
        $this->assertSame(1, DB::table('failed_jobs')->count());
    }

    /* ---------- helpers ---------- */

    private function assertDispatchUsesQueue(string $queue): void
    {
        Bus::fake();

        $this->service->dispatch($queue, new QueueManagerStubJob());

        Bus::assertDispatched(QueueManagerStubJob::class, function (QueueManagerStubJob $job) use ($queue) {
            return $job->queue === $queue;
        });
    }

    private function insertJob(string $queue): void
    {
        DB::table('jobs')->insert([
            'queue' => $queue,
            'payload' => json_encode(['displayName' => 'Test', 'job' => 'TestJob']),
            'attempts' => 0,
            'reserved_at' => null,
            'available_at' => time(),
            'created_at' => time(),
        ]);
    }

    private function insertFailedJob(string $uuid, \DateTimeInterface $failedAt): void
    {
        DB::table('failed_jobs')->insert([
            'uuid' => $uuid,
            'connection' => 'database',
            'queue' => 'default',
            'payload' => '{}',
            'exception' => 'TestException',
            'failed_at' => $failedAt,
        ]);
    }
}

/**
 * Job stub usado exclusivamente nos testes para inspecionar
 * onQueue/onConnection sem necessidade de uma classe real.
 */
class QueueManagerStubJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /** @var int|null */
    public $tries;

    /** @var int|null */
    public $timeout;

    public function handle(): void
    {
        // noop - test fixture
    }
}
