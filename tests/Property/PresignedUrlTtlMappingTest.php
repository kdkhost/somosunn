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
 * Sistema UNN - Property test (Property 2) para mapeamento extensao -> TTL
 *
 * Spec: .kiro/specs/advanced-security-performance (task 5.2)
 *
 * Property 2: File Extension to TTL Mapping
 *
 *   PresignedUrlService::getExpirationForType(string $ext): int e uma funcao
 *   TOTAL e DETERMINISTICA que mapeia qualquer string de extensao para um
 *   inteiro positivo de minutos:
 *
 *     - Docs   (pdf, doc, docx, xls, xlsx) -> 30  minutos
 *     - Media  (mp4, webm, mp3, wav)       -> 120 minutos
 *     - Outras (jpg, png, qualquer outra)  -> 60  minutos (default)
 *
 *   NOTA SOBRE DIVERGENCIA DESIGN x CODIGO:
 *   O design (e o prompt da task) cita docs adicionais (txt, csv) e medias
 *   adicionais (mov, avi, ogg) que NAO estao no codigo de
 *   PresignedUrlService::DOC_EXTENSIONS / MEDIA_EXTENSIONS. Conforme a
 *   restricao da task ("ajustar os asserts ao codigo real e reportar"),
 *   este teste valida o comportamento real do servico: txt/csv/mov/avi/ogg
 *   caem no default (60 minutos). Reporte arquitetural deve ser feito
 *   separadamente caso esse mapeamento precise ser estendido.
 *
 * Validates: Requirements 3.2, 3.4
 */

namespace Tests\Property;

use App\Contracts\PresignedUrlInterface;
use App\Services\PresignedUrlService;
use Eris\Generator;
use Eris\TestTrait;
use Tests\TestCase;

class PresignedUrlTtlMappingTest extends TestCase
{
    use TestTrait;

    /**
     * TTLs default usados como fallback pelo servico quando a tabela
     * settings esta indisponivel (cenario do ambiente de teste sem DB).
     * Coincidem com as constantes privadas de PresignedUrlService:
     *   - DEFAULT_DOCS_TTL     = 30
     *   - DEFAULT_MEDIA_TTL    = 120
     *   - DEFAULT_FALLBACK_TTL = 60
     */
    private const EXPECTED_DOCS_TTL = 30;
    private const EXPECTED_MEDIA_TTL = 120;
    private const EXPECTED_DEFAULT_TTL = 60;

    private PresignedUrlInterface $service;

    /**
     * Compatibilidade com PHPUnit 10: Eris ainda chama
     * \PHPUnit\Util\Test::parseTestMethodAnnotations() que foi removido na 10.
     * Como nao usamos anotacoes @eris-* nestes testes, retornar um array vazio
     * faz a trait operar com defaults (100 iterations, etc.).
     *
     * @return array<string,array<string,array<int,string>>>
     */
    public function getTestCaseAnnotations(): array
    {
        return ['class' => [], 'method' => []];
    }

    protected function setUp(): void
    {
        parent::setUp();

        // Resolver via container (instrucao da task) - garante que estamos
        // testando o binding registrado no AppServiceProvider, nao apenas
        // uma instancia ad-hoc.
        $this->service = app(PresignedUrlInterface::class);

        // Sanity check: o binding deve apontar para a implementacao concreta
        // que estamos validando. Caso contrario o teste estaria validando
        // outro servico.
        $this->assertInstanceOf(
            PresignedUrlService::class,
            $this->service,
            'Container deve resolver PresignedUrlInterface para PresignedUrlService.'
        );
    }

    /**
     * Property 2.A (Categoria docs):
     *
     * Toda extensao em PresignedUrlService::DOC_EXTENSIONS mapeia para 30 minutos.
     *
     * Validates: Requirements 3.2, 3.4
     */
    public function test_doc_extensions_map_to_thirty_minutes(): void
    {
        $this
            ->forAll(
                Generator\elements(PresignedUrlService::DOC_EXTENSIONS)
            )
            ->then(function (string $extension): void {
                $ttl = $this->service->getExpirationForType($extension);

                $this->assertSame(
                    self::EXPECTED_DOCS_TTL,
                    $ttl,
                    "Extensao de documento '{$extension}' deveria mapear para "
                        . self::EXPECTED_DOCS_TTL . " minutos, obtido: {$ttl}."
                );
            });
    }

    /**
     * Property 2.B (Categoria media):
     *
     * Toda extensao em PresignedUrlService::MEDIA_EXTENSIONS mapeia para 120 minutos.
     *
     * Validates: Requirements 3.2, 3.4
     */
    public function test_media_extensions_map_to_one_hundred_twenty_minutes(): void
    {
        $this
            ->forAll(
                Generator\elements(PresignedUrlService::MEDIA_EXTENSIONS)
            )
            ->then(function (string $extension): void {
                $ttl = $this->service->getExpirationForType($extension);

                $this->assertSame(
                    self::EXPECTED_MEDIA_TTL,
                    $ttl,
                    "Extensao de midia '{$extension}' deveria mapear para "
                        . self::EXPECTED_MEDIA_TTL . " minutos, obtido: {$ttl}."
                );
            });
    }

    /**
     * Property 2.C (Categoria default):
     *
     * Qualquer extensao alfanumerica nao classificada como doc/media mapeia
     * para 60 minutos. Cobre tanto extensoes "comuns" (jpg, png, gif, etc.)
     * quanto strings arbitrarias geradas por Eris.
     *
     * Validates: Requirements 3.2, 3.4
     */
    public function test_unknown_alphanumeric_extensions_map_to_sixty_minutes(): void
    {
        $alphaNumericGen = Generator\map(
            static fn (string $raw): string => (string) preg_replace('/[^a-zA-Z0-9]/', '', $raw),
            Generator\string()
        );

        $unknownExtensionGen = Generator\filter(
            function (string $candidate): bool {
                if ($candidate === '') {
                    return false;
                }
                $normalized = strtolower($candidate);

                return !in_array($normalized, PresignedUrlService::DOC_EXTENSIONS, true)
                    && !in_array($normalized, PresignedUrlService::MEDIA_EXTENSIONS, true);
            },
            $alphaNumericGen
        );

        $this
            ->forAll($unknownExtensionGen)
            ->then(function (string $extension): void {
                $ttl = $this->service->getExpirationForType($extension);

                $this->assertSame(
                    self::EXPECTED_DEFAULT_TTL,
                    $ttl,
                    "Extensao desconhecida '{$extension}' deveria mapear para "
                        . self::EXPECTED_DEFAULT_TTL . " minutos (default), obtido: {$ttl}."
                );
            });
    }

    /**
     * Property 2.D (Mapeamento total):
     *
     * Para QUALQUER string de input (incluindo strings vazias, com espacos,
     * com caracteres unicode, com pontos, etc.), o mapeamento e total: nunca
     * retorna null, nunca lanca, sempre retorna um inteiro estritamente
     * positivo.
     *
     * Validates: Requirements 3.2, 3.4
     */
    public function test_mapping_is_total_for_any_string_input(): void
    {
        $this
            ->forAll(Generator\string())
            ->then(function (string $extension): void {
                $ttl = $this->service->getExpirationForType($extension);

                // Mapeamento total: sempre retorna inteiro (PHP type-checked
                // pela assinatura, mas validamos explicitamente).
                $this->assertIsInt(
                    $ttl,
                    "TTL deve ser inteiro para input " . var_export($extension, true)
                );

                // Mapeamento valido: TTL deve ser estritamente positivo
                // (caso contrario a URL seria gerada ja expirada).
                $this->assertGreaterThan(
                    0,
                    $ttl,
                    "TTL deve ser > 0 para input " . var_export($extension, true)
                        . ", obtido: {$ttl}."
                );
            });
    }

    /**
     * Property 2.E (Determinismo):
     *
     * Duas chamadas consecutivas a getExpirationForType com a mesma entrada
     * produzem o mesmo TTL. Cobre extensoes conhecidas (via elements) e
     * strings arbitrarias (via string), garantindo a propriedade em todo
     * o dominio.
     *
     * Validates: Requirements 3.2, 3.4
     */
    public function test_mapping_is_deterministic_for_repeated_calls(): void
    {
        $knownExtensions = array_merge(
            PresignedUrlService::DOC_EXTENSIONS,
            PresignedUrlService::MEDIA_EXTENSIONS
        );

        $anyExtensionGen = Generator\oneOf(
            Generator\elements($knownExtensions),
            Generator\string()
        );

        $this
            ->forAll($anyExtensionGen)
            ->then(function (string $extension): void {
                $first = $this->service->getExpirationForType($extension);
                $second = $this->service->getExpirationForType($extension);

                $this->assertSame(
                    $first,
                    $second,
                    "Mapeamento nao deterministico para entrada "
                        . var_export($extension, true)
                        . ": primeira chamada={$first}, segunda chamada={$second}."
                );
            });
    }
}
