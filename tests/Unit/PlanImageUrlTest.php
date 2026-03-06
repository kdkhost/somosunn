<?php

namespace Tests\Unit;

use App\Models\Plan;
use Tests\TestCase;

class PlanImageUrlTest extends TestCase
{
    public function test_plan_image_url_resolves_public_disk_paths(): void
    {
        $plan = new Plan([
            'name' => 'Plano Pro',
            'image' => 'plan-images/pro.png',
        ]);

        $this->assertSame(asset('storage/plan-images/pro.png'), $plan->image_url);
    }

    public function test_plan_image_url_keeps_existing_public_paths(): void
    {
        $plan = new Plan([
            'name' => 'Plano Pro',
            'image' => 'uploads/plan-images/pro.png',
        ]);

        $this->assertSame(asset('uploads/plan-images/pro.png'), $plan->image_url);
    }
}
