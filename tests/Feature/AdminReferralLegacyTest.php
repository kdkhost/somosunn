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

class AdminReferralLegacyTest extends TestCase
{
    private string $sqlitePath;

    protected function setUp(): void
    {
        parent::setUp();

        $this->sqlitePath = database_path('testing-admin-referral-legacy.sqlite');

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
            $table->string('photo')->nullable();
            $table->string('referral_code', 20)->nullable();
            $table->unsignedBigInteger('referred_by')->nullable();
            $table->unsignedBigInteger('plan_id')->nullable();
            $table->timestamp('plan_expires_at')->nullable();
            $table->timestamps();
        });

        Schema::create('plans', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->decimal('price', 10, 2)->default(0);
            $table->boolean('is_free')->default(false);
            $table->timestamps();
        });

        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('plan_id')->nullable();
            $table->string('status')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->timestamps();
        });

        Schema::create('points_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('action_key');
            $table->integer('points')->default(0);
            $table->text('meta')->nullable();
            $table->timestamps();
        });

        Schema::create('points_rules', function (Blueprint $table) {
            $table->id();
            $table->string('key');
            $table->integer('points')->default(0);
            $table->boolean('active')->default(true);
            $table->timestamps();
        });

        Schema::create('connections', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('requester_id')->nullable();
            $table->unsignedBigInteger('requested_id')->nullable();
            $table->string('status')->nullable();
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

    public function test_admin_legacy_referral_page_shows_personal_and_global_sections(): void
    {
        $this->withoutMiddleware([
            EnsureUserHasActivePlan::class,
            TrackReferralLink::class,
            TrackVisitor::class,
            RunInternalCron::class,
            LogUserActivity::class,
        ]);

        DB::table('points_rules')->insert([
            'key' => 'referral',
            'points' => 100,
            'active' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $admin = User::create([
            'name' => 'Super Admin',
            'email' => 'superadmin@example.com',
            'role' => 'superadmin',
            'level' => 'superadmin',
            'referral_code' => 'UNNADMIN01',
        ]);

        $response = $this->actingAs($admin)->get(route('admin.referrals.index'));

        $response
            ->assertOk()
            ->assertSee('Seu link de indicação')
            ->assertSee('Acesso API pessoal')
            ->assertSee('Rastreio global de indicações');
    }

    public function test_admin_can_generate_rename_and_revoke_tokens_from_legacy_admin(): void
    {
        $this->withoutMiddleware([
            EnsureUserHasActivePlan::class,
            TrackReferralLink::class,
            TrackVisitor::class,
            RunInternalCron::class,
            LogUserActivity::class,
        ]);

        $admin = User::create([
            'name' => 'Admin Token',
            'email' => 'admintoken@example.com',
            'role' => 'admin',
            'referral_code' => 'UNNADMIN02',
        ]);

        $response = $this->actingAs($admin)->post(route('admin.referrals.tokens.store'), [
            'device_name' => 'Painel legado',
        ]);

        $response
            ->assertRedirect(route('admin.referrals.index'))
            ->assertSessionHas('api_token_plain_text')
            ->assertSessionHas('api_token_device_name', 'Painel legado');

        $token = PersonalAccessToken::query()->where('name', 'Painel legado')->first();

        $this->assertNotNull($token);

        $this->actingAs($admin)
            ->put(route('admin.referrals.tokens.update', $token->id), [
                'device_name' => 'Painel legado v2',
            ])
            ->assertRedirect(route('admin.referrals.index'))
            ->assertSessionHas('success', 'Token renomeado com sucesso.');

        $this->assertDatabaseHas('personal_access_tokens', [
            'id' => $token->id,
            'name' => 'Painel legado v2',
        ]);

        $this->actingAs($admin)
            ->delete(route('admin.referrals.tokens.destroy', $token->id))
            ->assertRedirect(route('admin.referrals.index'))
            ->assertSessionHas('success', 'Token revogado com sucesso.');

        $this->assertDatabaseMissing('personal_access_tokens', [
            'id' => $token->id,
        ]);
    }
}
