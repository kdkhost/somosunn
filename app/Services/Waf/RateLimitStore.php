<?php

namespace App\Services\Waf;

use Illuminate\Support\Facades\Cache;

/**
 * Token-bucket-like rate limiter, usando o cache padrao do Laravel.
 *
 * Chave: waf:rl:{scope}:{identity}
 *
 * Invariante (Property 11):
 *   para sequencia de hits dentro da janela w, o numero de allowed=true
 *   e <= limit. Apos w expirar, o contador zera.
 *
 * Spec: .kiro/specs/waf-e-auditoria-seguranca
 * Requisitos: 7.1, 7.2, 7.3, 11.1, 11.2, 22.2
 */
final class RateLimitStore
{
    public function hit(string $key, int $limit, int $windowSeconds): RateLimitResult
    {
        if ($limit <= 0 || $windowSeconds <= 0) {
            return new RateLimitResult(true, PHP_INT_MAX, 0);
        }

        $cacheKey = 'waf:rl:' . $key;

        try {
            $current = (int) Cache::get($cacheKey, 0);

            if ($current === 0) {
                Cache::put($cacheKey, 1, $windowSeconds);
                return new RateLimitResult(true, max(0, $limit - 1), 0);
            }

            if ($current >= $limit) {
                // Limite atingido
                return new RateLimitResult(false, 0, $windowSeconds);
            }

            // Incrementa sem re-setar TTL
            Cache::increment($cacheKey);

            return new RateLimitResult(true, max(0, $limit - $current - 1), 0);
        } catch (\Throwable $e) {
            // Se o cache falhar, permite (fail-open em nivel de rate limit)
            return new RateLimitResult(true, PHP_INT_MAX, 0);
        }
    }

    public function reset(string $key): void
    {
        try {
            Cache::forget('waf:rl:' . $key);
        } catch (\Throwable $e) {
            // ignora
        }
    }
}
