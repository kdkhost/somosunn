<?php

namespace Tests\Feature;

use App\Http\Middleware\EnsureUserHasActivePlan;
use App\Http\Middleware\LogUserActivity;
use App\Http\Middleware\RunInternalCron;
use App\Http\Middleware\TrackReferralLink;
use App\Http\Middleware\TrackVisitor;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Laravel\Sanctum\PersonalAccessToken;
use Tests\TestCase;

class PanelReferralApiTokenTest extends TestCase
{
    private string $sqlitePath;

    protected function setUp(): void
    {
        parent::setUp();

        $this->sqlitePath = database_path('testing-panel-referral-api-token.sqlite');

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

        Schema::create('personal_access_tokens', function (Blueprint $table) {
            $table->id();
            $table->morphs('tokenable');
            $table->string('name');
            $table->string('token', 64)->unique();
            $table->text('abilities')->nullable();
            $table->timestamp('last_used_at')->nullable();
            $table->string('last_used_ip', 45)->nullable();
            $table->timestamp('expires_at')->nullable();
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

    public function test_member_can_generate_rename_and_revoke_tokens_from_panel(): void
    {
        $this->withoutMiddleware([
            EnsureUserHasActivePlan::class,
            TrackReferralLink::class,
            TrackVisitor::class,
            RunInternalCron::class,
            LogUserActivity::class,
        ]);

        $user = User::create([
            'name' => 'Afiliado Teste',
            'email' => 'afiliado@example.com',
            'role' => 'member',
            'referral_code' => 'UNNAPI001',
        ]);

        $response = $this->actingAs($user)->post(route('panel.referral.tokens.store'), [
            'device_name' => 'Blog privado',
        ]);

        $response
            ->assertRedirect(route('panel.referral.index'))
            ->assertSessionHas('api_token_plain_text')
            ->assertSessionHas('api_token_device_name', 'Blog privado');

        $token = PersonalAccessToken::query()->where('name', 'Blog privado')->first();

        $this->assertNotNull($token);

        $this->actingAs($user)
            ->put(route('panel.referral.tokens.update', $token->id), [
                'device_name' => 'Blog privado v2',
            ])
            ->assertRedirect(route('panel.referral.index'))
            ->assertSessionHas('success', 'Token renomeado com sucesso.');

        $this->assertDatabaseHas('personal_access_tokens', [
            'id' => $token->id,
            'name' => 'Blog privado v2',
        ]);

        $this->actingAs($user)
            ->delete(route('panel.referral.tokens.destroy', $token->id))
            ->assertRedirect(route('panel.referral.index'))
            ->assertSessionHas('success', 'Token revogado com sucesso.');

        $this->assertDatabaseMissing('personal_access_tokens', [
            'id' => $token->id,
        ]);
    }

    public function test_api_updates_last_used_ip_for_personal_token(): void
    {
        $user = User::create([
            'name' => 'Afiliado API',
            'email' => 'api@example.com',
            'role' => 'member',
            'referral_code' => 'UNNAPI002',
        ]);

        $plainTextToken = $user->createToken('Painel privado')->plainTextToken;

        $this->withServerVariables([
            'REMOTE_ADDR' => '203.0.113.10',
        ])->getJson('/api/v1/me', [
            'Authorization' => 'Bearer ' . $plainTextToken,
            'Accept' => 'application/json',
        ])->assertOk()->assertJsonPath('user.email', 'api@example.com');

        $tokenId = (int) explode('|', $plainTextToken)[0];

        $token = PersonalAccessToken::query()->find($tokenId);

        $this->assertNotNull($token?->last_used_at);
        $this->assertSame('203.0.113.10', $token?->last_used_ip);
    }
}
