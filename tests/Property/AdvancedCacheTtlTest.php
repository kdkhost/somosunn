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
 * Sistema UNN - Property test (Property 4) para cache-through com TTL
 *
 * Spec: .kiro/specs/advanced-security-performance (task 6.3)
 *
 * Property 4: Cache-Through with Configurable TTL
 *   A primeira chamada com $callback executa o callback e armazena o
 *   resultado no cache. A segunda chamada, dentro do TTL, retorna o
 *   valor cacheado sem reexecutar o callback. Verificado via contador.
 *
 *   AdvancedCacheManager::getHeavyQuery() recebe $ttl em MINUTOS, por
 *   isso geramos TTLs entre 1 e 1440 minutos (equivalente a 60s..86400s
 *   em wall-clock). Os testes nao avancam o relogio, entao todas as
 *   chamadas ocorrem dentro do TTL configurado.
 *
 * Validates: Requirements 4.5, 4.8, 12.3
 */

namespace Tests\Property;

use App\Services\AdvancedCacheManager;
use Eris\Generators;
use Eris\TestTrait;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class AdvancedCacheTtlTest extends TestCase
{
    use TestTrait;

    private AdvancedCacheManager $manager;

    protected function setUp(): void
    {
        parent::setUp();

        config()->set('cache.default', 'array');
        Cache::store('array')->flush();
        Cache::flush();

        $this->manager = new AdvancedCacheManager();
    }

    /**
     * PHPUnit 10 removeu PHPUnit\Util\Test::parseTestMethodAnnotations() no
     * qual o Eris 0.14.x ainda se apoia. Sobrescrevemos para retornar []
     * (defaults: rand, 100 iteracoes, sem time-limit).
     */
    public function getTestCaseAnnotations(): array
    {
        return [];
    }

    /**
     * Property 4: a 1a chamada de getHeavyQuery() invoca o callback;
     * a 2a chamada (dentro do TTL) retorna o valor cacheado SEM invocar
     * o callback novamente.
     *
     * Validates: Requirements 4.5, 4.8, 12.3
     */
    public function test_heavy_query_executes_callback_only_on_first_call_within_ttl(): void
    {
        $this
            ->forAll(
                // Chave logica da query (sera hasheada em md5 internamente).
                Generators::regex('[a-zA-Z0-9_:-]{1,32}'),
                // TTL em minutos: 1..1440 == 60s..86400s em wall-clock.
                Generators::choose(1, 1440),
                // Payload arbitrario para verificar identidade do retorno.
                Generators::choose(0, PHP_INT_MAX >> 4)
            )
            ->then(function (string $queryKey, int $ttlMinutes, int $payload): void {
                // Estado limpo a cada iteracao.
                Cache::flush();

                $callCount = 0;
                $callback = function () use (&$callCount, $payload) {
                    $callCount++;
                    return ['payload' => $payload, 'invoked' => $callCount];
                };

                // 1a chamada: cache miss -> callback executa.
                $first = $this->manager->getHeavyQuery($queryKey, $callback, $ttlMinutes);

                $this->assertSame(
                    1,
                    $callCount,
                    "Property 4 violada: callback deveria ter sido invocado 1x na 1a chamada (queryKey='{$queryKey}', ttl={$ttlMinutes}m)"
                );
                $this->assertSame(
                    ['payload' => $payload, 'invoked' => 1],
                    $first,
                    "Property 4 violada: 1a chamada nao retornou o resultado do callback"
                );

                // 2a chamada: cache hit -> callback NAO deve ser invocado.
                $second = $this->manager->getHeavyQuery($queryKey, $callback, $ttlMinutes);

                $this->assertSame(
                    1,
                    $callCount,
                    "Property 4 violada: callback foi reinvocado dentro do TTL (queryKey='{$queryKey}', ttl={$ttlMinutes}m, callCount={$callCount})"
                );
                $this->assertSame(
                    $first,
                    $second,
                    "Property 4 violada: 2a chamada retornou valor diferente do cacheado"
                );

                // 3a chamada (idempotencia adicional): ainda cache hit.
                $third = $this->manager->getHeavyQuery($queryKey, $callback, $ttlMinutes);
                $this->assertSame(1, $callCount);
                $this->assertSame($first, $third);
            });
    }
}
