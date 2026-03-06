<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Blade;
use Tests\TestCase;

class PublicCardExcerptSanitizationTest extends TestCase
{
    public function test_home_cards_strip_html_tags_from_public_excerpts(): void
    {
        $eventExcerpt = Blade::render(
            '{{ Str::limit(strip_tags((string) ($event->description ?? "")), 100) }}',
            [
                'event' => (object) [
                    'description' => '<p>Evento <strong>gratuito</strong> para networking.</p>',
                ],
            ]
        );

        $mentorshipExcerpt = Blade::render(
            '{{ Str::limit(strip_tags((string) ($mentorship->description ?? "")), 100) }}',
            [
                'mentorship' => (object) [
                    'description' => '<p>Mentoria <strong>premium</strong> com prática real.</p>',
                ],
            ]
        );

        $this->assertStringContainsString('Evento gratuito para networking.', $eventExcerpt);
        $this->assertStringContainsString('Mentoria premium com prática real.', $mentorshipExcerpt);
        $this->assertStringNotContainsString('<p>', $eventExcerpt);
        $this->assertStringNotContainsString('<p>', $mentorshipExcerpt);
        $this->assertStringNotContainsString('&lt;p&gt;', $eventExcerpt);
        $this->assertStringNotContainsString('&lt;p&gt;', $mentorshipExcerpt);
    }

    public function test_portal_cards_strip_html_tags_from_public_excerpts(): void
    {
        $courseExcerpt = Blade::render(
            '{{ Str::limit(strip_tags((string) ($course->short_description ?? "")), 80) }}',
            [
                'course' => (object) [
                    'short_description' => '<p>Curso <strong>destaque</strong> da vitrine.</p>',
                ],
            ]
        );

        $eventExcerpt = Blade::render(
            '{{ Str::limit(strip_tags((string) ($event->description ?? "")), 60) }}',
            [
                'event' => (object) [
                    'description' => '<p>Evento <strong>portal</strong> com resumo limpo.</p>',
                ],
            ]
        );

        $mentorshipExcerpt = Blade::render(
            '{{ Str::limit(strip_tags((string) ($mentorship->description ?? "")), 100) }}',
            [
                'mentorship' => (object) [
                    'description' => '<p>Mentoria <em>portal</em> sem tags visíveis.</p>',
                ],
            ]
        );

        $this->assertStringContainsString('Curso destaque da vitrine.', $courseExcerpt);
        $this->assertStringContainsString('Evento portal com resumo limpo.', $eventExcerpt);
        $this->assertStringContainsString('Mentoria portal sem tags visíveis.', $mentorshipExcerpt);
        $this->assertStringNotContainsString('<p>', $courseExcerpt);
        $this->assertStringNotContainsString('<p>', $eventExcerpt);
        $this->assertStringNotContainsString('<p>', $mentorshipExcerpt);
        $this->assertStringNotContainsString('&lt;p&gt;', $courseExcerpt);
        $this->assertStringNotContainsString('&lt;p&gt;', $eventExcerpt);
        $this->assertStringNotContainsString('&lt;p&gt;', $mentorshipExcerpt);
    }
}
