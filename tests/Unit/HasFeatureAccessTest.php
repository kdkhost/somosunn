<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\User;
use App\Models\Plan;
use Illuminate\Foundation\Testing\RefreshDatabase;

class HasFeatureAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_access_any_feature()
    {
        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@test.com',
            'password' => bcrypt('password'),
            'role' => 'admin'
        ]);

        $this->assertTrue($admin->canAccessFeature('non_existent_feature'));
        $this->assertTrue($admin->canAccessFeature('events_create'));
    }

    public function test_superadmin_can_access_any_feature()
    {
        $super = User::create([
            'name' => 'Super Admin',
            'email' => 'super@test.com',
            'password' => bcrypt('password'),
            'role' => 'superadmin'
        ]);

        $this->assertTrue($super->canAccessFeature('anything'));
    }

    public function test_user_without_plan_cannot_access_feature()
    {
        $user = User::create([
            'name' => 'Member User',
            'email' => 'member@test.com',
            'password' => bcrypt('password'),
            'role' => 'member',
            'plan_id' => null
        ]);

        $this->assertFalse($user->canAccessFeature('events_create'));
    }

    public function test_user_with_plan_can_access_plan_features()
    {
        // Create a plan with specific permissions
        $plan = Plan::create([
            'name' => 'Basic Plan',
            'price' => 100,
            'duration_days' => 30,
            'permissions' => ['events_access', 'community_access']
        ]);

        $user = User::create([
            'name' => 'Plan User',
            'email' => 'plan@test.com',
            'password' => bcrypt('password'),
            'role' => 'member',
            'plan_id' => $plan->id,
            'plan_expires_at' => now()->addDays(30)
        ]);

        $this->assertTrue($user->canAccessFeature('events_access'));
        $this->assertTrue($user->canAccessFeature('community_access'));
        $this->assertFalse($user->canAccessFeature('events_create')); // Not in plan
    }

    public function test_user_with_expired_plan_cannot_access_features()
    {
        $plan = Plan::create([
            'name' => 'Basic Plan',
            'price' => 100,
            'duration_days' => 30,
            'permissions' => ['events_access']
        ]);

        $user = User::create([
            'name' => 'Expired User',
            'email' => 'expired@test.com',
            'password' => bcrypt('password'),
            'role' => 'member',
            'plan_id' => $plan->id,
            'plan_expires_at' => now()->subDay() // Expired
        ]);

        $this->assertFalse($user->canAccessFeature('events_access'));
    }

    public function test_user_can_access_extra_assigned_features()
    {
        $user = User::create([
            'name' => 'Extra User',
            'email' => 'extra@test.com',
            'password' => bcrypt('password'),
            'role' => 'member',
            'plan_id' => null,
            'extra_features' => ['special_access']
        ]);

        $this->assertTrue($user->canAccessFeature('special_access'));
        $this->assertFalse($user->canAccessFeature('other_feature'));
    }
}
