<?php

namespace App\Support;

use Illuminate\Support\Facades\Cache;

class AdaptiveQueryCache
{
    private const PREFIX = 'unn:adaptive:';

    /**
     * Executa ou recupera um valor do cache adaptativo com proteção anti-stampede
     *
     * @template T
     * @param string $key
     * @param callable(): T $callback
     * @param int|null $customTtl Segundos customizados ou null para auto-ajustar
     * @return T
     */
    public static function remember(string $key, callable $callback, ?int $customTtl = null)
    {
        $cacheKey = self::PREFIX . $key;
        $staleKey = self::PREFIX . 'stale:' . $key;
        $lockKey = self::PREFIX . 'lock:' . $key;

        $policy = TrafficLoadDetector::getAdaptivePolicy();
        $ttl = $customTtl !== null ? max(5, $customTtl) : (int) $policy['query_microcache_ttl'];

        try {
            // Se o dado estiver quente no cache, retorna imediatamente
            if (Cache::has($cacheKey)) {
                return Cache::get($cacheKey);
            }

            // Tenta obter um lock atômico para reconstruir o cache (Anti-Stampede Shield)
            // Se outro processo já estiver recalculando, retorna o dado stale prévio sem bloquear
            $lock = Cache::lock($lockKey, 5);

            if ($lock->get()) {
                try {
                    $freshValue = $callback();

                    // Salva a versão ativa com o TTL adaptativo
                    Cache::put($cacheKey, $freshValue, $ttl);
                    // Salva a versão de contingência (stale) com vida mais longa (1 hora)
                    Cache::put($staleKey, $freshValue, max(3600, $ttl * 10));

                    return $freshValue;
                } finally {
                    optional($lock)->release();
                }
            }

            // Se outro processo está recalculando, entrega o dado stale de contingência
            if (Cache::has($staleKey)) {
                return Cache::get($staleKey);
            }

            // Se for a primeira execução absoluta sem stale prévio, executa diretamente
            $value = $callback();
            Cache::put($cacheKey, $value, $ttl);
            Cache::put($staleKey, $value, max(3600, $ttl * 10));

            return $value;
        } catch (\Throwable $e) {
            // Em caso de falha de cache, executa diretamente sem travar a requisição
            return $callback();
        }
    }

    /**
     * Invalida uma chave do cache adaptativo
     */
    public static function forget(string $key): void
    {
        try {
            Cache::forget(self::PREFIX . $key);
            Cache::forget(self::PREFIX . 'stale:' . $key);
        } catch (\Throwable $e) {
            // Fail-silent
        }
    }
}
