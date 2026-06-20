<?php

namespace Tests\Feature;

use App\Models\User;
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
}
