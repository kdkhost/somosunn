<?php

/**
 * @autor marcelo-brad rj
 * @contato Tel: 21 981325441
 * @contato Email: contato@kdkhost.com.br
 * @contato Telegram: @MARCELO_BRAD
 * @contato Instagram: @marcelobradrj
 * @contato WhatsApp: 21981325441
 */

namespace Tests\Feature\Security;

use Illuminate\Routing\Route as RoutingRoute;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class SensitiveRouteCoverageTest extends TestCase
{
    public function test_cache_clear_route_is_post_only_local_admin_contract(): void
    {
        $route = $this->findRouteByUri('limpar-cache');

        $this->assertNotNull($route);
        $this->assertContains('POST', $route->methods());
        $this->assertNotContains('GET', $route->methods());
        $this->assertContains('auth', $route->gatherMiddleware());
        $this->assertContains('admin', $route->gatherMiddleware());
    }

    public function test_http_migration_route_is_retired_and_still_admin_protected(): void
    {
        $route = $this->findRouteByUri('run-migrations');

        $this->assertNotNull($route);
        $this->assertContains('POST', $route->methods());
        $this->assertNotContains('GET', $route->methods());
        $this->assertContains('auth', $route->gatherMiddleware());
        $this->assertContains('admin', $route->gatherMiddleware());
    }

    public function test_public_mercado_pago_webhook_is_rate_limited(): void
    {
        $route = $this->findRouteByUri('webhook/mercadopago');

        $this->assertNotNull($route);
        $this->assertContains('POST', $route->methods());
        $this->assertContains('throttle:webhook_mercadopago', $route->gatherMiddleware());
    }

    public function test_installer_routes_are_explicit_and_without_get_runner(): void
    {
        $this->assertSame('install', Route::getRoutes()->getByName('install.index')?->uri());
        $this->assertSame('install/run', Route::getRoutes()->getByName('install.run')?->uri());
        $this->assertSame('install/test-connection', Route::getRoutes()->getByName('install.test-connection')?->uri());
        $this->assertSame('backend/install/run', Route::getRoutes()->getByName('install.run.legacy')?->uri());
        $this->assertSame(
            'backend/install/test-connection',
            Route::getRoutes()->getByName('install.test-connection.legacy')?->uri()
        );

        $this->assertNotContains('GET', Route::getRoutes()->getByName('install.run')->methods());
        $this->assertNotContains('GET', Route::getRoutes()->getByName('install.test-connection')->methods());
        $this->assertNotContains('GET', Route::getRoutes()->getByName('install.run.legacy')->methods());
        $this->assertNotContains('GET', Route::getRoutes()->getByName('install.test-connection.legacy')->methods());
    }

    private function findRouteByUri(string $uri): ?RoutingRoute
    {
        foreach (Route::getRoutes() as $route) {
            if ($route->uri() === $uri) {
                return $route;
            }
        }

        return null;
    }
}
