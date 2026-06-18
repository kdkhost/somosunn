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
 * Sistema UNN - Unit tests para AuditLogService
 *
 * Cobre:
 *   - log() despacha WriteAuditLogJob na queue 'default' (Bus::fake);
 *   - log() preserva action, target_type/target_id, old/new values e
 *     metadata no payload do job;
 *   - log() aplica fallback sincrono (DB::table insert) quando o
 *     dispatch da queue falha, sem propagar excecao;
 *   - log() captura excecoes do fallback sincrono e apenas registra
 *     no canal stack (Log::error), JAMAIS propagando para o chamador;
 *   - query() retorna LengthAwarePaginator;
 *   - query() aplica filtros date_from, date_to, user_id, action,
 *     target_type, target_id;
 *   - query() ordena por created_at DESC;
 *   - purgeOld() deleta apenas registros mais antigos que a retencao;
 *   - purgeOld() usa retencao default (90 dias) quando sem argumento;
 *   - purgeOld() captura excecao quando o DB falha e retorna 0 sem
 *     propagar (apenas loga em canal stack).
 *
 * Spec: .kiro/specs/advanced-security-performance (task 9.6)
 * Requirements: 6.1, 6.3, 6.5, 6.7
 */

namespace Tests\Unit\Services;

use App\Jobs\WriteAuditLogJob;
use App\Models\AuditLog;
use App\Services\AuditLogService;
use Carbon\Carbon;
use Illuminate\Contracts\Bus\Dispatcher as BusDispatcher;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Tests\TestCase;

class AuditLogServiceTest extends TestCase
{
    use RefreshDatabase;

    private AuditLogService $service;

    /**
     * Verifica conectividade com o banco antes de acionar RefreshDatabase.
     * Se o banco estiver indisponivel, pula o teste em vez de falhar a suite.
     */
    protected function setUp(): void
    {
        try {
            DB::connection()->getPdo();
        } catch (\Throwable $e) {
            $this->markTestSkipped('Banco de dados indisponivel: ' . $e->getMessage());
        }

        parent::setUp();

        $this->service = new AuditLogService();
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    /* ---------------- log() ---------------- */

    public function test_log_dispatches_write_audit_log_job_on_default_queue(): void
    {
        Bus::fake();

        $this->service->log(AuditLogService::ACTION_LOGIN);

        Bus::assertDispatched(WriteAuditLogJob::class, function (WriteAuditLogJob $job): bool {
            return $job->queue === 'default'
                && ($job->payload['action'] ?? null) === AuditLogService::ACTION_LOGIN
                && array_key_exists('request_id', $job->payload)
                && array_key_exists('created_at', $job->payload);
        });
    }

    public function test_log_includes_target_type_target_id_and_diff_values_in_payload(): void
    {
        Bus::fake();

        // AuditLog e um Eloquent Model concreto disponivel no projeto;
        // usamos uma instancia em memoria (sem persistir) so para
        // alimentar target_type/target_id no payload.
        $target = new AuditLog();
        $target->forceFill(['id' => 42]);

        $this->service->log(
            AuditLogService::ACTION_CONFIG_CHANGE,
            $target,
            ['key' => 'old'],
            ['key' => 'new'],
            ['source' => 'unit-test']
        );

        Bus::assertDispatched(WriteAuditLogJob::class, function (WriteAuditLogJob $job) use ($target): bool {
            $p = $job->payload;
            return ($p['action'] ?? null) === AuditLogService::ACTION_CONFIG_CHANGE
                && ($p['target_type'] ?? null) === get_class($target)
                && ($p['target_id'] ?? null) === 42
                && is_array($p['old_values'] ?? null) && $p['old_values']['key'] === 'old'
                && is_array($p['new_values'] ?? null) && $p['new_values']['key'] === 'new'
                && is_array($p['metadata'] ?? null) && $p['metadata']['source'] === 'unit-test';
        });
    }

    public function test_log_falls_back_to_sync_insert_when_dispatch_throws(): void
    {
        // Substitui o Bus por um mock que ALWAYS lanca, forcando o
        // fallback sincrono via DB::table('audit_logs')->insert(...).
        $this->app->instance(BusDispatcher::class, new AuditLogThrowingBusDispatcher());

        // Garante que o fallback foi de fato disparado (warning no canal stack).
        Log::shouldReceive('channel')->with('stack')->andReturnSelf();
        Log::shouldReceive('warning')->atLeast()->once()->andReturnNull();
        Log::shouldReceive('error')->andReturnNull();

        $this->assertSame(0, AuditLog::query()->count());

        $this->service->log(AuditLogService::ACTION_PAYMENT, null, [], [], ['order_id' => 99]);

        $this->assertSame(
            1,
            AuditLog::query()->where('action', AuditLogService::ACTION_PAYMENT)->count(),
            'Fallback sincrono deveria ter persistido o registro via DB::table.'
        );
    }

    public function test_log_swallows_exception_when_both_dispatch_and_sync_fail(): void
    {
        // Forca o dispatch a falhar.
        $this->app->instance(BusDispatcher::class, new AuditLogThrowingBusDispatcher());

        // Remove a tabela audit_logs para que o fallback sincrono
        // (DB::table('audit_logs')->insert) tambem falhe.
        Schema::drop('audit_logs');

        // Esperamos que Log::error seja chamado pelo menos uma vez
        // (no fallback sincrono que tambem falhou). Nao validamos a
        // mensagem exata, apenas que NAO ha excecao propagada.
        Log::shouldReceive('channel')->with('stack')->andReturnSelf();
        Log::shouldReceive('warning')->andReturnNull();
        Log::shouldReceive('error')->atLeast()->once()->andReturnNull();

        try {
            $this->service->log(AuditLogService::ACTION_LOGIN);
        } catch (\Throwable $e) {
            $this->fail('AuditLogService::log() NUNCA deve propagar excecao: ' . $e->getMessage());
        }

        $this->addToAssertionCount(1);
    }

    /* ---------------- query() ---------------- */

    public function test_query_returns_length_aware_paginator(): void
    {
        $this->seedAuditLog(['action' => 'login']);
        $this->seedAuditLog(['action' => 'logout']);

        $result = $this->service->query([], 10);

        $this->assertInstanceOf(LengthAwarePaginator::class, $result);
        $this->assertSame(2, $result->total());
        $this->assertSame(10, $result->perPage());
    }

    public function test_query_orders_by_created_at_desc(): void
    {
        $older = $this->seedAuditLog([
            'action' => 'login',
            'created_at' => Carbon::now()->subDays(5),
        ]);
        $newer = $this->seedAuditLog([
            'action' => 'login',
            'created_at' => Carbon::now()->subDay(),
        ]);

        $page = $this->service->query([], 50);
        $items = $page->items();

        $this->assertCount(2, $items);
        $this->assertSame($newer->id, $items[0]->id);
        $this->assertSame($older->id, $items[1]->id);
    }

    public function test_query_filters_by_date_range(): void
    {
        $this->seedAuditLog([
            'action' => 'login',
            'created_at' => Carbon::create(2024, 1, 1, 12, 0, 0),
        ]);
        $inside = $this->seedAuditLog([
            'action' => 'login',
            'created_at' => Carbon::create(2024, 6, 15, 12, 0, 0),
        ]);
        $this->seedAuditLog([
            'action' => 'login',
            'created_at' => Carbon::create(2024, 12, 31, 12, 0, 0),
        ]);

        $page = $this->service->query([
            'date_from' => '2024-03-01 00:00:00',
            'date_to' => '2024-09-30 23:59:59',
        ], 50);

        $this->assertSame(1, $page->total());
        $this->assertSame($inside->id, $page->items()[0]->id);
    }

    public function test_query_filters_by_user_id_and_action(): void
    {
        $this->seedAuditLog(['action' => 'login', 'user_id' => 1]);
        $this->seedAuditLog(['action' => 'logout', 'user_id' => 1]);
        $expected = $this->seedAuditLog(['action' => 'login', 'user_id' => 2]);
        $this->seedAuditLog(['action' => 'login', 'user_id' => 3]);

        $page = $this->service->query([
            'user_id' => 2,
            'action' => 'login',
        ], 50);

        $this->assertSame(1, $page->total());
        $this->assertSame($expected->id, $page->items()[0]->id);
    }

    public function test_query_filters_by_target_type_and_target_id(): void
    {
        $this->seedAuditLog([
            'action' => 'admin_action',
            'target_type' => 'App\\Models\\User',
            'target_id' => 10,
        ]);
        $expected = $this->seedAuditLog([
            'action' => 'admin_action',
            'target_type' => 'App\\Models\\Order',
            'target_id' => 55,
        ]);
        $this->seedAuditLog([
            'action' => 'admin_action',
            'target_type' => 'App\\Models\\Order',
            'target_id' => 99,
        ]);

        $page = $this->service->query([
            'target_type' => 'App\\Models\\Order',
            'target_id' => 55,
        ], 50);

        $this->assertSame(1, $page->total());
        $this->assertSame($expected->id, $page->items()[0]->id);
    }

    /* ---------------- purgeOld() ---------------- */

    public function test_purge_old_deletes_only_records_older_than_retention(): void
    {
        $now = Carbon::create(2024, 6, 15, 12, 0, 0);
        Carbon::setTestNow($now);

        // 3 antigos (devem ser deletados)
        for ($i = 0; $i < 3; $i++) {
            $this->seedAuditLog([
                'action' => 'old',
                'created_at' => $now->copy()->subDays(91),
            ]);
        }
        // 2 recentes (devem permanecer)
        for ($i = 0; $i < 2; $i++) {
            $this->seedAuditLog([
                'action' => 'recent',
                'created_at' => $now->copy()->subDays(10),
            ]);
        }

        $deleted = $this->service->purgeOld(90);

        $this->assertSame(3, $deleted);
        $this->assertSame(0, AuditLog::query()->where('action', 'old')->count());
        $this->assertSame(2, AuditLog::query()->where('action', 'recent')->count());
    }

    public function test_purge_old_uses_default_retention_of_90_days_when_called_without_args(): void
    {
        $now = Carbon::create(2024, 6, 15, 12, 0, 0);
        Carbon::setTestNow($now);

        // Antigos: 91 dias atras (devem cair).
        $this->seedAuditLog([
            'action' => 'old',
            'created_at' => $now->copy()->subDays(91),
        ]);
        // Recentes: 89 dias atras (devem permanecer).
        $this->seedAuditLog([
            'action' => 'recent',
            'created_at' => $now->copy()->subDays(89),
        ]);

        $deleted = $this->service->purgeOld();

        $this->assertSame(1, $deleted);
        $this->assertSame(0, AuditLog::query()->where('action', 'old')->count());
        $this->assertSame(1, AuditLog::query()->where('action', 'recent')->count());
    }

    public function test_purge_old_returns_zero_and_logs_when_db_fails(): void
    {
        // Sem a tabela audit_logs, o delete lanca QueryException;
        // o service deve capturar e retornar 0, registrando em error.
        Schema::drop('audit_logs');

        Log::shouldReceive('channel')->with('stack')->andReturnSelf();
        Log::shouldReceive('error')->atLeast()->once()->andReturnNull();

        $deleted = $this->service->purgeOld(30);

        $this->assertSame(0, $deleted);
    }

    /* ---------------- helpers ---------------- */

    /**
     * Cria um audit_log com defaults sensatos para os filtros de query.
     *
     * @param  array<string, mixed> $overrides
     */
    private function seedAuditLog(array $overrides = []): AuditLog
    {
        $defaults = [
            'user_id' => null,
            'ip_address' => '127.0.0.1',
            'user_agent' => 'unit-test',
            'action' => 'login',
            'target_type' => null,
            'target_id' => null,
            'old_values' => null,
            'new_values' => null,
            'request_id' => (string) Str::uuid(),
            'metadata' => null,
            'created_at' => Carbon::now(),
        ];

        return AuditLog::create(array_merge($defaults, $overrides));
    }
}

/**
 * Bus dispatcher de testes que sempre lanca excecao no dispatch.
 * Usado para forcar o caminho de fallback do AuditLogService::log().
 */
class AuditLogThrowingBusDispatcher implements BusDispatcher
{
    public function dispatch($command)
    {
        throw new \RuntimeException('forced dispatch failure (test)');
    }

    public function dispatchSync($command, $handler = null)
    {
        throw new \RuntimeException('forced dispatchSync failure (test)');
    }

    public function dispatchNow($command, $handler = null)
    {
        throw new \RuntimeException('forced dispatchNow failure (test)');
    }

    public function hasCommandHandler($command)
    {
        return false;
    }

    public function getCommandHandler($command)
    {
        return false;
    }

    public function pipeThrough(array $pipes)
    {
        return $this;
    }

    public function map(array $map)
    {
        return $this;
    }
}
