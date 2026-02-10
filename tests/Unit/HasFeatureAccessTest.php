<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\User;
use App\Models\Plan;
use Illuminate\Foundation\Testing\RefreshDatabase;

class HasFeatureAccessTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function admin_can_access_any_feature()
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->assertTrue($admin->canAccessFeature('non_existent_feature'));
        $this->assertTrue($admin->canAccessFeature('courses'));
    }

    /** @test */
    public function member_without_plan_cannot_access_features()
    {
        $member = User::factory()->create(['role' => 'member']);

        $this->assertFalse($member->canAccessFeature('courses'));
    }

    /** @test */
    public function member_with_plan_can_access_plan_features()
    {
        $plan = Plan::factory()->create([
            'permissions' => ['events', 'courses']
        ]);

        $member = User::factory()->create([
            'role' => 'member',
            'plan_id' => $plan->id,
            'plan_expires_at' => now()->addMonth()
        ]);

        $this->assertTrue($member->canAccessFeature('events'));
        $this->assertTrue($member->canAccessFeature('courses'));
        $this->assertFalse($member->canAccessFeature('mentorships'));
    }

    /** @test */
    public function member_with_extra_features_can_access_them()
    {
        $member = User::factory()->create([
            'role' => 'member',
            'extra_features' => ['whatsapp']
        ]);

        $this->assertTrue($member->canAccessFeature('whatsapp'));
        $this->assertFalse($member->canAccessFeature('courses'));
    }

    /** @test */
    public function expired_plan_revokes_access()
    {
        $plan = Plan::factory()->create([
            'permissions' => ['events']
        ]);

        $member = User::factory()->create([
            'role' => 'member',
            'plan_id' => $plan->id,
            'plan_expires_at' => now()->subDay() // Expired
        ]);

        $this->assertFalse($member->canAccessFeature('events'));
    }
}
