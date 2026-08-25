<?php

namespace Tests\Unit;

use App\Http\Controllers\MagazineController;
use App\Models\Magazine;
use App\Models\User;
use Illuminate\Support\Facades\Route;
use ReflectionMethod;
use Tests\TestCase;

class FeaturedMagazineAccessTest extends TestCase
{
    public function test_published_featured_magazine_is_publicly_readable(): void
    {
        $magazine = new Magazine([
            'status' => 'published',
            'visibility' => 'interest',
            'is_featured' => true,
        ]);

        $method = new ReflectionMethod(MagazineController::class, 'canView');
        $method->setAccessible(true);

        $this->assertTrue($method->invoke(new MagazineController(), $magazine, null));
    }

    public function test_legacy_interest_magazine_is_publicly_readable(): void
    {
        $magazine = new Magazine([
            'status' => 'published',
            'visibility' => 'interest',
            'is_featured' => false,
        ]);

        $method = new ReflectionMethod(MagazineController::class, 'canView');
        $method->setAccessible(true);

        $this->assertTrue($method->invoke(new MagazineController(), $magazine, null));
    }

    public function test_members_magazine_requires_authentication(): void
    {
        $magazine = new Magazine([
            'status' => 'published',
            'visibility' => 'members',
        ]);

        $method = new ReflectionMethod(MagazineController::class, 'canView');
        $method->setAccessible(true);

        $this->assertFalse($method->invoke(new MagazineController(), $magazine, null));
        $this->assertTrue($method->invoke(new MagazineController(), $magazine, new User(['role' => 'member'])));
    }

    public function test_magazine_reader_route_is_not_globally_restricted_to_authentication(): void
    {
        $route = Route::getRoutes()->getByName('magazines.show');

        $this->assertNotNull($route);
        $this->assertNotContains('auth', $route->gatherMiddleware());
    }

    public function test_magazine_index_route_is_available_as_safe_public_fallback(): void
    {
        $route = Route::getRoutes()->getByName('magazines.index');

        $this->assertNotNull($route);
        $this->assertNotContains('auth', $route->gatherMiddleware());
    }

    public function test_featured_toggle_routes_exist_in_both_admin_panels(): void
    {
        $legacy = Route::getRoutes()->getByName('admin.magazines.toggle-featured');
        $panel = Route::getRoutes()->getByName('panel.admin.magazines.toggle-featured');

        $this->assertNotNull($legacy);
        $this->assertNotNull($panel);
        $this->assertSame('admin/magazines/{magazine}/toggle-featured', $legacy->uri());
        $this->assertSame('painel/admin/magazines/{magazine}/toggle-featured', $panel->uri());
        $this->assertContains('POST', $legacy->methods());
        $this->assertContains('POST', $panel->methods());
    }
}
