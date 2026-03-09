<?php

namespace Tests\Unit;

use App\Models\Plan;
use Tests\TestCase;

class PlanCommercialPermissionsTest extends TestCase
{
    public function test_paid_plan_keeps_only_explicit_permissions(): void
    {
        $permissions = Plan::normalizeCommercialPermissions(['community', 'courses'], false, 97.0);

        $this->assertContains('community', $permissions);
        $this->assertContains('courses', $permissions);
        $this->assertNotContains('marketplace.sell', $permissions);
        $this->assertNotContains('courses.create', $permissions);
        $this->assertNotContains('events.create', $permissions);
        $this->assertNotContains('mentorships.create', $permissions);
    }

    public function test_free_plan_uses_canonical_permissions_only(): void
    {
        $permissions = Plan::normalizeCommercialPermissions(['community', 'courses', 'events.create'], true, 0);

        $this->assertSame(Plan::DEFAULT_FREE_PLAN_PERMISSIONS, $permissions);
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

    public function test_pro_blueprint_exposes_customer_requested_permissions(): void
    {
        $plan = new Plan([
            'slug' => 'pro',
            'price' => 97,
            'permissions' => [],
        ]);

        $this->assertTrue($plan->hasFeature('community'));
        $this->assertTrue($plan->hasFeature('courses'));
        $this->assertTrue($plan->hasFeature('mentorships'));
        $this->assertTrue($plan->hasFeature('benefits.club.access'));
        $this->assertTrue($plan->hasFeature('events.pitch.priority'));
        $this->assertTrue($plan->hasFeature('events.keynote.annual'));
        $this->assertTrue($plan->hasFeature('events.first_lot'));
        $this->assertTrue($plan->hasFeature('rankings'));
        $this->assertFalse($plan->hasFeature('courses.create'));
        $this->assertFalse($plan->hasFeature('events.create'));
        $this->assertFalse($plan->hasFeature('benefits.club.partner'));
    }

    public function test_elite_blueprint_exposes_creator_and_partner_permissions(): void
    {
        $plan = new Plan([
            'slug' => 'elite',
            'price' => 297,
            'permissions' => [],
        ]);

        $this->assertTrue($plan->hasFeature('courses.create'));
        $this->assertTrue($plan->hasFeature('mentorships.create'));
        $this->assertTrue($plan->hasFeature('events.create'));
        $this->assertTrue($plan->hasFeature('events.mentor'));
        $this->assertTrue($plan->hasFeature('benefits.club.partner'));
        $this->assertTrue($plan->hasFeature('marketplace.sell'));
    }

    public function test_available_periods_only_return_enabled_positive_prices(): void
    {
        $plan = new Plan([
            'price' => 97,
            'price_periods' => [
                'mensal' => 97,
                'trimestral' => 270,
                'semestral' => 0,
                'anual' => 900,
            ],
            'period_settings' => [
                'mensal' => ['enabled' => false],
                'trimestral' => ['enabled' => true],
                'semestral' => ['enabled' => true],
                'anual' => ['enabled' => false],
            ],
        ]);

        $this->assertSame(['trimestral' => 270.0], $plan->getAvailablePeriods());
        $this->assertSame('trimestral', $plan->firstAvailablePeriod());
        $this->assertSame(270.0, $plan->getPriceForPeriod('mensal'));
    }
}
