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
 *   Para qualquer (W, H, maxW, maxH) com 100 <= W <= 5000, 100 <= H <= 5000,
 *   100 <= maxW <= 2048, 100 <= maxH <= 2048, o metodo
 *   ImageProcessorService::calculateResizeDimensions(W, H, maxW, maxH) deve
 *   retornar (W', H') tal que:
 *     1. abs(W'/H' - W/H) < 0.01    (preservacao do aspect ratio com tolerancia 1%)
 *     2. W' <= maxW                  (cabe na bounding box)
 *     3. H' <= maxH                  (cabe na bounding box)
 *     4. Se W <= maxW e H <= maxH, retorna exatamente [W, H] (nao escala para cima)
 *
 *   Observacao sobre a assinatura: o metodo real e
 *     public static function calculateResizeDimensions(int, int, int, int): array{0:int,1:int}
 *   ou seja, retorna um array POSICIONAL [W', H'], nao associativo.
 *   O teste preserva esse contrato.
 *
 * Validates: Requirements 2.3, 2.5
 */

namespace Tests\Property\Services;

use App\Services\ImageProcessorService;
use Eris\Generator;
use Eris\TestTrait;
use Tests\TestCase;

class ImageProcessorAspectRatioTest extends TestCase
{
    use TestTrait;

    /**
     * Compatibilidade com PHPUnit 10: a Eris 0.14 ainda chama
     * \PHPUnit\Util\Test::parseTestMethodAnnotations(), que foi removida na
     * PHPUnit 10. Como nao usamos anotacoes @eris-* aqui, retornar um array
     * vazio faz a trait operar com defaults (100 iterations).
     *
     * @return array<string,array<string,array<int,string>>>
     */
    public function getTestCaseAnnotations(): array
    {
        return ['class' => [], 'method' => []];
    }

    /**
     * Property 1: Aspect Ratio Preservation on Resize.
     *
     * Validates: Requirements 2.3, 2.5
     */
    public function testAspectRatioPreservedOnResize(): void
    {
        $this->forAll(
            Generator\choose(100, 5000),  // W: largura original
            Generator\choose(100, 5000),  // H: altura original
            Generator\choose(100, 2048),  // maxW: largura maxima da bounding box
            Generator\choose(100, 2048)   // maxH: altura maxima da bounding box
        )->then(function (int $w, int $h, int $maxW, int $maxH): void {
            $result = ImageProcessorService::calculateResizeDimensions($w, $h, $maxW, $maxH);

            // O metodo retorna array posicional [W', H'].
            $this->assertIsArray(
                $result,
                "calculateResizeDimensions deve retornar array para inputs ({$w}, {$h}, {$maxW}, {$maxH})"
            );
            $this->assertCount(
                2,
                $result,
                "Resultado deve conter exatamente [W', H'] para inputs ({$w}, {$h}, {$maxW}, {$maxH})"
            );

            [$newW, $newH] = $result;

            $this->assertIsInt($newW);
            $this->assertIsInt($newH);

            // Sanidade: dimensoes nao degeneradas (necessario para os ratios abaixo).
            $this->assertGreaterThan(
                0,
                $newW,
                "newW deve ser > 0 para inputs ({$w}, {$h}, {$maxW}, {$maxH})"
            );
            $this->assertGreaterThan(
                0,
                $newH,
                "newH deve ser > 0 para inputs ({$w}, {$h}, {$maxW}, {$maxH})"
            );

            // Condicao 2: W' <= maxW.
            $this->assertLessThanOrEqual(
                $maxW,
                $newW,
                "newW ({$newW}) deve ser <= maxW ({$maxW}) para inputs ({$w}, {$h}, {$maxW}, {$maxH})"
            );

            // Condicao 3: H' <= maxH.
            $this->assertLessThanOrEqual(
                $maxH,
                $newH,
                "newH ({$newH}) deve ser <= maxH ({$maxH}) para inputs ({$w}, {$h}, {$maxW}, {$maxH})"
            );

            // Condicao 4: nao escala para cima quando ja cabe na bounding box.
            if ($w <= $maxW && $h <= $maxH) {
                $this->assertSame(
                    $w,
                    $newW,
                    "Imagem que ja cabe na box nao deve ser escalada: W={$w} esperado, recebido {$newW}"
                );
                $this->assertSame(
                    $h,
                    $newH,
                    "Imagem que ja cabe na box nao deve ser escalada: H={$h} esperado, recebido {$newH}"
                );
            }

            // Condicao 1: aspect ratio preservado dentro de tolerancia 1%.
            $originalRatio = $w / $h;
            $newRatio = $newW / $newH;
            $diff = abs($originalRatio - $newRatio);

            $this->assertLessThan(
                0.01,
                $diff,
                "Aspect ratio deve ser preservado com tolerancia < 0.01. "
                    . "Original=({$w}/{$h})={$originalRatio}, "
                    . "Novo=({$newW}/{$newH})={$newRatio}, "
                    . "diff={$diff}, "
                    . "inputs=({$w}, {$h}, {$maxW}, {$maxH})"
            );
        });
    }
}
