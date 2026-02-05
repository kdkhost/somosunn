<?php

namespace Tests\Feature;

use App\Models\Event;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SiteEventsTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_events_index_does_not_require_login(): void
    {
        $this->get(route('events.index'))->assertOk();
    }

    public function test_unpublished_event_returns_404(): void
    {
        $event = Event::create([
            'title' => 'Evento privado',
            'start_at' => now()->addDay(),
            'end_at' => now()->addDay()->addHour(),
            'published' => false,
        ]);

        $this->get(route('events.show', $event))->assertNotFound();
    }
}

