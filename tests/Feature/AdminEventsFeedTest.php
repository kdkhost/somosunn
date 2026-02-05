<?php

namespace Tests\Feature;

use App\Models\Event;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminEventsFeedTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_events_feed_returns_json(): void
    {
        $event = Event::create([
            'title' => 'Evento calendário',
            'start_at' => now()->addDay(),
            'end_at' => now()->addDay()->addHour(),
            'published' => true,
        ]);

        $start = now()->startOfDay()->toIso8601String();
        $end = now()->addDays(7)->endOfDay()->toIso8601String();

        $this->withoutMiddleware()
            ->getJson(route('admin.events.feed', ['start' => $start, 'end' => $end]))
            ->assertOk()
            ->assertJsonFragment([
                'id' => $event->id,
                'title' => $event->title,
            ]);
    }
}

