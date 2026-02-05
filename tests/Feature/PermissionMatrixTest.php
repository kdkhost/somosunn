<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Plan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class PermissionMatrixTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function an_admin_can_access_any_feature()
    {
        $admin = User::create([
            'name' => 'Admin',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
        ]);

        $this->assertTrue($admin->canAccessFeature('any_feature'));
        $this->assertTrue($admin->canAccessFeature('whatsapp'));
    }

    /** @test */
    public function a_member_can_only_access_features_in_their_plan()
    {
        $plan = Plan::create([
            'name' => 'Basic',
            'slug' => 'basic',
            'permissions' => ['courses', 'events']
        ]);

        $user = User::create([
            'name' => 'Member',
            'email' => 'member@example.com',
            'password' => Hash::make('password'),
            'role' => 'member',
            'plan_id' => $plan->id
        ]);

        $this->assertTrue($user->canAccessFeature('courses'));
        $this->assertTrue($user->canAccessFeature('events'));
        $this->assertFalse($user->canAccessFeature('mentorships'));
        $this->assertFalse($user->canAccessFeature('chat'));
    }

    /** @test */
    public function a_member_cannot_access_features_if_plan_is_expired()
    {
        $plan = Plan::create([
            'name' => 'Basic',
            'slug' => 'basic-expired',
            'permissions' => ['courses']
        ]);

        $user = User::create([
            'name' => 'Member Expired',
            'email' => 'member-expired@example.com',
            'password' => Hash::make('password'),
            'role' => 'member',
            'plan_id' => $plan->id,
            'plan_expires_at' => now()->subDay()
        ]);

        $this->assertFalse($user->canAccessFeature('courses'));
    }

    /** @test */
    public function routes_are_protected_by_feature_middleware()
    {
        $plan = Plan::create([
            'name' => 'Basic',
            'slug' => 'basic-routes',
            'permissions' => ['courses']
        ]);

        $user = User::create([
            'name' => 'Member Routes',
            'email' => 'member-routes@example.com',
            'password' => Hash::make('password'),
            'role' => 'member',
            'plan_id' => $plan->id
        ]);

        // Accessing a feature they HAVE
        $this->actingAs($user)
            ->get(route('courses.index'))
            ->assertOk();

        // Accessing a feature they DON'T HAVE
        $this->actingAs($user)
            ->get(route('chat.index'))
            ->assertRedirect(route('portal'));
    }
}
