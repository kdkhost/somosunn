<?php

namespace Tests\Feature;

use App\Models\Plan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PlanHighlightTest extends TestCase
{
    use RefreshDatabase;

    public function test_only_one_plan_can_be_highlighted(): void
    {
        $this->withoutMiddleware();

        $this->post(route('admin.plans.store'), [
            'name' => 'Plano A',
            'slug' => '',
            'description' => '',
            'price' => 10,
            'period' => 'mensal',
            'highlight' => 1,
            'coupons_enabled' => 0,
            'benefits' => "Benefício 1\nBenefício 2",
            'permissions' => [],
            'is_active' => 1,
        ])->assertRedirect(route('admin.plans.index'));

        $planA = Plan::where('name', 'Plano A')->firstOrFail();
        $this->assertTrue((bool) $planA->highlight);

        $this->post(route('admin.plans.store'), [
            'name' => 'Plano B',
            'slug' => '',
            'description' => '',
            'price' => 20,
            'period' => 'mensal',
            'highlight' => 1,
            'coupons_enabled' => 0,
            'benefits' => "Benefício 1\nBenefício 2",
            'permissions' => [],
            'is_active' => 1,
        ])->assertRedirect(route('admin.plans.index'));

        $planA->refresh();
        $planB = Plan::where('name', 'Plano B')->firstOrFail();

        $this->assertTrue((bool) $planB->highlight);
        $this->assertFalse((bool) $planA->highlight);
    }
}

