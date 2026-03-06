<?php

namespace Tests\Feature;

use App\Models\AffiliateApiSandboxRequest;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class AffiliateSandboxRequestTest extends TestCase
{
    private string $sqlitePath;

    protected function setUp(): void
    {
        parent::setUp();

        $this->sqlitePath = database_path('testing-affiliate-sandbox-request.sqlite');

        if (file_exists($this->sqlitePath)) {
            @unlink($this->sqlitePath);
        }

        touch($this->sqlitePath);

        config()->set('database.default', 'sqlite');
        config()->set('database.connections.sqlite.database', $this->sqlitePath);

        DB::purge('sqlite');
        DB::reconnect('sqlite');

        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password')->nullable();
            $table->string('role')->nullable();
            $table->string('level')->nullable();
            $table->string('referral_code', 20)->nullable();
            $table->timestamps();
        });

        Schema::create('affiliate_api_sandbox_requests', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->text('reason');
            $table->string('requested_domain')->nullable();
            $table->string('requested_ip', 45)->nullable();
            $table->string('status')->default('pending');
            $table->text('admin_notes')->nullable();
            $table->unsignedBigInteger('reviewed_by')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();
        });
    }

    protected function tearDown(): void
    {
        DB::disconnect('sqlite');

        if (file_exists($this->sqlitePath)) {
            @unlink($this->sqlitePath);
        }

        parent::tearDown();
    }

    public function test_member_can_open_or_update_sandbox_ticket_from_panel(): void
    {
        $this->withoutMiddleware();

        $user = User::create([
            'name' => 'Afiliado Ticket',
            'email' => 'ticket@example.com',
            'role' => 'member',
            'referral_code' => 'UNNTICKET',
        ]);

        $this->actingAs($user)
            ->post(route('panel.referral.sandbox.store'), [
                'reason' => 'Quero testar meu microsite privado de afiliados com integração via API.',
                'requested_domain' => 'afiliado.exemplo.com',
                'requested_ip' => '203.0.113.40',
            ])
            ->assertRedirect(route('panel.referral.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('affiliate_api_sandbox_requests', [
            'user_id' => $user->id,
            'status' => 'pending',
            'requested_domain' => 'afiliado.exemplo.com',
            'requested_ip' => '203.0.113.40',
        ]);

        $this->actingAs($user)
            ->post(route('panel.referral.sandbox.store'), [
                'reason' => 'Atualizando o ticket para informar o domínio final e o novo IP de saída.',
                'requested_domain' => 'painel.afiliado.exemplo.com',
                'requested_ip' => '203.0.113.41',
            ])
            ->assertRedirect(route('panel.referral.index'))
            ->assertSessionHas('success');

        $this->assertSame(1, AffiliateApiSandboxRequest::query()->where('user_id', $user->id)->count());
        $this->assertDatabaseHas('affiliate_api_sandbox_requests', [
            'user_id' => $user->id,
            'status' => 'pending',
            'requested_domain' => 'painel.afiliado.exemplo.com',
            'requested_ip' => '203.0.113.41',
        ]);
    }

    public function test_superadmin_can_review_sandbox_ticket_in_legacy_admin(): void
    {
        $this->withoutMiddleware([\App\Http\Middleware\EnsureUserHasActivePlan::class]);

        $admin = User::create([
            'name' => 'Super Admin',
            'email' => 'superadmin@example.com',
            'role' => 'superadmin',
        ]);

        $member = User::create([
            'name' => 'Afiliado Revisão',
            'email' => 'revisao@example.com',
            'role' => 'member',
            'referral_code' => 'UNNREVIEW',
        ]);

        $request = AffiliateApiSandboxRequest::query()->create([
            'user_id' => $member->id,
            'reason' => 'Preciso homologar um painel próprio que vai consumir overview e analytics.',
            'requested_domain' => 'painel.revenda.com',
            'requested_ip' => '203.0.113.55',
            'status' => 'pending',
        ]);

        $this->actingAs($admin)
            ->put(route('admin.referrals.sandbox.update', $request), [
                'status' => 'approved',
                'admin_notes' => 'Aprovado para homologação no domínio e IP informados.',
            ])
            ->assertRedirect(route('admin.referrals.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('affiliate_api_sandbox_requests', [
            'id' => $request->id,
            'status' => 'approved',
            'reviewed_by' => $admin->id,
            'admin_notes' => 'Aprovado para homologação no domínio e IP informados.',
        ]);
    }
}