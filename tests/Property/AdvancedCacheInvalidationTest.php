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
 * Sistema UNN - Property test (Property 3) para invalidacao de cache
 *
 * Spec: .kiro/specs/advanced-security-performance (task 6.2)
 *
 * Property 3: Cache Invalidation on Entity Modification
 *   Para qualquer entity_type aceito por AdvancedCacheManager::invalidate()
 *   e qualquer identificador valido:
 *     1) Apos popular o cache na chave correspondente,
 *        Cache::has($key) === true.
 *     2) Apos $manager->invalidate($type, $id),
 *        Cache::has($key) === false.
 *
 * Tipos cobertos (mapeados internamente pelo manager):
 *   - navigation  -> unn:nav:{role}
 *   - settings    -> unn:settings (identificador ignorado)
 *   - permissions -> unn:perms:{userId}
 *   - dashboard   -> unn:dash:{metric}
 *   - heavy_query -> unn:query:{md5(identifier)}
 *
 * Validates: Requirements 4.3, 4.6, 12.5
 */

namespace Tests\Property;

use App\Services\AdvancedCacheManager;
use Eris\Generators;
use Eris\TestTrait;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class AdvancedCacheInvalidationTest extends TestCase
{
    use TestTrait;

    private AdvancedCacheManager $manager;

    protected function setUp(): void
    {
        parent::setUp();

        // Forca o store de array no ambiente de teste e garante isolamento
        // entre iteracoes (sem Redis, sem leak entre rodadas).
        config()->set('cache.default', 'array');
        Cache::store('array')->flush();
        Cache::flush();

        $this->manager = new AdvancedCacheManager();
    }

    /**
     * PHPUnit 10 removeu PHPUnit\Util\Test::parseTestMethodAnnotations() no
     * qual o Eris 0.14.x ainda se apoia. Sobrescrevemos para retornar []
     * (defaults: rand, 100 iteracoes, sem time-limit). Determinismo via
     * ERIS_SEED quando necessario.
     */
    public function getTestCaseAnnotations(): array
    {
        return [];
    }

    /**
     * Property 3: invalidate($type, $id) garante Cache::has($key) === false
     * para a chave correspondente, qualquer que seja o tipo/identificador.
     *
     * Geradores escolhidos:
     *   - tipo: elements() entre os 5 tipos de entidade suportados;
     *   - id : choose(1, 1000) -> int convertido para string (espelha o
     *          contrato de invalidate(string $type, ?string $identifier)).
     *
     * Validates: Requirements 4.3, 4.6, 12.5
     */
    public function test_invalidate_removes_cache_entry_for_any_entity_type(): void
    {
        $this
            ->forAll(
                Generators::elements(['navigation', 'settings', 'permissions', 'dashboard', 'heavy_query']),
                Generators::choose(1, 1000)
            )
            ->then(function (string $type, int $id): void {
                $identifier = (string) $id;

                // Estado limpo a cada iteracao para evitar leak entre rodadas.
                Cache::flush();

                $key = $this->cacheKeyFor($type, $identifier);

                // Pre-popula o cache na chave que o manager invalidaria.
                Cache::put($key, ['payload' => $identifier, 'type' => $type], 3600);

                $this->assertTrue(
                    Cache::has($key),
                    "Pre-condicao falhou: cache nao foi populado para chave '{$key}' (type={$type}, id={$identifier})"
                );

                // Acao sob teste.
                $this->manager->invalidate($type, $identifier);

                // Pos-condicao: chave nao deve mais existir no cache.
                $this->assertFalse(
                    Cache::has($key),
                    "Property 3 violada: invalidate('{$type}', '{$identifier}') nao removeu chave '{$key}'"
                );
            });
    }

    /**
     * Mapeia (type, identifier) para a chave de cache produzida pelo
     * AdvancedCacheManager.
     *
     * Notas:
     *   - 'settings' usa chave fixa (unn:settings); identifier e ignorado.
     *   - 'heavy_query' aplica md5() ao identifier (igual ao manager).
     */
    private function cacheKeyFor(string $type, string $identifier): string
    {
        return match ($type) {
            'navigation'  => AdvancedCacheManager::PREFIX_NAVIGATION . $identifier,
            'settings'    => AdvancedCacheManager::PREFIX_SETTINGS,
            'permissions' => AdvancedCacheManager::PREFIX_PERMISSIONS . $identifier,
            'dashboard'   => AdvancedCacheManager::PREFIX_DASHBOARD . $identifier,
            'heavy_query' => AdvancedCacheManager::PREFIX_QUERY . md5($identifier),
            default       => 'unn:unknown:' . $identifier,
        };
    }
}
