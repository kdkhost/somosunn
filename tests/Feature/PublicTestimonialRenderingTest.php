<?php

namespace Tests\Feature;

use App\Models\Testimonial;
use Illuminate\Support\Facades\Blade;
use Tests\TestCase;

class PublicTestimonialRenderingTest extends TestCase
{
    public function test_member_testimonial_uses_model_content_on_public_site(): void
    {
        $testimonial = new Testimonial([
            'author_name' => 'Maria da Silva',
            'author_title' => 'Empreendedora',
            'content' => 'O networking abriu novas oportunidades para minha empresa.',
            'rating' => 5,
        ]);

        $html = $this->renderTestimonial($testimonial);

        $this->assertStringContainsString('Maria da Silva', $html);
        $this->assertStringContainsString('Empreendedora', $html);
        $this->assertStringContainsString('O networking abriu novas oportunidades para minha empresa.', $html);
    }

    public function test_cms_testimonial_keeps_legacy_text_format(): void
    {
        $html = $this->renderTestimonial([
            'name' => 'Ana Souza',
            'role' => 'Associada',
            'text' => 'Depoimento configurado pelo CMS.',
            'rating' => 4,
        ]);

        $this->assertStringContainsString('Ana Souza', $html);
        $this->assertStringContainsString('Associada', $html);
        $this->assertStringContainsString('Depoimento configurado pelo CMS.', $html);
    }

    private function renderTestimonial(mixed $testimonial): string
    {
        return Blade::render(<<<'BLADE'
            @php
                $name = data_get($testimonial, 'display_name')
                    ?? data_get($testimonial, 'author_name')
                    ?? data_get($testimonial, 'name')
                    ?? 'Anônimo';
                $role = data_get($testimonial, 'author_title')
                    ?? data_get($testimonial, 'role')
                    ?? '';
                $text = data_get($testimonial, 'content')
                    ?? data_get($testimonial, 'text')
                    ?? '';
            @endphp
            <article><h2>{{ $name }}</h2><span>{{ $role }}</span><p>{{ $text }}</p></article>
        BLADE, compact('testimonial'));
    }
}
