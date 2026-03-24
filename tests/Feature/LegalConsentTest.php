<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\LegalConsentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class LegalConsentTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Route::middleware('web')->post('/_test/lgpd-mutation', function () {
            return response()->json(['ok' => true]);
        });
    }

    public function test_authenticated_user_can_accept_current_lgpd_terms(): void
    {
        $user = $this->createUser();

        $response = $this->actingAs($user)
            ->postJson(route('lgpd.accept'), [
                'accept' => true,
            ]);

        $response->assertOk()
            ->assertJson([
                'accepted' => true,
            ]);

        $user->refresh();
        $service = app(LegalConsentService::class);

        $this->assertNotNull($user->lgpd_accepted_at);
        $this->assertSame($service->currentVersion(), $user->lgpd_version);
        $this->assertTrue($service->hasAcceptedCurrentVersion($user));
    }

    public function test_authenticated_user_without_consent_cannot_submit_mutation_requests(): void
    {
        $user = $this->createUser();

        $response = $this->actingAs($user)
            ->postJson('/_test/lgpd-mutation', [
                'name' => 'blocked',
            ]);

        $response->assertStatus(423)
            ->assertJson([
                'requires_lgpd_consent' => true,
            ]);
    }

    public function test_authenticated_user_with_current_consent_can_submit_mutation_requests(): void
    {
        $user = $this->createUser();
        $service = app(LegalConsentService::class);

        $user->forceFill([
            'lgpd_accepted_at' => now(),
            'lgpd_version' => $service->currentVersion(),
        ])->save();

        $response = $this->actingAs($user)
            ->postJson('/_test/lgpd-mutation', [
                'name' => 'allowed',
            ]);

        $response->assertOk()
            ->assertJson([
                'ok' => true,
            ]);
    }

    public function test_legal_pages_render_without_blocking_modal_for_user_pending_consent(): void
    {
        $user = $this->createUser();

        $response = $this->actingAs($user)
            ->get(route('site.lgpd'));

        $response->assertOk();
        $response->assertDontSee('id="lgpd-consent-modal"', false);
    }

    private function createUser(): User
    {
        static $sequence = 1;

        $user = User::create([
            'name' => 'Teste LGPD ' . $sequence,
            'email' => 'lgpd' . $sequence . '@example.com',
            'password' => Hash::make('password123'),
        ]);

        $sequence++;

        return $user;
    }
}
