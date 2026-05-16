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
 * Sistema UNN - Unit tests para Database Optimizer.
 *
 * Spec: .kiro/specs/advanced-security-performance (task 17.2)
 *
 * Cobre:
 *   1. Trait HasEagerLoading aplica eager loading correto
 *      (scopeWithCommonRelations + scopeWithCounts)
 *   2. Cache de aggregates funciona (AdvancedCacheManager::getDashboardStats)
 *   3. Invalidacao de cache quando entidade modifica
 *      (AdvancedCacheManager::invalidate('dashboard', ...))
 *
 * Requirements: 12.2, 12.3, 12.5
 */

namespace Tests\Unit\Database;

use App\Models\Concerns\HasEagerLoading;
use App\Services\AdvancedCacheManager;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Cache;
use Mockery;
use Tests\TestCase;

class HasEagerLoadingTest extends TestCase
{
    private AdvancedCacheManager $cache;

    protected function setUp(): void
    {
        parent::setUp();

        // Isolamento: usa store array para nao tocar disco/db.
        config()->set('cache.default', 'array');
        Cache::store('array')->flush();
        Cache::flush();

        $this->cache = new AdvancedCacheManager();
    }

    protected function tearDown(): void
    {
        if (class_exists(Mockery::class)) {
            Mockery::close();
        }

        parent::tearDown();
    }

    // ------------------------------------------------------------------
    // 1. Trait HasEagerLoading aplica eager loading correto
    // ------------------------------------------------------------------

    public function test_scope_with_common_relations_applies_eager_loading_when_property_defined(): void
    {
        $model = new class {
            use HasEagerLoading;

            /** @var array<int,string> */
            public array $commonEagerRelations = ['profile', 'roles', 'orders'];
        };

        $builder = Mockery::mock(Builder::class);
        $builder->shouldReceive('with')
            ->once()
            ->with(['profile', 'roles', 'orders'])
            ->andReturnSelf();

        $result = $model->scopeWithCommonRelations($builder);

        $this->assertSame($builder, $result);
    }

    public function test_scope_with_common_relations_skips_eager_loading_when_property_absent(): void
    {
        $model = new class {
            use HasEagerLoading;
        };

        $builder = Mockery::mock(Builder::class);
        // with() NAO deve ser chamado quando nao ha relacoes comuns definidas.
        $builder->shouldNotReceive('with');

        $result = $model->scopeWithCommonRelations($builder);

        $this->assertSame($builder, $result);
    }

    public function test_scope_with_common_relations_skips_eager_loading_when_property_is_empty_array(): void
    {
        $model = new class {
            use HasEagerLoading;

            /** @var array<int,string> */
            public array $commonEagerRelations = [];
        };

        $builder = Mockery::mock(Builder::class);
        $builder->shouldNotReceive('with');

        $result = $model->scopeWithCommonRelations($builder);

        $this->assertSame($builder, $result);
    }

    public function test_scope_with_counts_calls_with_count_for_provided_relations(): void
    {
        $model = new class {
            use HasEagerLoading;
        };

        $builder = Mockery::mock(Builder::class);
        $builder->shouldReceive('withCount')
            ->once()
            ->with(['orders', 'comments'])
            ->andReturnSelf();

        $result = $model->scopeWithCounts($builder, ['orders', 'comments']);

        $this->assertSame($builder, $result);
    }

    public function test_scope_with_counts_skips_when_relations_array_is_empty(): void
    {
        $model = new class {
            use HasEagerLoading;
        };

        $builder = Mockery::mock(Builder::class);
        $builder->shouldNotReceive('withCount');

        $result = $model->scopeWithCounts($builder, []);

        $this->assertSame($builder, $result);
    }

    // ------------------------------------------------------------------
    // 2. Cache de aggregates funciona (Database Optimizer / Cache Manager)
    // ------------------------------------------------------------------

    public function test_cache_aggregate_dashboard_stat_is_persisted_under_unn_dash_key(): void
    {
        // Aggregate pesado simulado: COUNT(*) em users.
        $value = $this->cache->getDashboardStats('users_total', fn () => 1234);

        $this->assertSame(1234, $value);
        $this->assertTrue(Cache::has('unn:dash:users_total'));
        $this->assertSame(1234, Cache::get('unn:dash:users_total'));
    }

    public function test_cache_aggregate_loader_runs_only_once_within_ttl(): void
    {
        $calls = 0;
        $loader = function () use (&$calls) {
            $calls++;
            return ['count' => $calls];
        };

        $first = $this->cache->getDashboardStats('orders_today', $loader);
        $second = $this->cache->getDashboardStats('orders_today', $loader);
        $third = $this->cache->getDashboardStats('orders_today', $loader);

        $this->assertSame(1, $calls, 'loader deve ser invocado apenas uma vez (cache hit nas demais)');
        $this->assertSame(['count' => 1], $first);
        $this->assertSame($first, $second);
        $this->assertSame($first, $third);
    }

    public function test_cache_aggregate_isolates_per_metric_key(): void
    {
        $this->cache->getDashboardStats('users_total', fn () => 100);
        $this->cache->getDashboardStats('orders_today', fn () => 7);

        $this->assertSame(100, Cache::get('unn:dash:users_total'));
        $this->assertSame(7, Cache::get('unn:dash:orders_today'));
    }

    public function test_heavy_query_aggregate_cache_uses_md5_hashed_key(): void
    {
        $logical = 'SELECT COUNT(*) FROM orders WHERE status = \'paid\'';
        $expectedKey = AdvancedCacheManager::PREFIX_QUERY . md5($logical);

        $value = $this->cache->getHeavyQuery($logical, fn () => 42, 5);

        $this->assertSame(42, $value);
        $this->assertTrue(Cache::has($expectedKey));
        $this->assertSame(42, Cache::get($expectedKey));
    }

    // ------------------------------------------------------------------
    // 3. Invalidacao de cache quando entidade modifica
    // ------------------------------------------------------------------

    public function test_invalidating_specific_dashboard_metric_clears_only_that_entry(): void
    {
        $this->cache->getDashboardStats('users_total', fn () => 100);
        $this->cache->getDashboardStats('orders_today', fn () => 7);

        // Simulacao: novo usuario foi criado -> invalida apenas users_total.
        $this->cache->invalidate('dashboard', 'users_total');

        $this->assertFalse(
            Cache::has('unn:dash:users_total'),
            'invalidacao especifica deve remover unn:dash:users_total'
        );
        $this->assertTrue(
            Cache::has('unn:dash:orders_today'),
            'invalidacao especifica nao deve afetar outras metricas'
        );
    }

    public function test_invalidating_dashboard_without_identifier_clears_all_known_metrics(): void
    {
        foreach (AdvancedCacheManager::COMMON_DASHBOARD_METRICS as $metric) {
            Cache::put('unn:dash:' . $metric, 1, 3600);
        }

        // Simulacao: operacao em massa que afeta todas as metricas comuns.
        $this->cache->invalidate('dashboard');

        foreach (AdvancedCacheManager::COMMON_DASHBOARD_METRICS as $metric) {
            $this->assertFalse(
                Cache::has('unn:dash:' . $metric),
                "Cache da metrica '{$metric}' nao foi invalidado"
            );
        }
    }

    public function test_invalidating_heavy_query_aggregate_clears_corresponding_md5_key(): void
    {
        $logical = 'aggregate:orders:revenue:30d';
        $key = AdvancedCacheManager::PREFIX_QUERY . md5($logical);

        $this->cache->getHeavyQuery($logical, fn () => ['sum' => 9999.50], 10);
        $this->assertTrue(Cache::has($key));

        // Simulacao: novo Order salvo -> invalida agregado correspondente.
        $this->cache->invalidate('heavy_query', $logical);

        $this->assertFalse(Cache::has($key));
    }

    public function test_invalidation_then_reload_recomputes_aggregate_via_loader(): void
    {
        $calls = 0;
        $loader = function () use (&$calls) {
            $calls++;
            return $calls * 10;
        };

        // 1a leitura: loader corre, retorna 10.
        $this->assertSame(10, $this->cache->getDashboardStats('revenue_month', $loader));
        // 2a leitura: cache hit, mesmo valor, loader NAO corre.
        $this->assertSame(10, $this->cache->getDashboardStats('revenue_month', $loader));
        $this->assertSame(1, $calls);

        // Entidade modifica -> invalidacao.
        $this->cache->invalidate('dashboard', 'revenue_month');

        // 3a leitura: cache miss apos invalidacao, loader corre de novo, retorna 20.
        $this->assertSame(20, $this->cache->getDashboardStats('revenue_month', $loader));
        $this->assertSame(2, $calls);
    }
}
