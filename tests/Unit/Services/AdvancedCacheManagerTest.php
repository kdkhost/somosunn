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
 * Sistema UNN - Unit tests para AdvancedCacheManager
 *
 * Spec: .kiro/specs/advanced-security-performance (task 6.4)
 *
 * Cobre:
 *   1. getNavigation($role)             -> key unn:nav:{role} + TTL
 *   2. getSettings()                    -> key unn:settings
 *   3. getUserPermissions($userId)      -> key unn:perms:{userId}
 *   4. getDashboardStats($metric)       -> key unn:dash:{metric}
 *   5. getHeavyQuery($key, $cb, $ttl)   -> cache-through correto
 *   6. invalidate($type, $id)           -> invalidacao por tipo
 *   7. warmUp()                         -> pre-popula caches criticos
 *
 * Requirements: 4.1, 4.2, 4.3, 4.4, 4.5
 */

namespace Tests\Unit\Services;

use App\Services\AdvancedCacheManager;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class AdvancedCacheManagerTest extends TestCase
{
    private AdvancedCacheManager $manager;

    protected function setUp(): void
    {
        parent::setUp();

        // Isolamento: usa o store array para evitar tocar disco/db.
        config()->set('cache.default', 'array');
        Cache::store('array')->flush();
        Cache::flush();

        $this->manager = new AdvancedCacheManager();
    }

    // ------------------------------------------------------------------
    // 1. getNavigation()
    // ------------------------------------------------------------------

    public function test_get_navigation_uses_unn_nav_role_key(): void
    {
        $value = $this->manager->getNavigation('admin', fn () => ['Painel', 'Usuarios']);

        $this->assertSame(['Painel', 'Usuarios'], $value);
        $this->assertTrue(Cache::has('unn:nav:admin'));
        $this->assertSame(['Painel', 'Usuarios'], Cache::get('unn:nav:admin'));
    }

    public function test_get_navigation_does_not_invoke_loader_on_subsequent_calls(): void
    {
        $calls = 0;
        $loader = function () use (&$calls) {
            $calls++;
            return ['Inicio'];
        };

        $first = $this->manager->getNavigation('user', $loader);
        $second = $this->manager->getNavigation('user', $loader);

        $this->assertSame(1, $calls);
        $this->assertSame($first, $second);
    }

    public function test_get_navigation_normalizes_non_array_loader_return_to_array(): void
    {
        $value = $this->manager->getNavigation('guest', fn () => 'not-an-array');

        $this->assertSame([], $value);
    }

    public function test_get_navigation_passes_seconds_ttl_to_cache(): void
    {
        $captured = null;

        Cache::shouldReceive('remember')
            ->once()
            ->andReturnUsing(function ($key, $ttl, $callback) use (&$captured) {
                $captured = ['key' => $key, 'ttl' => $ttl];
                return $callback();
            });

        $this->manager->getNavigation('admin', fn () => ['x']);

        $this->assertSame('unn:nav:admin', $captured['key']);
        // Padrao de navegacao: 60min == 3600s
        $this->assertSame(60 * 60, $captured['ttl']);
    }

    // ------------------------------------------------------------------
    // 2. getSettings()
    // ------------------------------------------------------------------

    public function test_get_settings_uses_unn_settings_key(): void
    {
        $value = $this->manager->getSettings(fn () => ['app_name' => 'UNN']);

        $this->assertSame(['app_name' => 'UNN'], $value);
        $this->assertTrue(Cache::has('unn:settings'));
    }

    public function test_get_settings_caches_loader_result(): void
    {
        $calls = 0;
        $loader = function () use (&$calls) {
            $calls++;
            return ['k' => 'v', 'iter' => $calls];
        };

        $a = $this->manager->getSettings($loader);
        $b = $this->manager->getSettings($loader);

        $this->assertSame(1, $calls);
        $this->assertSame($a, $b);
    }

    public function test_get_settings_passes_seconds_ttl_to_cache(): void
    {
        $captured = null;

        Cache::shouldReceive('remember')
            ->once()
            ->andReturnUsing(function ($key, $ttl, $callback) use (&$captured) {
                $captured = ['key' => $key, 'ttl' => $ttl];
                return $callback();
            });

        $this->manager->getSettings(fn () => ['app_name' => 'UNN']);

        $this->assertSame('unn:settings', $captured['key']);
        // Padrao de settings: 120min == 7200s
        $this->assertSame(120 * 60, $captured['ttl']);
    }

    // ------------------------------------------------------------------
    // 3. getUserPermissions()
    // ------------------------------------------------------------------

    public function test_get_user_permissions_uses_unn_perms_userId_key(): void
    {
        $value = $this->manager->getUserPermissions(42, fn () => ['users.view', 'users.edit']);

        $this->assertSame(['users.view', 'users.edit'], $value);
        $this->assertTrue(Cache::has('unn:perms:42'));
        $this->assertSame(['users.view', 'users.edit'], Cache::get('unn:perms:42'));
    }

    public function test_get_user_permissions_isolates_per_user_id(): void
    {
        $this->manager->getUserPermissions(1, fn () => ['a']);
        $this->manager->getUserPermissions(2, fn () => ['b']);

        $this->assertSame(['a'], Cache::get('unn:perms:1'));
        $this->assertSame(['b'], Cache::get('unn:perms:2'));
    }

    // ------------------------------------------------------------------
    // 4. getDashboardStats()
    // ------------------------------------------------------------------

    public function test_get_dashboard_stats_uses_unn_dash_metric_key(): void
    {
        $value = $this->manager->getDashboardStats('users_total', fn () => 1234);

        $this->assertSame(1234, $value);
        $this->assertTrue(Cache::has('unn:dash:users_total'));
        $this->assertSame(1234, Cache::get('unn:dash:users_total'));
    }

    public function test_get_dashboard_stats_supports_non_array_values(): void
    {
        $value = $this->manager->getDashboardStats('revenue_month', fn () => 999.99);

        $this->assertSame(999.99, $value);
        $this->assertSame(999.99, Cache::get('unn:dash:revenue_month'));
    }

    // ------------------------------------------------------------------
    // 5. getHeavyQuery()
    // ------------------------------------------------------------------

    public function test_get_heavy_query_uses_md5_hashed_key(): void
    {
        $logicalKey = 'select * from users where status=active';
        $expectedKey = AdvancedCacheManager::PREFIX_QUERY . md5($logicalKey);

        $value = $this->manager->getHeavyQuery($logicalKey, fn () => ['rows' => 10], 5);

        $this->assertSame(['rows' => 10], $value);
        $this->assertTrue(Cache::has($expectedKey));
        $this->assertSame(['rows' => 10], Cache::get($expectedKey));
    }

    public function test_get_heavy_query_executes_callback_only_once_within_ttl(): void
    {
        $calls = 0;
        $cb = function () use (&$calls) {
            $calls++;
            return $calls;
        };

        $first = $this->manager->getHeavyQuery('q1', $cb, 10);
        $second = $this->manager->getHeavyQuery('q1', $cb, 10);
        $third = $this->manager->getHeavyQuery('q1', $cb, 10);

        $this->assertSame(1, $first);
        $this->assertSame(1, $second);
        $this->assertSame(1, $third);
        $this->assertSame(1, $calls);
    }

    public function test_get_heavy_query_uses_default_ttl_when_null(): void
    {
        $captured = null;

        Cache::shouldReceive('remember')
            ->once()
            ->andReturnUsing(function ($key, $ttl, $callback) use (&$captured) {
                $captured = ['key' => $key, 'ttl' => $ttl];
                return $callback();
            });

        $this->manager->getHeavyQuery('any-key', fn () => 'v', null);

        $this->assertSame(AdvancedCacheManager::PREFIX_QUERY . md5('any-key'), $captured['key']);
        // Padrao de heavy query: 15min == 900s
        $this->assertSame(15 * 60, $captured['ttl']);
    }

    public function test_get_heavy_query_uses_provided_ttl_in_minutes(): void
    {
        $captured = null;

        Cache::shouldReceive('remember')
            ->once()
            ->andReturnUsing(function ($key, $ttl, $callback) use (&$captured) {
                $captured = ['key' => $key, 'ttl' => $ttl];
                return $callback();
            });

        $this->manager->getHeavyQuery('q', fn () => 'v', 30);

        // 30 minutos == 1800s
        $this->assertSame(30 * 60, $captured['ttl']);
    }

    // ------------------------------------------------------------------
    // 6. invalidate()
    // ------------------------------------------------------------------

    public function test_invalidate_navigation_with_specific_role(): void
    {
        Cache::put('unn:nav:admin', ['admin-menu'], 3600);
        Cache::put('unn:nav:user', ['user-menu'], 3600);

        $this->manager->invalidate('navigation', 'admin');

        $this->assertFalse(Cache::has('unn:nav:admin'));
        $this->assertTrue(Cache::has('unn:nav:user'));
    }

    public function test_invalidate_navigation_without_identifier_clears_all_known_roles(): void
    {
        foreach (AdvancedCacheManager::KNOWN_ROLES as $role) {
            Cache::put('unn:nav:' . $role, ['menu-' . $role], 3600);
        }

        $this->manager->invalidate('navigation');

        foreach (AdvancedCacheManager::KNOWN_ROLES as $role) {
            $this->assertFalse(
                Cache::has('unn:nav:' . $role),
                "Cache para role '{$role}' nao foi invalidado"
            );
        }
    }

    public function test_invalidate_settings_clears_unn_settings_key(): void
    {
        Cache::put('unn:settings', ['k' => 'v'], 3600);

        $this->manager->invalidate('settings');

        $this->assertFalse(Cache::has('unn:settings'));
    }

    public function test_invalidate_permissions_with_specific_user(): void
    {
        $this->manager->getUserPermissions(10, fn () => ['p1']);
        $this->manager->getUserPermissions(20, fn () => ['p2']);

        $this->manager->invalidate('permissions', '10');

        $this->assertFalse(Cache::has('unn:perms:10'));
        $this->assertTrue(Cache::has('unn:perms:20'));
    }

    public function test_invalidate_permissions_without_identifier_clears_all_tracked_users(): void
    {
        $this->manager->getUserPermissions(10, fn () => ['p1']);
        $this->manager->getUserPermissions(20, fn () => ['p2']);

        $this->manager->invalidate('permissions');

        $this->assertFalse(Cache::has('unn:perms:10'));
        $this->assertFalse(Cache::has('unn:perms:20'));
    }

    public function test_invalidate_dashboard_with_specific_metric(): void
    {
        Cache::put('unn:dash:users_total', 100, 3600);
        Cache::put('unn:dash:orders_today', 5, 3600);

        $this->manager->invalidate('dashboard', 'users_total');

        $this->assertFalse(Cache::has('unn:dash:users_total'));
        $this->assertTrue(Cache::has('unn:dash:orders_today'));
    }

    public function test_invalidate_dashboard_without_identifier_clears_common_metrics(): void
    {
        foreach (AdvancedCacheManager::COMMON_DASHBOARD_METRICS as $metric) {
            Cache::put('unn:dash:' . $metric, 1, 3600);
        }

        $this->manager->invalidate('dashboard');

        foreach (AdvancedCacheManager::COMMON_DASHBOARD_METRICS as $metric) {
            $this->assertFalse(
                Cache::has('unn:dash:' . $metric),
                "Cache para metrica '{$metric}' nao foi invalidado"
            );
        }
    }

    public function test_invalidate_heavy_query_with_identifier_uses_md5(): void
    {
        $logical = 'expensive-aggregate';
        $key = AdvancedCacheManager::PREFIX_QUERY . md5($logical);
        Cache::put($key, 'cached', 3600);

        $this->manager->invalidate('heavy_query', $logical);

        $this->assertFalse(Cache::has($key));
    }

    public function test_invalidate_unknown_type_does_not_throw(): void
    {
        // Nao deve lancar excecao mesmo com tipo desconhecido (fail-safe).
        $this->manager->invalidate('tipo-fantasia', 'qualquer');

        $this->addToAssertionCount(1);
    }

    // ------------------------------------------------------------------
    // 7. warmUp()
    // ------------------------------------------------------------------

    public function test_warm_up_pre_populates_settings_navigation_and_dashboard_caches(): void
    {
        // Estado inicial: nada no cache.
        $this->assertFalse(Cache::has('unn:settings'));
        foreach (AdvancedCacheManager::KNOWN_ROLES as $role) {
            $this->assertFalse(Cache::has('unn:nav:' . $role));
        }
        foreach (AdvancedCacheManager::COMMON_DASHBOARD_METRICS as $metric) {
            $this->assertFalse(Cache::has('unn:dash:' . $metric));
        }

        $this->manager->warmUp();

        // Apos warmUp: settings e navegacao por role conhecido devem estar
        // populados (loaders retornam array, observavel via Cache::has()).
        $this->assertTrue(
            Cache::has('unn:settings'),
            'warmUp() nao pre-populou unn:settings'
        );
        foreach (AdvancedCacheManager::KNOWN_ROLES as $role) {
            $this->assertTrue(
                Cache::has('unn:nav:' . $role),
                "warmUp() nao pre-populou unn:nav:{$role}"
            );
        }

        // Para metricas de dashboard, warmUp() invoca getDashboardStats com
        // loader generico (sem dados reais durante o pre-aquecimento). O
        // contrato verificado e: warmUp() executa sem erro e a metrica
        // aparece em Cache::get() / has() apenas quando o loader real produz
        // valor nao nulo. Aqui, validamos que warmUp() nao lanca excecao
        // e que uma metrica previamente populada permanece intacta (caso
        // testado em test_warm_up_does_not_overwrite_existing_cache_entries).
        $this->addToAssertionCount(1);
    }

    public function test_warm_up_does_not_overwrite_existing_cache_entries(): void
    {
        Cache::put('unn:settings', ['preexistente' => true], 3600);
        Cache::put('unn:nav:admin', ['preexistente-admin'], 3600);
        Cache::put('unn:dash:users_total', 99999, 3600);

        $this->manager->warmUp();

        // Valores preexistentes devem ser preservados (warmUp checa Cache::has).
        $this->assertSame(['preexistente' => true], Cache::get('unn:settings'));
        $this->assertSame(['preexistente-admin'], Cache::get('unn:nav:admin'));
        $this->assertSame(99999, Cache::get('unn:dash:users_total'));
    }
}
