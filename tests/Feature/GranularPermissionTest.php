<?php
namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Plan;

class GranularPermissionTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Testa todas as features granularizadas para cada rota protegida.
     */
    public function test_all_granular_features_are_enforced()
    {
        $user = User::factory()->create(['role' => 'member']);
        $plan = Plan::factory()->create([
            'permissions' => [
                // Exemplo: só permite acesso a cursos
                'courses_access',
            ],
        ]);
        $user->plan_id = $plan->id;
        $user->save();
        $this->actingAs($user);

        // Rotas que devem permitir
        $this->get('/courses')->assertStatus(200);
        // Rotas que devem bloquear
        $this->get('/mentorships')->assertStatus(403);
        $this->get('/admin/coupons')->assertStatus(403);
        $this->post('/courses')->assertStatus(403);
        $this->get('/admin/plans')->assertStatus(403);
        $this->get('/admin/fonts')->assertStatus(403);
        $this->get('/admin/faq')->assertStatus(403);
        $this->get('/admin/events')->assertStatus(403);
        $this->get('/admin/points-rules')->assertStatus(403);
        $this->get('/admin/certificates/create')->assertStatus(403);
        $this->get('/admin/ranking')->assertStatus(403);
        $this->post('/admin/upload/chunk')->assertStatus(403);
        $this->get('/admin/mailtemplates')->assertStatus(403);
        $this->get('/admin/orders')->assertStatus(403);
        $this->get('/admin/invoices')->assertStatus(403);
        $this->get('/admin/social')->assertStatus(403);
    }
}
