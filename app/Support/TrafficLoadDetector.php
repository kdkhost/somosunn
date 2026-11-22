<?php

namespace App\Support;

use Illuminate\Support\Facades\Cache;

class TrafficLoadDetector
{
    public const LEVEL_NORMAL = 'normal';
    public const LEVEL_ELEVATED = 'elevated';
    public const LEVEL_HIGH = 'high';
    public const LEVEL_SURGE = 'surge';

    private const WINDOW_BLOCK_SECONDS = 10;
    private const WINDOW_BLOCKS_COUNT = 6; // 6 blocos de 10s = 60s
    private const CACHE_PREFIX = 'traffic:win:';
    private const FORCE_MODE_SETTING = 'traffic_scaling_force_mode';

    /**
     * Registra um acesso na janela deslizante atual
     */
    public static function recordHit(): void
    {
        try {
            $block = (int) floor(time() / self::WINDOW_BLOCK_SECONDS);
            $key = self::CACHE_PREFIX . $block;

            // Incrementa contador do bloco de 10s com expiração de 120s
            if (!Cache::has($key)) {
                Cache::put($key, 1, 120);
            } else {
                Cache::increment($key);
            }
        } catch (\Throwable $e) {
            // Fail-silent para não interromper a requisição sob nenhuma hipótese
        }
    }

    /**
     * Calcula o volume de requisições por minuto (RPM) atual
     */
    public static function getCurrentRpm(): int
    {
        try {
            $currentBlock = (int) floor(time() / self::WINDOW_BLOCK_SECONDS);
            $totalHits = 0;

            for ($i = 0; $i < self::WINDOW_BLOCKS_COUNT; $i++) {
                $blockKey = self::CACHE_PREFIX . ($currentBlock - $i);
                $hits = (int) Cache::get($blockKey, 0);
                $totalHits += $hits;
            }

            return $totalHits;
        } catch (\Throwable $e) {
            return 0;
        }
    }

    /**
     * Retorna o nível de carga atual do sistema
     */
    public static function getLoadLevel(): string
    {
        try {
            // Se o admin tiver configurado um modo forçado nas settings
            $forceMode = Cache::remember('traffic:force_mode_active', 10, function () {
                return \App\Models\Setting::get(self::FORCE_MODE_SETTING, 'auto');
            });

            if (in_array($forceMode, [self::LEVEL_NORMAL, self::LEVEL_ELEVATED, self::LEVEL_HIGH, self::LEVEL_SURGE], true)) {
                return $forceMode;
            }

            $rpm = self::getCurrentRpm();

            if ($rpm >= 1000) {
                return self::LEVEL_SURGE;
            }

            if ($rpm >= 300) {
                return self::LEVEL_HIGH;
            }

            if ($rpm >= 80) {
                return self::LEVEL_ELEVATED;
            }

            return self::LEVEL_NORMAL;
        } catch (\Throwable $e) {
            return self::LEVEL_NORMAL;
        }
    }

    /**
     * Retorna as políticas e parâmetros adaptativos para o nível de carga atual
     *
     * @return array<string, mixed>
     */
    public static function getAdaptivePolicy(): array
    {
        $level = self::getLoadLevel();

        return match ($level) {
            self::LEVEL_SURGE => [
                'level' => self::LEVEL_SURGE,
                'label' => 'Extremo (Viral / Surge)',
                'edge_max_age' => 1800,           // 30 min no browser
                'edge_s_maxage' => 3600,          // 1 hora na CDN (Cloudflare)
                'edge_stale_while_revalidate' => 900,  // 15 min servindo stale em revalidação
                'edge_stale_if_error' => 3600,    // 1 hora de tolerância a falhas na borda
                'query_microcache_ttl' => 180,    // 3 min microcache de queries públicas
                'defer_analytics' => true,
                'status_color' => 'red',
            ],
            self::LEVEL_HIGH => [
                'level' => self::LEVEL_HIGH,
                'label' => 'Alto Impacto (Pico de Acessos)',
                'edge_max_age' => 900,            // 15 min no browser
                'edge_s_maxage' => 1800,          // 30 min na CDN
                'edge_stale_while_revalidate' => 300,  // 5 min stale revalidation
                'edge_stale_if_error' => 1800,    // 30 min tolerância a falhas
                'query_microcache_ttl' => 90,     // 1.5 min microcache de queries públicas
                'defer_analytics' => true,
                'status_color' => 'amber',
            ],
            self::LEVEL_ELEVATED => [
                'level' => self::LEVEL_ELEVATED,
                'label' => 'Elevado (Tráfego Aquecido)',
                'edge_max_age' => 450,            // 7.5 min no browser
                'edge_s_maxage' => 900,           // 15 min na CDN
                'edge_stale_while_revalidate' => 120,  // 2 min stale revalidation
                'edge_stale_if_error' => 600,     // 10 min tolerância a falhas
                'query_microcache_ttl' => 45,     // 45s microcache
                'defer_analytics' => false,
                'status_color' => 'blue',
            ],
            default => [
                'level' => self::LEVEL_NORMAL,
                'label' => 'Normal (Operação Regular)',
                'edge_max_age' => 300,            // 5 min no browser
                'edge_s_maxage' => 600,           // 10 min na CDN
                'edge_stale_while_revalidate' => 60,   // 1 min stale revalidation
                'edge_stale_if_error' => 300,     // 5 min tolerância a falhas
                'query_microcache_ttl' => 15,     // 15s microcache
                'defer_analytics' => false,
                'status_color' => 'emerald',
            ],
        };
    }
}
