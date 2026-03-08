<?php

namespace Tests\Unit;

use App\Models\Plan;
use Tests\TestCase;

class PlanCommercialPermissionsTest extends TestCase
{
    public function test_paid_plan_receives_instructor_and_seller_bundle(): void
    {
        $permissions = Plan::normalizeCommercialPermissions(['community'], false, 49.90);

        $this->assertContains('marketplace.sell', $permissions);
        $this->assertContains('courses.create', $permissions);
        $this->assertContains('events.create', $permissions);
        $this->assertContains('mentorships.create', $permissions);
        $this->assertContains('courses.certificates', $permissions);
    }

    public function test_free_plan_does_not_receive_paid_bundle(): void
    {
        $permissions = Plan::normalizeCommercialPermissions(['community'], true, 0);

        $this->assertContains('community', $permissions);
        $this->assertNotContains('marketplace.sell', $permissions);
        $this->assertNotContains('courses.create', $permissions);
    }

    public function test_free_plan_strips_instructor_and_seller_permissions_even_if_already_present(): void
    {
        $permissions = Plan::normalizeCommercialPermissions([
            'community',
            'courses',
            'events',
            'mentorships',
            'marketplace.sell',
            'events.create',
            'courses.certificates',
        ], true, 0);

        $this->assertContains('community', $permissions);
        $this->assertContains('rankings', $permissions);
        $this->assertNotContains('courses', $permissions);
        $this->assertNotContains('events', $permissions);
        $this->assertNotContains('mentorships', $permissions);
        $this->assertNotContains('marketplace.sell', $permissions);
    }

    public function test_free_plan_does_not_grant_runtime_access_to_commercial_features(): void
    {
        $plan = new Plan([
            'price' => 0,
            'is_free' => true,
            'permissions' => ['community', 'rankings', 'courses', 'events', 'marketplace.sell', 'events.create'],
        ]);

        $this->assertFalse($plan->hasFeature('marketplace.sell'));
        $this->assertFalse($plan->hasFeature('events.create'));
        $this->assertFalse($plan->hasFeature('courses'));
        $this->assertFalse($plan->hasFeature('courses_access'));
        $this->assertFalse($plan->hasFeature('events'));
        $this->assertTrue($plan->hasFeature('community'));
        $this->assertTrue($plan->hasFeature('rankings'));
    }

    public function test_free_plan_uses_canonical_benefits_and_description(): void
    {
        $plan = new Plan([
            'price' => 0,
            'is_free' => true,
            'description' => 'Descricao antiga',
            'benefits' => ['Beneficio antigo'],
        ]);

        $this->assertSame(Plan::DEFAULT_FREE_PLAN_DESCRIPTION, $plan->marketingDescription());
        $this->assertSame(Plan::DEFAULT_FREE_PLAN_BENEFITS, $plan->resolvedBenefits());
    }
}
