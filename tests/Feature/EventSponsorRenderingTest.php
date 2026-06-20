<?php

namespace Tests\Feature;

use App\Models\Event;
use Tests\TestCase;

class EventSponsorRenderingTest extends TestCase
{
    public function test_event_model_has_sponsors_relation(): void
    {
        $event = new Event();
        $this->assertTrue(method_exists($event, 'sponsors'));
    }
}
