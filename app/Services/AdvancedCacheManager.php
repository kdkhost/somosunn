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
 */

namespace App\Services;

use App\Contracts\AdvancedCacheInterface;
use App\Models\Setting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * AdvancedCacheManager.
 *
 * Implementa cache de alta granularidade (navegação, settings, permissões,
 * métricas de dashboard e queries pesadas) usando o driver de arquivo padrão
 * do Laravel. TTLs (em minutos) são configuráveis via tabela `settings`.
 *
 * Padrão de chaves:
 *   - unn:nav:{role}
 *   - unn:settings
 *   - unn:perms:{userId}
 *   - unn:dash:{metric}
 *   - unn:query:{md5(key)}
 *
 * Compatível com hospedagem compartilhada (cPanel/LiteSpeed) — não depende
 * de Redis.
 */
class AdvancedCacheManager implements AdvancedCacheInterface
{
    public const PREFIX_NAVIGATION = 'unn:nav:';
    public const PREFIX_SETTINGS = 'unn:settings';
    public const PREFIX_PERMISSIONS = 'unn:perms:';
    public const PREFIX_DASHBOARD = 'unn:dash:';
    public const PREFIX_QUERY = 'unn:query:';

    /** TTL padrão em minutos (fallback quando settings indisponíveis). */
    public const DEFAULT_TTL_NAVIGATION = 60;
    public const DEFAULT_TTL_SETTINGS = 120;
    public const DEFAULT_TTL_PERMISSIONS = 30;
    public const DEFAULT_TTL_DASHBOARD = 5;
    public const DEFAULT_TTL_HEAVY_QUERY = 15;

    /**
     * Roles conhecidos usados para invalidação em massa de navegação e
     * para pré-aquecimento (warmUp).
     */
    public const KNOWN_ROLES = ['superadmin', 'admin', 'moderator', 'user', 'guest'];

    /**
     * Métricas comuns pré-aquecidas pelo warmUp.
     */
    public const COMMON_DASHBOARD_METRICS = [
        'users_total',
        'orders_today',
        'revenue_month',
        'active_users',
    ];

    /**
     * Cache de chaves de permissões já registradas (para invalidação em massa
     * sem flush global). Persistido em uma chave índice no próprio cache.
     */
    private const PERMISSIONS_INDEX_KEY = 'unn:perms:_index';

    /** {@inheritdoc} */
    public function getNavigation(string $role, callable $loader): array
    {
        $key = self::PREFIX_NAVIGATION . $role;
        $ttl = $this->ttlForNavigation();

        return Cache::remember($key, $this->minutesToSeconds($ttl), function () use ($loader) {
            $value = $loader();
            return is_array($value) ? $value : [];
        });
    }

    /** {@inheritdoc} */
    public function getSettings(callable $loader): array
    {
        $ttl = $this->ttlForSettings();

        return Cache::remember(self::PREFIX_SETTINGS, $this->minutesToSeconds($ttl), function () use ($loader) {
            $value = $loader();
            return is_array($value) ? $value : [];
        });
    }

    /** {@inheritdoc} */
    public function getUserPermissions(int $userId, callable $loader): array
    {
        $key = self::PREFIX_PERMISSIONS . $userId;
        $ttl = $this->ttlForPermissions();

        $this->trackPermissionsKey($userId);

        return Cache::remember($key, $this->minutesToSeconds($ttl), function () use ($loader) {
            $value = $loader();
            return is_array($value) ? $value : [];
        });
    }

    /** {@inheritdoc} */
    public function getDashboardStats(string $metric, callable $loader): mixed
    {
        $key = self::PREFIX_DASHBOARD . $metric;
        $ttl = $this->ttlForDashboard();

        return Cache::remember($key, $this->minutesToSeconds($ttl), $loader);
    }

    /** {@inheritdoc} */
    public function getHeavyQuery(string $key, callable $callback, ?int $ttl = null): mixed
    {
        $cacheKey = self::PREFIX_QUERY . md5($key);
        $minutes = $ttl !== null ? max(1, (int) $ttl) : $this->ttlForHeavyQuery();

        return Cache::remember($cacheKey, $this->minutesToSeconds($minutes), $callback);
    }

    /** {@inheritdoc} */
    public function invalidate(string $type, ?string $identifier = null): void
    {
        try {
            switch ($type) {
                case 'navigation':
                    if ($identifier !== null && $identifier !== '') {
                        Cache::forget(self::PREFIX_NAVIGATION . $identifier);
                    } else {
                        foreach (self::KNOWN_ROLES as $role) {
                            Cache::forget(self::PREFIX_NAVIGATION . $role);
                        }
                    }
                    break;

                case 'settings':
                    Cache::forget(self::PREFIX_SETTINGS);
                    break;

                case 'permissions':
                    if ($identifier !== null && $identifier !== '') {
                        Cache::forget(self::PREFIX_PERMISSIONS . $identifier);
                        $this->untrackPermissionsKey((int) $identifier);
                    } else {
                        $this->flushPermissions();
                    }
                    break;

                case 'dashboard':
                    if ($identifier !== null && $identifier !== '') {
                        Cache::forget(self::PREFIX_DASHBOARD . $identifier);
                    } else {
                        foreach (self::COMMON_DASHBOARD_METRICS as $metric) {
                            Cache::forget(self::PREFIX_DASHBOARD . $metric);
                        }
                    }
                    break;

                case 'heavy_query':
                    if ($identifier !== null && $identifier !== '') {
                        Cache::forget(self::PREFIX_QUERY . md5($identifier));
                    }
                    break;

                case 'all':
                    Cache::flush();
                    break;

                default:
                    Log::warning('AdvancedCacheManager: tipo de invalidação desconhecido', [
                        'type' => $type,
                        'identifier' => $identifier,
                    ]);
            }
        } catch (Throwable $e) {
            Log::warning('AdvancedCacheManager: falha ao invalidar cache: ' . $e->getMessage(), [
                'type' => $type,
                'identifier' => $identifier,
            ]);
        }
    }

    /** {@inheritdoc} */
    public function warmUp(): void
    {
        try {
            // Settings
            if (! Cache::has(self::PREFIX_SETTINGS)) {
                $this->getSettings(function () {
                    try {
                        return Setting::query()->pluck('value', 'key')->toArray();
                    } catch (Throwable $e) {
                        return [];
                    }
                });
            }

            // Navegação por role conhecido
            foreach (self::KNOWN_ROLES as $role) {
                $key = self::PREFIX_NAVIGATION . $role;
                if (! Cache::has($key)) {
                    $this->getNavigation($role, fn() => []);
                }
            }

            // Métricas de dashboard comuns
            foreach (self::COMMON_DASHBOARD_METRICS as $metric) {
                $key = self::PREFIX_DASHBOARD . $metric;
                if (! Cache::has($key)) {
                    $this->getDashboardStats($metric, fn() => null);
                }
            }
        } catch (Throwable $e) {
            Log::warning('AdvancedCacheManager: falha em warmUp(): ' . $e->getMessage());
        }
    }

    // ------------------------------------------------------------------
    // Helpers internos
    // ------------------------------------------------------------------

    /**
     * Converte minutos em segundos (Laravel Cache::remember espera segundos
     * a partir do Laravel 6+).
     */
    private function minutesToSeconds(int $minutes): int
    {
        return max(1, $minutes) * 60;
    }

    private function ttlForNavigation(): int
    {
        return $this->settingInt('cache_ttl_navigation', self::DEFAULT_TTL_NAVIGATION);
    }

    private function ttlForSettings(): int
    {
        return $this->settingInt('cache_ttl_settings', self::DEFAULT_TTL_SETTINGS);
    }

    private function ttlForPermissions(): int
    {
        return $this->settingInt('cache_ttl_permissions', self::DEFAULT_TTL_PERMISSIONS);
    }

    private function ttlForDashboard(): int
    {
        return $this->settingInt('cache_ttl_dashboard', self::DEFAULT_TTL_DASHBOARD);
    }

    private function ttlForHeavyQuery(): int
    {
        return $this->settingInt('cache_ttl_heavy_query', self::DEFAULT_TTL_HEAVY_QUERY);
    }

    private function settingInt(string $key, int $default): int
    {
        try {
            $raw = Setting::get($key, $default);
            $value = is_numeric($raw) ? (int) $raw : $default;
            return $value > 0 ? $value : $default;
        } catch (Throwable $e) {
            return $default;
        }
    }

    /**
     * Registra a chave de permissões no índice para permitir flush em massa
     * sem precisar limpar todo o cache.
     */
    private function trackPermissionsKey(int $userId): void
    {
        try {
            $index = Cache::get(self::PERMISSIONS_INDEX_KEY, []);
            if (! is_array($index)) {
                $index = [];
            }
            if (! in_array($userId, $index, true)) {
                $index[] = $userId;
                Cache::forever(self::PERMISSIONS_INDEX_KEY, $index);
            }
        } catch (Throwable $e) {
            // silencioso — índice é otimização opcional
        }
    }

    private function untrackPermissionsKey(int $userId): void
    {
        try {
            $index = Cache::get(self::PERMISSIONS_INDEX_KEY, []);
            if (! is_array($index)) {
                return;
            }
            $index = array_values(array_filter($index, fn($id) => (int) $id !== $userId));
            Cache::forever(self::PERMISSIONS_INDEX_KEY, $index);
        } catch (Throwable $e) {
            // silencioso
        }
    }

    private function flushPermissions(): void
    {
        try {
            $index = Cache::get(self::PERMISSIONS_INDEX_KEY, []);
            if (is_array($index)) {
                foreach ($index as $userId) {
                    Cache::forget(self::PREFIX_PERMISSIONS . $userId);
                }
            }
            Cache::forget(self::PERMISSIONS_INDEX_KEY);
        } catch (Throwable $e) {
            Log::warning('AdvancedCacheManager: falha em flushPermissions(): ' . $e->getMessage());
        }
    }
}
