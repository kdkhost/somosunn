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
 * Sistema UNN - Property test (Property 4) para cache-through com TTL.
 *
 * Spec: .kiro/specs/advanced-security-performance (task 6.3)
 *
 * Property 4: Cache-Through with Configurable TTL
 *   Para qualquer chave logica e TTL T > 0:
 *     (a) a 1a chamada a Cache::remember(key, T, $callback) executa o
 *         callback exatamente uma vez e armazena o valor no cache;
 *     (b) a 2a chamada com TTL ainda nao expirado retorna o valor
 *         cacheado SEM reexecutar o callback;
 *     (c) apos avanco de tempo > T (via Carbon::setTestNow), a proxima
 *         chamada executa o callback novamente (cache miss por expiracao).
 *
 *   Este teste complementa AdvancedCacheTtlTest (que cobre getHeavyQuery
 *   dentro do TTL) ao adicionar a fronteira de expiracao temporal.
 *
 *   AdvancedCacheManager nao expoe um metodo `remember` publico (a API
 *   equivalente e getHeavyQuery, que recebe TTL em MINUTOS); por isso
 *   este teste valida a Property 4 contra Cache::remember diretamente,
 *   conforme orientacao da task 6.3 ("testar via Cache::remember
 *   diretamente como surrogate" caso o servico nao exponha remember).
 *
 * Validates: Requirements 4.5, 4.8, 12.3
 */

namespace Tests\Property;

use Eris\Generators;
use Eris\TestTrait;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class CacheThroughTtlTest extends TestCase
{
    use TestTrait;

    protected function setUp(): void
    {
        parent::setUp();

        // Driver array: zero I/O em disco, isolado entre iteracoes do Eris.
        // O ArrayStore usa Carbon::now() para calcular expiracoes, o que
        // permite simular avanco de tempo via Carbon::setTestNow().
        config()->set('cache.default', 'array');
        Cache::store('array')->flush();
        Cache::flush();

        // Ancora temporal determinista para a fase de "dentro do TTL".
        Carbon::setTestNow(Carbon::create(2026, 1, 1, 12, 0, 0));
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
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
     * Property 4 (cache-through com TTL):
     *   - 1a chamada de Cache::remember executa o callback (cache miss).
     *   - 2a chamada dentro do TTL retorna o valor cacheado sem executar
     *     o callback novamente (cache hit).
     *   - Apos avancar o tempo alem do TTL, a proxima chamada executa o
     *     callback novamente (cache miss por expiracao).
     *
     * Validates: Requirements 4.5, 4.8, 12.3
     */
    public function test_remember_caches_until_ttl_and_refreshes_after_expiration(): void
    {
        $this
            ->forAll(
                // TTL em segundos (limites razoaveis para evitar overflow do
                // calculo de timestamp do ArrayStore).
                Generators::choose(60, 3600),
                // Sufixo aleatorio para garantir chave unica por iteracao.
                // Usamos string() (em vez de regex()) para evitar dependencia
                // opcional icomefromthenet/reverse-regex; a chave final passa
                // por md5() e portanto e segura mesmo com bytes arbitrarios.
                Generators::string()
            )
            ->then(function (int $ttl, string $suffix): void {
                // Reset por iteracao: cache limpo e ancora temporal fixa.
                Cache::flush();
                $baseline = Carbon::create(2026, 1, 1, 12, 0, 0);
                Carbon::setTestNow($baseline);

                $key = 'pbt:cache_through:' . md5($suffix);

                $callCount = 0;
                $callback = function () use (&$callCount, $suffix) {
                    $callCount++;
                    return ['suffix' => $suffix, 'invocation' => $callCount];
                };

                // (a) Primeira chamada: cache miss -> callback executa 1x.
                $first = Cache::remember($key, $ttl, $callback);
                $this->assertSame(
                    1,
                    $callCount,
                    "Property 4 violada: callback deveria ter sido invocado 1x na 1a chamada (key='{$key}', ttl={$ttl}s)"
                );
                $this->assertSame(
                    ['suffix' => $suffix, 'invocation' => 1],
                    $first,
                    "Property 4 violada: 1a chamada nao retornou o resultado do callback"
                );

                // (b) Segunda chamada dentro do TTL: cache hit -> callback NAO executa.
                //     Avancamos um pouco no tempo, mas ainda dentro do TTL.
                Carbon::setTestNow($baseline->copy()->addSeconds(intdiv($ttl, 2)));

                $second = Cache::remember($key, $ttl, $callback);
                $this->assertSame(
                    1,
                    $callCount,
                    "Property 4 violada: callback foi reinvocado dentro do TTL (key='{$key}', ttl={$ttl}s, callCount={$callCount})"
                );
                $this->assertSame(
                    $first,
                    $second,
                    "Property 4 violada: 2a chamada retornou valor diferente do cacheado dentro do TTL"
                );

                // (c) Apos expiracao do TTL: cache miss -> callback executa novamente.
                //     Avancamos para depois de TTL + 1s para cruzar a fronteira.
                Carbon::setTestNow($baseline->copy()->addSeconds($ttl + 1));

                $third = Cache::remember($key, $ttl, $callback);
                $this->assertSame(
                    2,
                    $callCount,
                    "Property 4 violada: callback nao foi reinvocado apos expiracao do TTL (key='{$key}', ttl={$ttl}s, callCount={$callCount})"
                );
                $this->assertSame(
                    ['suffix' => $suffix, 'invocation' => 2],
                    $third,
                    "Property 4 violada: 3a chamada apos expiracao nao retornou o novo resultado do callback"
                );
            });
    }
}
