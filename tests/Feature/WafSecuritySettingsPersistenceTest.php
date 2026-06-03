<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\Waf\WafSettings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WafSecuritySettingsPersistenceTest extends TestCase
{
    use RefreshDatabase;

    public function test_security_settings_are_persisted_in_the_keys_used_by_the_waf(): void
    {
        $admin = User::create([
            'name' => 'Superadmin',
            'email' => 'security-admin@test.com',
            'password' => bcrypt('secret123'),
            'role' => 'superadmin',
            'level' => 'superadmin',
        ]);

        $response = $this->actingAs($admin)->put(route('admin.security.update'), [
            'mode' => 'enforce',
            'threshold_monitor' => 10,
            'threshold_challenge' => 40,
            'threshold_block' => 90,
            'fail_policy' => 'block',
            'exempt_routes' => "/health\n/webhook/*",
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('waf_settings', ['key' => 'waf.thresholds']);
        $this->assertDatabaseMissing('waf_settings', ['key' => 'waf.threshold.monitor']);

        $settings = WafSettings::load();
        $this->assertSame('enforce', $settings->mode);
        $this->assertSame(10, $settings->thresholdMonitor);
        $this->assertSame(40, $settings->thresholdChallenge);
        $this->assertSame(90, $settings->thresholdBlock);
        $this->assertFalse($settings->isFailOpen());
        $this->assertSame(['/health', '/webhook/*'], $settings->exemptRoutes);
    }
}
