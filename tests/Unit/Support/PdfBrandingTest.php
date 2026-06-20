<?php

namespace Tests\Unit\Support;

use App\Support\PdfBranding;
use Tests\TestCase;

class PdfBrandingTest extends TestCase
{
    public function test_it_injects_embedded_logo_watermark_into_html(): void
    {
        $html = '<!doctype html><html><head><meta charset="utf-8"></head><body><main>Conteudo</main></body></html>';

        $result = PdfBranding::injectDefaultLogoWatermark($html);

        $this->assertStringContainsString('pdf-brand-watermark', $result);
        $this->assertStringContainsString('pdf-brand-watermark-inner', $result);
        $this->assertMatchesRegularExpression('/data:image\\/(svg\\+xml|png|jpeg);base64,/', $result);
        $this->assertStringContainsString('<div class="pdf-brand-content">', $result);
    }
}
