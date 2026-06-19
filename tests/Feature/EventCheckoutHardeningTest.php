<?php

namespace Tests\Feature;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class EventCheckoutHardeningTest extends TestCase
{
    public function test_event_create_route_has_precedence_over_event_slug_route(): void
    {
        $createRoute = Route::getRoutes()->match(Request::create('/eventos/create', 'GET'));
        $showRoute = Route::getRoutes()->match(Request::create('/eventos/somos-unn-summit', 'GET'));

        $this->assertSame('events.create', $createRoute->getName());
        $this->assertSame('events.show', $showRoute->getName());
    }

    public function test_event_public_reservation_uses_specific_rate_limiter(): void
    {
        $route = Route::getRoutes()->getByName('events.reserve');

        $this->assertNotNull($route);
        $this->assertContains('throttle:event_reservations', $route->gatherMiddleware());
    }
}
