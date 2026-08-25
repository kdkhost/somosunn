<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\Testimonial;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SomosUnicasPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_somos_unicas_page_shows_upcoming_visible_events_with_open_action(): void
    {
        $futureEvent = Event::create([
            'title' => 'Encontro Futuro Somos Unicas',
            'start_at' => now()->addDays(4)->setTime(19, 0),
            'end_at' => now()->addDays(4)->setTime(21, 0),
            'published' => true,
            'visibility' => 'somos_unicas',
        ]);

        Event::create([
            'title' => 'Evento Passado Somos Unicas',
            'start_at' => now()->subDays(2)->setTime(19, 0),
            'end_at' => now()->subDays(2)->setTime(21, 0),
            'published' => true,
            'visibility' => 'somos_unicas',
        ]);

        $response = $this->get(route('somos-unicas'));

        $response->assertOk();
        $response->assertSee('Encontro Futuro Somos Unicas');
        $response->assertSee($futureEvent->start_at->format('d'));
        $response->assertSee('Garantir Vaga');
        $response->assertDontSee('Evento Passado Somos Unicas');
        $response->assertDontSee('Evento Encerrado');
    }

    public function test_somos_unicas_page_only_shows_approved_active_testimonials(): void
    {
        Testimonial::create([
            'author_name' => 'Integrante Aprovada',
            'content' => 'Minha experiência na comunidade foi transformadora.',
            'rating' => 5,
            'status' => 'approved',
            'is_active' => true,
        ]);

        Testimonial::create([
            'author_name' => 'Integrante Pendente',
            'content' => 'Este depoimento ainda não pode aparecer.',
            'rating' => 5,
            'status' => 'pending',
            'is_active' => true,
        ]);

        Testimonial::create([
            'author_name' => 'Integrante Inativa',
            'content' => 'Este depoimento foi desativado.',
            'rating' => 5,
            'status' => 'approved',
            'is_active' => false,
        ]);

        $response = $this->get(route('somos-unicas'));

        $response->assertOk();
        $response->assertSee('O que dizem nossas integrantes');
        $response->assertSee('Integrante Aprovada');
        $response->assertSee('Minha experiência na comunidade foi transformadora.');
        $response->assertDontSee('Integrante Pendente');
        $response->assertDontSee('Integrante Inativa');
    }
}
