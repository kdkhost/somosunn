<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SupervisedAccessIsolationTest extends TestCase
{
    use RefreshDatabase;

    public function test_supervised_access_uses_a_clean_session_and_target_user_data(): void
    {
        $supervisor = $this->user('Supervisor Principal', 'supervisor@test.com', 'superadmin');
        $target = $this->user('Usuario Acessado', 'target@test.com', 'member');

        $response = $this->actingAs($supervisor)
            ->withSession([
                '_old_input' => ['name' => $supervisor->name, 'email' => $supervisor->email],
                'url.intended' => '/admin/profile',
                'temporary_supervisor_data' => 'nao pode vazar',
            ])
            ->get(route('admin.users.impersonate', $target));

        $response->assertRedirect(route('panel.dashboard'));
        $this->assertAuthenticatedAs($target);
        $response->assertSessionMissing('_old_input');
        $response->assertSessionMissing('url.intended');
        $response->assertSessionMissing('temporary_supervisor_data');
        $response->assertSessionHas('impersonator_id', $supervisor->id);
        $response->assertSessionHas('impersonator_name', $supervisor->name);
        $response->assertSessionHas('impersonated_user_id', $target->id);
        $response->assertSessionHas('impersonated_user_name', $target->name);

        $this->get(route('panel.profile.edit'))
            ->assertOk()
            ->assertHeader('Cache-Control', 'no-store, no-cache, must-revalidate, private')
            ->assertSee('value="' . $target->name . '"', false)
            ->assertDontSee('value="' . $supervisor->name . '"', false)
            ->assertSee('Supervisor: ' . $supervisor->name)
            ->assertSee('Conta: ' . $target->name);
    }

    public function test_supervised_access_profile_disables_browser_autofill_for_sensitive_fields(): void
    {
        $supervisor = $this->user('Supervisor Autofill', 'supervisor-autofill@test.com', 'superadmin');
        $target = $this->user('Cliente Original', 'cliente-original@test.com', 'member');

        $this->actingAs($supervisor)
            ->get(route('admin.users.impersonate', $target))
            ->assertRedirect(route('panel.dashboard'));

        $this->get(route('panel.profile.edit'))
            ->assertOk()
            ->assertSee('data-supervised-profile="1"', false)
            ->assertSee('data-autosave="false"', false)
            ->assertSee('id="supervised-profile-payload"', false)
            ->assertSee('name="supervised_profile_fake_name"', false)
            ->assertSee('name="supervised_profile_fake_email"', false)
            ->assertSee('name="supervised_profile_fake_phone"', false)
            ->assertSee('name="name"', false)
            ->assertSee('value="' . $target->name . '"', false)
            ->assertSee('type="email" name="email"', false)
            ->assertSee('value="' . $target->email . '"', false)
            ->assertSee('autocomplete="off"', false)
            ->assertSee('"name":"' . $target->name . '"', false)
            ->assertSee('"email":"' . $target->email . '"', false)
            ->assertSee('data-supervised-lock="1"', false);
    }

    public function test_stopping_supervised_access_restores_supervisor_with_a_clean_session(): void
    {
        $supervisor = $this->user('Supervisor Principal', 'supervisor-stop@test.com', 'superadmin');
        $target = $this->user('Usuario Acessado', 'target-stop@test.com', 'member');

        $response = $this->actingAs($target)
            ->withSession([
                'impersonator_id' => $supervisor->id,
                'impersonator_name' => $supervisor->name,
                'impersonated_user_id' => $target->id,
                'impersonated_user_name' => $target->name,
                '_old_input' => ['name' => $target->name],
                'temporary_target_data' => 'nao pode voltar',
            ])
            ->get(route('admin.impersonate.stop'));

        $response->assertRedirect(route('admin.dashboard'));
        $this->assertAuthenticatedAs($supervisor);
        $response->assertSessionMissing('impersonator_id');
        $response->assertSessionMissing('impersonated_user_id');
        $response->assertSessionMissing('_old_input');
        $response->assertSessionMissing('temporary_target_data');
    }

    public function test_nested_supervised_access_is_blocked(): void
    {
        $supervisor = $this->user('Supervisor Principal', 'supervisor-nested@test.com', 'superadmin');
        $firstTarget = $this->user('Primeiro Usuario', 'first-target@test.com', 'admin');
        $secondTarget = $this->user('Segundo Usuario', 'second-target@test.com', 'member');

        $response = $this->actingAs($firstTarget)
            ->withSession([
                'impersonator_id' => $supervisor->id,
                'impersonator_name' => $supervisor->name,
                'impersonated_user_id' => $firstTarget->id,
                'impersonated_user_name' => $firstTarget->name,
            ])
            ->get(route('admin.users.impersonate', $secondTarget));

        $response->assertRedirect(route('panel.dashboard'));
        $this->assertAuthenticatedAs($firstTarget);
        $response->assertSessionHas('impersonator_id', $supervisor->id);
        $response->assertSessionHas('impersonated_user_id', $firstTarget->id);
    }

    public function test_inconsistent_supervised_session_is_terminated(): void
    {
        $supervisor = $this->user('Supervisor Principal', 'supervisor-invalid@test.com', 'superadmin');
        $authenticatedUser = $this->user('Usuario Autenticado', 'authenticated@test.com', 'member');
        $unexpectedUser = $this->user('Usuario Inesperado', 'unexpected@test.com', 'member');

        $response = $this->actingAs($authenticatedUser)
            ->withSession([
                'impersonator_id' => $supervisor->id,
                'impersonator_name' => $supervisor->name,
                'impersonated_user_id' => $unexpectedUser->id,
                'impersonated_user_name' => $unexpectedUser->name,
            ])
            ->get(route('panel.dashboard'));

        $response->assertRedirect(route('login'));
        $this->assertGuest();
        $response->assertSessionMissing('impersonator_id');
        $response->assertSessionMissing('impersonated_user_id');
    }

    private function user(string $name, string $email, string $role): User
    {
        return User::create([
            'name' => $name,
            'email' => $email,
            'password' => bcrypt('secret123'),
            'role' => $role,
            'level' => $role === 'superadmin' ? 'superadmin' : 'iniciante',
            'email_verified_at' => now(),
        ]);
    }
}
