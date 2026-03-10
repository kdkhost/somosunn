<?php

namespace Tests\Feature;

use App\Http\Controllers\NotificationHubController;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class NotificationHubAcknowledgementTest extends TestCase
{
    private string $sqlitePath;
    private string $originalDefaultConnection;
    private string $originalSqliteDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->originalDefaultConnection = (string) config('database.default');
        $this->originalSqliteDatabase = (string) config('database.connections.sqlite.database');
        $this->sqlitePath = database_path('testing-notification-hub-ack.sqlite');

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
            $table->string('role')->default('member');
            $table->string('level')->nullable();
            $table->unsignedBigInteger('plan_id')->nullable();
            $table->timestamp('plan_expires_at')->nullable();
            $table->text('extra_features')->nullable();
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

        Schema::create('share_requests', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('post_id')->nullable();
            $table->unsignedBigInteger('from_user_id')->nullable();
            $table->unsignedBigInteger('to_user_id')->nullable();
            $table->text('message')->nullable();
            $table->string('status')->default('pending');
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
        });

        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('seller_id')->nullable();
            $table->string('status')->default('pending');
            $table->timestamps();
        });

        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('title')->nullable();
            $table->timestamp('start_at')->nullable();
            $table->timestamp('end_at')->nullable();
            $table->timestamps();
        });

        Schema::create('notifications', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('type');
            $table->string('notifiable_type');
            $table->unsignedBigInteger('notifiable_id');
            $table->text('data')->nullable();
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
        });

        Schema::create('conversations', function (Blueprint $table) {
            $table->id();
            $table->string('type')->nullable();
            $table->string('title')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
        });

        Schema::create('conversation_user', function (Blueprint $table) {
            $table->unsignedBigInteger('conversation_id');
            $table->unsignedBigInteger('user_id');
            $table->string('role')->nullable();
            $table->timestamp('joined_at')->nullable();
        });

        Schema::create('messages', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('conversation_id');
            $table->unsignedBigInteger('user_id');
            $table->text('body')->nullable();
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
        });
    }

    protected function tearDown(): void
    {
        DB::disconnect('sqlite');

        config()->set('database.default', $this->originalDefaultConnection);
        config()->set('database.connections.sqlite.database', $this->originalSqliteDatabase);

        if (file_exists($this->sqlitePath)) {
            @unlink($this->sqlitePath);
        }

        parent::tearDown();
    }

    public function test_acknowledged_bucket_disappears_until_new_records_arrive(): void
    {
        $member = User::create([
            'name' => 'Member',
            'email' => 'member@example.com',
            'password' => 'secret',
            'extra_features' => [],
        ]);

        $requesterA = User::create([
            'name' => 'Requester A',
            'email' => 'requester-a@example.com',
            'password' => 'secret',
        ]);

        $requesterB = User::create([
            'name' => 'Requester B',
            'email' => 'requester-b@example.com',
            'password' => 'secret',
        ]);

        DB::table('connections')->insert([
            'requester_id' => $requesterA->id,
            'requested_id' => $member->id,
            'status' => 'pending',
            'hide_profile' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->actingAs($member);

        $controller = app(NotificationHubController::class);

        $initialPayload = $controller->index()->getData(true);
        $initialConnectionItem = collect($initialPayload['items'])->firstWhere('type', 'connections');

        $this->assertSame(1, $initialPayload['total']);
        $this->assertSame(1, $initialConnectionItem['count']);

        $acknowledgedPayload = $controller
            ->acknowledge(Request::create('/api/notifications/hub/acknowledge', 'POST', ['type' => 'connections']))
            ->getData(true);

        $acknowledgedConnectionItem = collect($acknowledgedPayload['items'])->firstWhere('type', 'connections');

        $this->assertSame(0, $acknowledgedPayload['total']);
        $this->assertSame(0, $acknowledgedConnectionItem['count']);

        $reloadedPayload = $controller->index()->getData(true);
        $reloadedConnectionItem = collect($reloadedPayload['items'])->firstWhere('type', 'connections');

        $this->assertSame(0, $reloadedPayload['total']);
        $this->assertSame(0, $reloadedConnectionItem['count']);

        DB::table('connections')->insert([
            'requester_id' => $requesterB->id,
            'requested_id' => $member->id,
            'status' => 'pending',
            'hide_profile' => false,
            'created_at' => now()->addSecond(),
            'updated_at' => now()->addSecond(),
        ]);

        $payloadWithNewRecord = $controller->index()->getData(true);
        $connectionItemWithNewRecord = collect($payloadWithNewRecord['items'])->firstWhere('type', 'connections');

        $this->assertSame(1, $payloadWithNewRecord['total']);
        $this->assertSame(1, $connectionItemWithNewRecord['count']);
    }
}
