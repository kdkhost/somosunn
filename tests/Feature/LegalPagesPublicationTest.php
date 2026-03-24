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
}
