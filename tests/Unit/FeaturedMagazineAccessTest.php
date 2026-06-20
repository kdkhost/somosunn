<?php

namespace Tests\Unit;

use App\Http\Controllers\MagazineController;
use App\Models\Magazine;
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

    public function test_non_featured_interest_magazine_still_requires_user_access(): void
    {
        $magazine = new Magazine([
            'status' => 'published',
            'visibility' => 'interest',
            'is_featured' => false,
        ]);

        $method = new ReflectionMethod(MagazineController::class, 'canView');
        $method->setAccessible(true);

        $this->assertFalse($method->invoke(new MagazineController(), $magazine, null));
    }

    public function test_magazine_reader_route_is_not_globally_restricted_to_authentication(): void
    {
        $route = Route::getRoutes()->getByName('magazines.show');

        $this->assertNotNull($route);
        $this->assertNotContains('auth', $route->gatherMiddleware());
    }
}
