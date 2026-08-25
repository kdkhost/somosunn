<?php

namespace Tests\Unit;

use App\Console\Commands\ImportManchetePdfs;
use Illuminate\Support\Facades\Http;
use ReflectionMethod;
use Tests\TestCase;

class ImportManchetePdfsTest extends TestCase
{
    public function test_catalog_adds_new_official_magazine(): void
    {
        Http::fake([
            'https://revistamanchete.com.br/revistas/' => Http::response(<<<'HTML'
                <article class="revista-item">
                    <a href="https://revistamanchete.com.br/revistas/revista-manchete-edicao-9-julho-2026/">
                        <img data-src="https://revistamanchete.com.br/wp-content/uploads/capa-9.jpg">
                        <span class="revista-edicao">9ª Edição</span>
                    </a>
                </article>
                HTML),
        ]);

        $sources = $this->invoke(new ImportManchetePdfs(), 'catalogSources');
        $source = collect($sources)->firstWhere('url', 'https://revistamanchete.com.br/revistas/revista-manchete-edicao-9-julho-2026/');

        $this->assertNotNull($source);
        $this->assertSame('Revista Manchete - 9ª Edição', $source['title']);
        $this->assertSame('2026-07-01', $source['published']);
    }

    public function test_only_https_official_domains_are_accepted(): void
    {
        $command = new ImportManchetePdfs();

        $this->assertTrue($this->invoke($command, 'isOfficialUrl', ['https://revistamanchete.com.br/revistas/edicao/']));
        $this->assertFalse($this->invoke($command, 'isOfficialUrl', ['https://revistamanchete.com.br.example.com/file.pdf']));
        $this->assertFalse($this->invoke($command, 'isOfficialUrl', ['http://revistamanchete.com.br/file.pdf']));
    }

    public function test_judiciary_july_is_distinct_from_previous_special_edition(): void
    {
        $source = $this->invoke(new ImportManchetePdfs(), 'metadataFromCatalog', [
            'https://revistamanchete.com.br/revistas/revista-manchete-judiciario-julho/',
            'Manchete Judiciário',
        ]);

        $this->assertSame('Revista Manchete Judiciário - Julho 2026', $source['title']);
        $this->assertSame('2026-07-01', $source['published']);
    }

    private function invoke(object $object, string $method, array $arguments = []): mixed
    {
        $reflection = new ReflectionMethod($object, $method);
        $reflection->setAccessible(true);

        return $reflection->invokeArgs($object, $arguments);
    }
}
