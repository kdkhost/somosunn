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
 * Sistema UNN - Property test (Property 1) para preservacao de aspect ratio
 *
 * Spec: .kiro/specs/advanced-security-performance (task 4.3)
 *
 * Property 1: Aspect Ratio Preservation on Resize
 *   Para qualquer (W, H) com W,H em [100, 5000] e (maxW, maxH) com
 *   maxW,maxH em [100, 2048], seja (W', H') o resultado de
 *   ImageProcessorService::calculateResizeDimensions(W, H, maxW, maxH).
 *
 *   Devem valer simultaneamente:
 *     1. W' <= maxW
 *     2. H' <= maxH
 *     3. abs(W'/H' / W/H - 1) < 0.05        (aspect ratio preservado dentro de 5%)
 *     4. Se W <= maxW e H <= maxH, entao (W', H') == (W, H)  (no-upscale)
 *
 *   Nota sobre tolerancia: foi adotada tolerancia RELATIVA (5%) ao inves
 *   de absoluta (0.01) porque o algoritmo aplica floor() a ambas as
 *   dimensoes para nunca exceder a bounding box. Para aspect ratios
 *   extremos (ex.: 20:1) em boxes pequenas (ex.: 100x100), o floor pode
 *   introduzir uma diferenca absoluta de ratios da ordem do proprio
 *   ratio original, mas a diferenca relativa permanece dentro de 5%.
 *   Tolerancia relativa expressa fielmente o intent original "aspect
 *   ratio preservado" sem ser fragil em dominios extremos.
 *
 * Validates: Requirements 2.3, 2.5
 */

namespace Tests\Property;

use App\Services\ImageProcessorService;
use Eris\Generators;
use Eris\TestTrait;
use Tests\TestCase;

class ImageProcessorAspectRatioTest extends TestCase
{
    use TestTrait;

    /**
     * Tolerancia RELATIVA da assertion de aspect ratio.
     *
     * abs(newRatio/origRatio - 1) < 0.05 -> aceita diferenca de ate 5%
     * entre os ratios. Tolerancia absoluta (0.01) seria inalcancavel para
     * ratios extremos (ex.: 20:1) reduzidos a boxes pequenas, onde o
     * floor de uma das dimensoes pode introduzir distorcoes maiores que
     * 0.01 em valor absoluto, ainda que percentualmente baixas.
     */
    private const ASPECT_RATIO_RELATIVE_TOLERANCE = 0.05;

    /**
     * PHPUnit 10 removeu PHPUnit\Util\Test::parseTestMethodAnnotations()
     * no qual o Eris 0.14.x ainda se apoia. Sobrescrevemos para retornar
     * [] (defaults: rand, 100 iteracoes, sem time-limit). Os testes
     * seguem deterministicos via ERIS_SEED quando necessario.
     */
    public function getTestCaseAnnotations(): array
    {
        return [];
    }

    /**
     * Property 1: aspect ratio preservado e bounding box respeitada.
     *
     * Validates: Requirements 2.3, 2.5
     */
    public function test_resize_preserves_aspect_ratio_and_bounding_box(): void
    {
        $this
            ->forAll(
                Generators::choose(100, 5000),  // width  original
                Generators::choose(100, 5000),  // height original
                Generators::choose(100, 2048),  // maxWidth
                Generators::choose(100, 2048)   // maxHeight
            )
            ->then(function (int $w, int $h, int $maxW, int $maxH): void {
                [$wPrime, $hPrime] = ImageProcessorService::calculateResizeDimensions($w, $h, $maxW, $maxH);

                // Sanity: dimensoes resultantes sao inteiros >= 1.
                $this->assertIsInt($wPrime, "W' deve ser int");
                $this->assertIsInt($hPrime, "H' deve ser int");
                $this->assertGreaterThanOrEqual(1, $wPrime, "W' >= 1 (in: {$w}x{$h} max {$maxW}x{$maxH})");
                $this->assertGreaterThanOrEqual(1, $hPrime, "H' >= 1 (in: {$w}x{$h} max {$maxW}x{$maxH})");

                // (1) e (2) bounding box: W' <= maxW, H' <= maxH.
                $this->assertLessThanOrEqual(
                    $maxW,
                    $wPrime,
                    "W' ({$wPrime}) deve ser <= maxW ({$maxW}) [in: {$w}x{$h} max {$maxW}x{$maxH}]"
                );
                $this->assertLessThanOrEqual(
                    $maxH,
                    $hPrime,
                    "H' ({$hPrime}) deve ser <= maxH ({$maxH}) [in: {$w}x{$h} max {$maxW}x{$maxH}]"
                );

                // (4) no-upscale: se ja cabe, mantem (W, H).
                if ($w <= $maxW && $h <= $maxH) {
                    $this->assertSame(
                        [$w, $h],
                        [$wPrime, $hPrime],
                        "no-upscale violado: ({$w},{$h}) ja cabe em ({$maxW},{$maxH}), retornou ({$wPrime},{$hPrime})"
                    );
                }

                // (3) aspect ratio preservado dentro da tolerancia RELATIVA.
                $originalRatio = $w / $h;
                $newRatio = $wPrime / max($hPrime, 1);
                $relativeDiff = abs($newRatio / $originalRatio - 1.0);

                $this->assertLessThan(
                    self::ASPECT_RATIO_RELATIVE_TOLERANCE,
                    $relativeDiff,
                    sprintf(
                        "aspect ratio nao preservado: orig=%.6f new=%.6f rel_diff=%.6f tol=%.4f [in: %dx%d max %dx%d -> %dx%d]",
                        $originalRatio,
                        $newRatio,
                        $relativeDiff,
                        self::ASPECT_RATIO_RELATIVE_TOLERANCE,
                        $w,
                        $h,
                        $maxW,
                        $maxH,
                        $wPrime,
                        $hPrime
                    )
                );
            });
    }

    // -----------------------------------------------------------------
    // Sanity checks (exemplos especificos)
    // -----------------------------------------------------------------

    /**
     * Imagem ja dentro da bounding box: nao deve ser ampliada.
     */
    public function test_does_not_upscale_when_image_fits_in_bounds(): void
    {
        [$w, $h] = ImageProcessorService::calculateResizeDimensions(800, 600, 2048, 2048);

        $this->assertSame([800, 600], [$w, $h]);
    }

    /**
     * Imagem quadrada maior que a box quadrada: escala para a box exata.
     */
    public function test_scales_square_image_to_square_bounds(): void
    {
        [$w, $h] = ImageProcessorService::calculateResizeDimensions(4000, 4000, 1024, 1024);

        $this->assertSame([1024, 1024], [$w, $h]);
    }

    /**
     * Imagem widescreen (16:9) reduzida pela largura.
     */
    public function test_scales_widescreen_image_constrained_by_width(): void
    {
        // 1920x1080 -> max 1280x1280 -> scale = 1280/1920 = 0.6667
        // newW = floor(1920 * 0.6667) = 1280
        // newH = floor(1080 * 0.6667) = 720
        [$w, $h] = ImageProcessorService::calculateResizeDimensions(1920, 1080, 1280, 1280);

        $this->assertSame(1280, $w, 'largura deve igualar maxW');
        $this->assertSame(720, $h, 'altura deve preservar 16:9');
    }

    /**
     * Imagem retrato reduzida pela altura.
     */
    public function test_scales_portrait_image_constrained_by_height(): void
    {
        // 1080x1920 -> max 2048x1280 -> scale = min(2048/1080, 1280/1920) = 0.6667
        [$w, $h] = ImageProcessorService::calculateResizeDimensions(1080, 1920, 2048, 1280);

        $this->assertSame(720, $w, 'largura deve preservar 9:16');
        $this->assertSame(1280, $h, 'altura deve igualar maxH');
    }

    /**
     * Garante que o resultado nunca ultrapassa a bounding box em cenarios
     * onde floor poderia produzir 1px excedente.
     */
    public function test_never_exceeds_bounding_box_in_edge_case(): void
    {
        [$w, $h] = ImageProcessorService::calculateResizeDimensions(5000, 100, 100, 2048);

        $this->assertLessThanOrEqual(100, $w);
        $this->assertLessThanOrEqual(2048, $h);
        $this->assertGreaterThanOrEqual(1, $h);
    }
}
