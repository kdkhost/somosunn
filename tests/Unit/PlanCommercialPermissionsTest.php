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
}
