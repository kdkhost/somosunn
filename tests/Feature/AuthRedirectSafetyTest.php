<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthRedirectSafetyTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_ignores_dashboard_stats_as_intended_redirect(): void
    {
        $user = User::create([
            'name' => 'Redirect Safe',
            'email' => 'redirect-safe-' . rand(1, 100000) . '@test.com',
            'password' => bcrypt('secret123'),
        ]);

        $response = $this->withSession([
            'url.intended' => route('panel.dashboard.stats', ['chart' => 1]),
        ])->post('/login', [
            'email' => $user->email,
            'password' => 'secret123',
        ]);

        $response->assertRedirect(route('panel.dashboard'));
    }

    public function test_member_dashboard_stats_redirects_when_opened_directly_in_browser(): void
    {
        $admin = User::create([
            'name' => 'Redirect Admin',
            'email' => 'redirect-admin-' . rand(1, 100000) . '@test.com',
            'password' => bcrypt('secret123'),
            'role' => 'admin',
        ]);

        $this->actingAs($admin);

        $this->get(route('panel.dashboard.stats'))
            ->assertRedirect(route('panel.dashboard'));
    }

    public function test_admin_dashboard_stats_redirects_when_opened_directly_in_browser(): void
    {
        $admin = User::create([
            'name' => 'Legacy Redirect Admin',
            'email' => 'legacy-redirect-admin-' . rand(1, 100000) . '@test.com',
            'password' => bcrypt('secret123'),
            'role' => 'superadmin',
        ]);

        $this->actingAs($admin);

        $this->get(route('admin.dashboard.stats'))
            ->assertRedirect(route('admin.dashboard'));
    }
}
