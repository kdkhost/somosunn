<?php

namespace Tests\Feature;

use App\Http\Controllers\MemberController;
use App\Models\Page;
use App\Models\Plan;
use App\Models\Setting;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class PublicMembersListingTest extends TestCase
{
    private string $sqlitePath;
    private string $originalDefaultConnection;
    private string $originalSqliteDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->originalDefaultConnection = (string) config('database.default');
        $this->originalSqliteDatabase = (string) config('database.connections.sqlite.database');
        $this->sqlitePath = database_path('testing-public-members-listing.sqlite');

        if (file_exists($this->sqlitePath)) {
            @unlink($this->sqlitePath);
        }

        touch($this->sqlitePath);

        config()->set('database.default', 'sqlite');
        config()->set('database.connections.sqlite.database', $this->sqlitePath);

        DB::purge('sqlite');
        DB::reconnect('sqlite');

        Page::resetTableAvailabilityCache();
        Setting::flushRuntimeCache();

        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password')->nullable();
            $table->string('role')->default('member');
            $table->boolean('hide_profile')->default(false);
            $table->unsignedBigInteger('plan_id')->nullable();
            $table->timestamp('plan_expires_at')->nullable();
            $table->text('bio')->nullable();
            $table->string('photo')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('linkedin')->nullable();
            $table->string('facebook')->nullable();
            $table->string('instagram')->nullable();
            $table->string('twitter')->nullable();
            $table->string('youtube')->nullable();
            $table->string('website')->nullable();
            $table->string('level')->nullable();
            $table->timestamps();
        });

        Schema::create('plans', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->nullable();
            $table->decimal('price', 10, 2)->default(0);
            $table->boolean('is_active')->default(true);
            $table->boolean('is_free')->default(false);
            $table->timestamps();
        });

        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('plan_id');
            $table->string('status')->default('active');
            $table->timestamp('started_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->timestamp('next_billing_at')->nullable();
            $table->boolean('prorata')->default(false);
            $table->string('cycle')->nullable();
            $table->timestamps();
        });

        Schema::create('connections', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('requester_id');
            $table->unsignedBigInteger('requested_id');
            $table->string('status')->default('pending');
            $table->timestamp('responded_at')->nullable();
            $table->boolean('hide_profile')->default(false);
            $table->timestamps();
        });
    }

    protected function tearDown(): void
    {
        DB::disconnect('sqlite');

        config()->set('database.default', $this->originalDefaultConnection);
        config()->set('database.connections.sqlite.database', $this->originalSqliteDatabase);

        Page::resetTableAvailabilityCache();
        Setting::flushRuntimeCache();

        if (file_exists($this->sqlitePath)) {
            @unlink($this->sqlitePath);
        }

        parent::tearDown();
    }

    public function test_public_members_page_lists_only_users_with_paid_active_access(): void
    {
        $freePlan = Plan::create([
            'name' => 'Gratis',
            'slug' => 'free',
            'price' => 0,
            'is_active' => true,
            'is_free' => true,
        ]);

        $paidPlan = Plan::create([
            'name' => 'Pro',
            'slug' => 'pro',
            'price' => 99,
            'is_active' => true,
            'is_free' => false,
        ]);

        User::create([
            'name' => 'Membro Gratuito',
            'email' => 'gratis@example.com',
            'password' => 'secret',
            'plan_id' => $freePlan->id,
            'plan_expires_at' => now()->addMonth(),
        ]);

        User::create([
            'name' => 'Membro Pago Direto',
            'email' => 'pago-direto@example.com',
            'password' => 'secret',
            'plan_id' => $paidPlan->id,
            'plan_expires_at' => now()->addMonth(),
        ]);

        User::create([
            'name' => 'Membro Pago Expirado',
            'email' => 'expirado@example.com',
            'password' => 'secret',
            'plan_id' => $paidPlan->id,
            'plan_expires_at' => now()->subDay(),
        ]);

        $paidSubscriber = User::create([
            'name' => 'Membro Pago Assinatura',
            'email' => 'pago-assinatura@example.com',
            'password' => 'secret',
        ]);

        Subscription::create([
            'user_id' => $paidSubscriber->id,
            'plan_id' => $paidPlan->id,
            'status' => 'active',
            'started_at' => now()->subDay(),
            'ends_at' => now()->addMonth(),
        ]);

        $freeSubscriber = User::create([
            'name' => 'Membro Gratis Assinatura',
            'email' => 'gratis-assinatura@example.com',
            'password' => 'secret',
        ]);

        Subscription::create([
            'user_id' => $freeSubscriber->id,
            'plan_id' => $freePlan->id,
            'status' => 'active',
            'started_at' => now()->subDay(),
            'ends_at' => now()->addMonth(),
        ]);

        $view = app(MemberController::class)->index();
        $members = $view->getData()['members'];
        $names = $members->pluck('name')->sort()->values()->all();

        $this->assertSame([
            'Membro Pago Assinatura',
            'Membro Pago Direto',
        ], $names);
    }
}
