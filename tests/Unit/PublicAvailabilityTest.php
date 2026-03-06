<?php

namespace Tests\Unit;

use App\Models\Event;
use App\Models\Mentorship;
use Tests\TestCase;

class PublicAvailabilityTest extends TestCase
{
    public function test_event_with_past_end_is_closed_for_public(): void
    {
        $event = new Event([
            'title' => 'Evento encerrado',
            'start_at' => now()->subDays(2),
            'end_at' => now()->subDay(),
        ]);

        $this->assertTrue($event->isClosedForPublic());
        $this->assertFalse($event->hasPublicAction());
    }

    public function test_event_with_future_start_keeps_public_action(): void
    {
        $event = new Event([
            'title' => 'Evento futuro',
            'start_at' => now()->addDay(),
        ]);

        $this->assertFalse($event->isClosedForPublic());
        $this->assertTrue($event->hasPublicAction());
    }

    public function test_mentorship_with_future_session_keeps_public_action(): void
    {
        $mentorship = new Mentorship([
            'title' => 'Mentoria aberta',
            'schedule' => [
                'timezone' => 'America/Sao_Paulo',
                'sessions' => [
                    [
                        'date' => now()->addDays(3)->format('Y-m-d'),
                        'time' => '19:00',
                    ],
                ],
            ],
        ]);

        $this->assertFalse($mentorship->isClosedForPublic());
        $this->assertTrue($mentorship->hasPublicAction());
        $this->assertNotNull($mentorship->latestScheduleAt());
    }

    public function test_mentorship_with_only_past_sessions_is_closed_for_public(): void
    {
        $mentorship = new Mentorship([
            'title' => 'Mentoria encerrada',
            'schedule' => [
                'timezone' => 'America/Sao_Paulo',
                'sessions' => [
                    [
                        'date' => now()->subDays(2)->format('Y-m-d'),
                        'time' => '19:00',
                    ],
                    [
                        'date' => now()->subDay()->format('Y-m-d'),
                        'time' => '21:00',
                    ],
                ],
            ],
        ]);

        $this->assertTrue($mentorship->isClosedForPublic());
        $this->assertFalse($mentorship->hasPublicAction());
    }
}
