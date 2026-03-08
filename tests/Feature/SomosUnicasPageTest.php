<?php

namespace Tests\Feature;

use App\Models\Event;
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
}
