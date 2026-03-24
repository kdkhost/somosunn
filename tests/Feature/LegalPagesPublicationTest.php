<?php

namespace Tests\Feature;

use App\Models\Page;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LegalPagesPublicationTest extends TestCase
{
    use RefreshDatabase;

    public function test_legal_pages_are_published_to_database_with_cms_fields(): void
    {
        foreach (['termos-de-uso', 'politica-de-privacidade', 'consentimento-lgpd'] as $slug) {
            $page = Page::findBySlug($slug);

            $this->assertNotNull($page, "Pagina legal nao encontrada: {$slug}");
            $this->assertIsArray($page->data);
            $this->assertNotSame('', trim((string) ($page->get('hero_title') ?? '')));
            $this->assertNotSame('', trim((string) ($page->get('hero_subtitle') ?? '')));
            $this->assertStringContainsString('<p>', (string) ($page->get('body_content') ?? ''));
        }
    }

    public function test_admin_legal_page_partial_uses_body_content_field(): void
    {
        $page = new Page([
            'slug' => 'consentimento-lgpd',
            'title' => 'Consentimento LGPD',
            'data' => [],
        ]);

        $html = view('admin.pages.partials.institucional', [
            'page' => $page,
            'data' => [
                'hero_title' => 'Consentimento LGPD',
                'hero_subtitle' => 'Subtitulo de teste',
                'body_content' => '<p>Conteudo legal</p>',
            ],
        ])->render();

        $this->assertStringContainsString('name="hero_subtitle"', $html);
        $this->assertStringContainsString('name="body_content"', $html);
        $this->assertStringNotContainsString('name="content"', $html);
        $this->assertStringContainsString('Subtitulo de teste', $html);
        $this->assertStringContainsString('&lt;p&gt;Conteudo legal&lt;/p&gt;', $html);
    }
}
