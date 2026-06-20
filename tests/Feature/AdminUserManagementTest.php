<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminUserManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_complete_member_with_verified_email(): void
    {
        $admin = User::factory()->create([
            'role' => 'superadmin',
            'email_verified_at' => now(),
        ]);

        $response = $this->actingAs($admin)->postJson(route('admin.users.store'), [
            'name' => 'Maria da Silva',
            'email' => 'maria@example.com',
            'password' => 'senha-segura',
            'password_confirmation' => 'senha-segura',
            'email_verified' => true,
            'person_type' => 'F',
            'doc' => '123.456.789-00',
            'phone' => '(21) 99999-9999',
            'gender' => 'female',
            'birth_date' => '1990-05-10',
            'occupation' => 'Empresária',
            'company' => 'Empresa Exemplo',
            'segment' => 'Tecnologia',
            'interests' => 'Networking, eventos',
            'bio' => 'Perfil criado pelo painel administrativo.',
            'cep' => '20000-000',
            'street' => 'Rua do Mercado',
            'number' => '10',
            'neighborhood' => 'Centro',
            'city' => 'Rio de Janeiro',
            'state' => 'RJ',
            'role' => 'member',
            'level' => 'iniciante',
        ]);

        $response->assertOk();

        $member = User::where('email', 'maria@example.com')->firstOrFail();
        $this->assertNotNull($member->email_verified_at);
        $this->assertSame('12345678900', $member->doc);
        $this->assertSame('Empresa Exemplo', $member->company);
        $this->assertSame('Rio de Janeiro', $member->city);
        $this->assertTrue((bool) $member->platform_fee_enabled);
    }

    public function test_admin_can_verify_member_email_manually_in_modern_panel(): void
    {
        $admin = User::factory()->create([
            'role' => 'superadmin',
            'email_verified_at' => now(),
        ]);
        $member = User::factory()->unverified()->create(['role' => 'member']);

        $response = $this->actingAs($admin)->post(route('panel.admin.users.verify-email', $member));

        $response->assertRedirect();
        $this->assertNotNull($member->fresh()->email_verified_at);
    }

    public function test_pix_key_is_hidden_for_member_and_visible_for_authorized_recipients(): void
    {
        $operator = User::factory()->create(['role' => 'superadmin']);
        $member = User::factory()->create(['role' => 'member']);
        $admin = User::factory()->create(['role' => 'admin']);
        $marketing = User::factory()->create(['role' => 'member']);
        Setting::set('platform_marketing_user_id', (string) $marketing->id);

        $this->actingAs($operator)
            ->get(route('admin.users.edit', $member))
            ->assertOk()
            ->assertSee('id="admin-user-pix-container"', false)
            ->assertSee('style="display:none;"', false)
            ->assertSee('disabled', false)
            ->assertSee('name="platform_fee_enabled"', false);

        $this->get(route('admin.users.edit', $admin))
            ->assertOk()
            ->assertSee('name="pix_key"', false)
            ->assertSee('required', false)
            ->assertDontSee('Cobrar taxa da plataforma deste membro', false);

        $this->get(route('panel.admin.users.edit', $marketing))
            ->assertOk()
            ->assertSee('name="pix_key"', false)
            ->assertSee('required', false);
    }

    public function test_admin_or_superadmin_registration_requires_pix_key(): void
    {
        $operator = User::factory()->create(['role' => 'superadmin']);

        $this->actingAs($operator)->postJson(route('admin.users.store'), [
            'name' => 'Administrador sem PIX',
            'email' => 'admin-sem-pix@example.com',
            'password' => 'senha-segura',
            'password_confirmation' => 'senha-segura',
            'email_verified' => true,
            'role' => 'admin',
            'level' => 'iniciante',
        ])->assertUnprocessable()->assertJsonValidationErrors('pix_key');
    }

    public function test_marketing_manager_assignment_requires_and_stores_pix_key(): void
    {
        $operator = User::factory()->create(['role' => 'superadmin']);
        $member = User::factory()->create(['role' => 'member', 'pix_key' => null]);

        $this->actingAs($operator)->postJson(route('admin.users.marketing-manager', $member), [
            'action' => 'set',
        ])->assertUnprocessable()->assertJsonValidationErrors('pix_key');

        $this->postJson(route('admin.users.marketing-manager', $member), [
            'action' => 'set',
            'pix_key' => 'pix-marketing-obrigatorio',
        ])->assertOk();

        $this->assertSame('pix-marketing-obrigatorio', $member->fresh()->pix_key);
        $this->assertSame((string) $member->id, Setting::get('platform_marketing_user_id'));
        $this->assertFalse((bool) $member->fresh()->platform_fee_enabled);
    }

    public function test_superadmin_can_toggle_platform_fee_for_regular_member_only(): void
    {
        $operator = User::factory()->create(['role' => 'superadmin']);
        $member = User::factory()->create(['role' => 'member', 'platform_fee_enabled' => true]);

        $this->actingAs($operator)->putJson(route('admin.users.update', $member), [
            'name' => $member->name,
            'email' => $member->email,
            'email_verified' => true,
            'role' => 'member',
            'level' => 'iniciante',
            'platform_fee_enabled' => false,
            'show_email_public' => false,
            'show_phone_public' => false,
            'show_address_public' => false,
            'hide_profile' => false,
        ])->assertOk();

        $this->assertFalse((bool) $member->fresh()->platform_fee_enabled);
    }

    public function test_non_superadmin_cannot_disable_platform_fee_for_member(): void
    {
        $operator = User::factory()->create(['role' => 'admin']);
        $member = User::factory()->create(['role' => 'member', 'platform_fee_enabled' => true]);

        $this->actingAs($operator)->putJson(route('admin.users.update', $member), [
            'name' => $member->name,
            'email' => $member->email,
            'email_verified' => true,
            'role' => 'member',
            'level' => 'iniciante',
            'platform_fee_enabled' => false,
            'show_email_public' => false,
            'show_phone_public' => false,
            'show_address_public' => false,
            'hide_profile' => false,
        ])->assertOk();

        $this->assertTrue((bool) $member->fresh()->platform_fee_enabled);
    }

    public function test_admin_and_superadmin_are_saved_without_platform_fee(): void
    {
        $operator = User::factory()->create(['role' => 'superadmin']);

        $this->actingAs($operator)->postJson(route('admin.users.store'), [
            'name' => 'Admin Isento',
            'email' => 'admin-isento@example.com',
            'password' => 'senha-segura',
            'password_confirmation' => 'senha-segura',
            'email_verified' => true,
            'role' => 'admin',
            'level' => 'iniciante',
            'pix_key' => 'pix-admin-isento',
            'platform_fee_enabled' => true,
            'show_email_public' => false,
            'show_phone_public' => false,
            'show_address_public' => false,
            'hide_profile' => false,
        ])->assertOk();

        $admin = User::where('email', 'admin-isento@example.com')->firstOrFail();

        $this->assertFalse((bool) $admin->platform_fee_enabled);
    }
}
