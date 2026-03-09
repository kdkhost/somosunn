<?php

namespace Tests\Unit;

use App\Http\Controllers\Panel\MyJobController;
use ReflectionMethod;
use Tests\TestCase;

class MyJobVisibilityNormalizationTest extends TestCase
{
    public function test_it_normalizes_legacy_job_visibility_aliases(): void
    {
        $controller = new MyJobController();
        $method = new ReflectionMethod(MyJobController::class, 'normalizeVisibility');
        $method->setAccessible(true);

        $this->assertSame('external', $method->invoke($controller, 'public'));
        $this->assertSame('internal', $method->invoke($controller, 'private'));
        $this->assertSame('internal', $method->invoke($controller, 'internal'));
        $this->assertSame('external', $method->invoke($controller, 'external'));
        $this->assertSame('both', $method->invoke($controller, 'both'));
        $this->assertSame('both', $method->invoke($controller, 'qualquer-coisa'));
    }
}
