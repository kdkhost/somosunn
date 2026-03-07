<?php

namespace Tests\Unit;

use App\Events\ServiceVisitRegistered;
use PHPUnit\Framework\TestCase;

class ServiceVisitRegisteredTest extends TestCase
{
    public function test_event_exposes_broadcast_name_and_payload(): void
    {
        $event = new ServiceVisitRegistered('curso', 15, 42);

        $this->assertSame('service.visit.registered', $event->broadcastAs());
        $this->assertSame([
            'serviceType' => 'curso',
            'serviceId' => 15,
            'count' => 42,
        ], $event->broadcastWith());
    }
}
