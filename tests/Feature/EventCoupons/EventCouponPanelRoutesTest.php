<?php

/**
 * @autor marcelo-brad rj
 * @contato Tel: 21 981325441
 * @contato Email: contato@kdkhost.com.br
 * @contato Telegram: @MARCELO_BRAD
 * @contato Instagram: @marcelobradrj
 * @contato WhatsApp: 21981325441
 */

namespace Tests\Feature\EventCoupons;

use App\Http\Controllers\Admin\EventCouponController as LegacyEventCouponController;
use App\Http\Controllers\EventGroupController;
use App\Http\Controllers\Panel\Admin\EventCouponController as PanelEventCouponController;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class EventCouponPanelRoutesTest extends TestCase
{
    /**
     * @dataProvider couponRoutesProvider
     */
    public function test_event_coupon_routes_exist_in_both_admin_panels(
        string $routeName,
        string $uri,
        string $method,
        string $controller,
        string $action
    ): void {
        $route = Route::getRoutes()->getByName($routeName);

        $this->assertNotNull($route, "Rota {$routeName} nao foi registrada.");
        $this->assertSame($uri, $route->uri());
        $this->assertContains($method, $route->methods());
        $this->assertSame($controller . '@' . $action, $route->getActionName());
    }

    public function test_modern_panel_coupon_controller_reuses_legacy_business_rules(): void
    {
        $this->assertTrue(is_subclass_of(
            PanelEventCouponController::class,
            LegacyEventCouponController::class
        ));
    }

    public function test_event_group_join_route_requires_authentication_and_rate_limit(): void
    {
        $route = Route::getRoutes()->getByName('events.group.join');

        $this->assertNotNull($route);
        $this->assertSame('eventos/{event}/entrar-no-grupo', $route->uri());
        $this->assertContains('POST', $route->methods());
        $this->assertSame(EventGroupController::class . '@join', $route->getActionName());
        $this->assertContains('auth', $route->gatherMiddleware());
        $this->assertContains('throttle:10,1', $route->gatherMiddleware());
    }

    public static function couponRoutesProvider(): array
    {
        return [
            'admin index' => [
                'admin.events.coupons.index',
                'admin/events/{event}/coupons',
                'GET',
                LegacyEventCouponController::class,
                'index',
            ],
            'admin create' => [
                'admin.events.coupons.create',
                'admin/events/{event}/coupons/create',
                'GET',
                LegacyEventCouponController::class,
                'create',
            ],
            'admin store' => [
                'admin.events.coupons.store',
                'admin/events/{event}/coupons',
                'POST',
                LegacyEventCouponController::class,
                'store',
            ],
            'admin edit' => [
                'admin.events.coupons.edit',
                'admin/events/{event}/coupons/{coupon}/edit',
                'GET',
                LegacyEventCouponController::class,
                'edit',
            ],
            'admin update' => [
                'admin.events.coupons.update',
                'admin/events/{event}/coupons/{coupon}',
                'PUT',
                LegacyEventCouponController::class,
                'update',
            ],
            'admin toggle' => [
                'admin.events.coupons.toggle',
                'admin/events/{event}/coupons/{coupon}/toggle',
                'POST',
                LegacyEventCouponController::class,
                'toggle',
            ],
            'admin destroy' => [
                'admin.events.coupons.destroy',
                'admin/events/{event}/coupons/{coupon}',
                'DELETE',
                LegacyEventCouponController::class,
                'destroy',
            ],
            'panel index' => [
                'panel.admin.events.coupons.index',
                'painel/admin/events/{event}/coupons',
                'GET',
                PanelEventCouponController::class,
                'index',
            ],
            'panel create' => [
                'panel.admin.events.coupons.create',
                'painel/admin/events/{event}/coupons/create',
                'GET',
                PanelEventCouponController::class,
                'create',
            ],
            'panel store' => [
                'panel.admin.events.coupons.store',
                'painel/admin/events/{event}/coupons',
                'POST',
                PanelEventCouponController::class,
                'store',
            ],
            'panel edit' => [
                'panel.admin.events.coupons.edit',
                'painel/admin/events/{event}/coupons/{coupon}/edit',
                'GET',
                PanelEventCouponController::class,
                'edit',
            ],
            'panel update' => [
                'panel.admin.events.coupons.update',
                'painel/admin/events/{event}/coupons/{coupon}',
                'PUT',
                PanelEventCouponController::class,
                'update',
            ],
            'panel toggle' => [
                'panel.admin.events.coupons.toggle',
                'painel/admin/events/{event}/coupons/{coupon}/toggle',
                'POST',
                PanelEventCouponController::class,
                'toggle',
            ],
            'panel destroy' => [
                'panel.admin.events.coupons.destroy',
                'painel/admin/events/{event}/coupons/{coupon}',
                'DELETE',
                PanelEventCouponController::class,
                'destroy',
            ],
        ];
    }
}
