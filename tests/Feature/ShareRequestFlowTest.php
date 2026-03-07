<?php

namespace Tests\Feature;

use App\Console\Commands\ExpireShareRequests;
use App\Http\Middleware\CheckFeature;
use App\Http\Middleware\EnsureUserHasActivePlan;
use App\Models\Post;
use App\Models\ShareRequest;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class ShareRequestFlowTest extends TestCase
{
    private string $sqlitePath;

    protected function setUp(): void
    {
        parent::setUp();

        $this->sqlitePath = database_path('testing-share-requests.sqlite');

        if (file_exists($this->sqlitePath)) {
            @unlink($this->sqlitePath);
        }

        touch($this->sqlitePath);

        config()->set('database.default', 'sqlite');
        config()->set('database.connections.sqlite.database', $this->sqlitePath);

        DB::purge('sqlite');
        DB::reconnect('sqlite');

        $this->createSchema();
    }

    protected function tearDown(): void
    {
        DB::disconnect('sqlite');

        if (file_exists($this->sqlitePath)) {
            @unlink($this->sqlitePath);
        }

        parent::tearDown();
    }

    public function test_member_can_create_approve_and_track_pending_share_requests(): void
    {
        Notification::fake();

        $this->disableAccessMiddleware();

        [$author, $sender, $recipient] = $this->seedUsers();
        $this->connectUsers($sender->id, $recipient->id);

        $post = Post::query()->create([
            'user_id' => $author->id,
            'content' => 'Conteúdo original do post',
            'visibility' => 'connections',
            'is_pinned' => false,
        ]);

        $this->actingAs($sender)
            ->from('/feed')
            ->post(route('social.post.share.user', ['post' => $post->getKey()]), [
                'target_user_id' => $recipient->id,
                'message' => 'Confira isso',
            ])
            ->assertRedirect('/feed')
            ->assertSessionHas('success');

        $shareRequest = ShareRequest::query()->first();

        $this->assertNotNull($shareRequest);
        $this->assertSame('pending', $shareRequest->status);

        $this->actingAs($recipient)
            ->getJson(route('social.share-requests.count'))
            ->assertOk()
            ->assertJson(['count' => 1]);

        $this->actingAs($recipient)
            ->getJson(route('notifications.hub'))
            ->assertOk()
            ->assertJsonPath('total', 1)
            ->assertJsonFragment([
                'type' => 'share_requests',
                'count' => 1,
            ]);

        $this->actingAs($recipient)
            ->from('/compartilhamentos/pendentes')
            ->post(route('social.share-requests.approve', ['shareRequest' => $shareRequest->getKey()]))
            ->assertRedirect('/compartilhamentos/pendentes')
            ->assertSessionHas('success');

        $shareRequest->refresh();

        $this->assertSame('approved', $shareRequest->status);

        $publishedPost = Post::query()
            ->where('user_id', $recipient->id)
            ->where('shared_to_user_id', $recipient->id)
            ->latest('id')
            ->first();

        $this->assertNotNull($publishedPost);
        $this->assertStringContainsString('Confira isso', (string) $publishedPost->content);
        $this->assertStringContainsString('Compartilhou de Autor Original:', (string) $publishedPost->content);
        $this->assertStringContainsString('Conteúdo original do post', (string) $publishedPost->content);

        $this->actingAs($recipient)
            ->getJson(route('social.share-requests.count'))
            ->assertOk()
            ->assertJson(['count' => 0]);
    }

    public function test_member_can_reject_pending_share_request(): void
    {
        Notification::fake();

        $this->disableAccessMiddleware();

        [, $sender, $recipient] = $this->seedUsers();

        $post = Post::query()->create([
            'user_id' => $sender->id,
            'content' => 'Post para rejeição',
            'visibility' => 'connections',
            'is_pinned' => false,
        ]);

        $shareRequest = ShareRequest::query()->create([
            'post_id' => $post->id,
            'from_user_id' => $sender->id,
            'to_user_id' => $recipient->id,
            'message' => 'Não publique isso',
            'status' => 'pending',
            'expires_at' => now()->addDays(7),
        ]);

        $this->actingAs($recipient)
            ->from('/compartilhamentos/pendentes')
            ->post(route('social.share-requests.reject', ['shareRequest' => $shareRequest->getKey()]))
            ->assertRedirect('/compartilhamentos/pendentes')
            ->assertSessionHas('success');

        $shareRequest->refresh();

        $this->assertSame('rejected', $shareRequest->status);
        $this->assertSame(1, Post::query()->count());
    }

    public function test_expire_command_marks_old_pending_requests_as_expired(): void
    {
        $this->disableAccessMiddleware();

        [$author, $sender, $recipient] = $this->seedUsers();

        $post = Post::query()->create([
            'user_id' => $author->id,
            'content' => 'Post expirável',
            'visibility' => 'connections',
            'is_pinned' => false,
        ]);

        ShareRequest::query()->create([
            'post_id' => $post->id,
            'from_user_id' => $sender->id,
            'to_user_id' => $recipient->id,
            'status' => 'pending',
            'expires_at' => now()->subDays(8),
        ]);

        ShareRequest::query()->create([
            'post_id' => $post->id,
            'from_user_id' => $sender->id,
            'to_user_id' => $recipient->id,
            'status' => 'pending',
            'expires_at' => now()->addDays(2),
        ]);

        $this->assertSame(0, Artisan::call('share-requests:expire'));

        $this->assertDatabaseHas('share_requests', [
            'status' => 'expired',
        ]);

        $this->assertSame(1, ShareRequest::query()->pending()->count());
    }

    public function test_member_cannot_approve_share_request_of_another_member(): void
    {
        Notification::fake();

        $this->disableAccessMiddleware();

        [$author, $sender, $recipient] = $this->seedUsers();
        $intruder = User::query()->create([
            'name' => 'Intruso',
            'email' => 'intruso@example.com',
            'password' => bcrypt('secret'),
            'role' => 'member',
        ]);

        $post = Post::query()->create([
            'user_id' => $author->id,
            'content' => 'Post protegido',
            'visibility' => 'connections',
            'is_pinned' => false,
        ]);

        $shareRequest = ShareRequest::query()->create([
            'post_id' => $post->id,
            'from_user_id' => $sender->id,
            'to_user_id' => $recipient->id,
            'status' => 'pending',
            'expires_at' => now()->addDays(7),
        ]);

        $this->actingAs($intruder)
            ->postJson(route('social.share-requests.approve', ['shareRequest' => $shareRequest->getKey()]))
            ->assertForbidden();

        $shareRequest->refresh();

        $this->assertSame('pending', $shareRequest->status);
        $this->assertSame(1, Post::query()->count());
    }

    private function seedUsers(): array
    {
        $author = User::query()->create([
            'name' => 'Autor Original',
            'email' => 'autor@example.com',
            'password' => bcrypt('secret'),
            'role' => 'member',
        ]);

        $sender = User::query()->create([
            'name' => 'Remetente',
            'email' => 'remetente@example.com',
            'password' => bcrypt('secret'),
            'role' => 'member',
        ]);

        $recipient = User::query()->create([
            'name' => 'Destinatário',
            'email' => 'destinatario@example.com',
            'password' => bcrypt('secret'),
            'role' => 'member',
        ]);

        return [$author, $sender, $recipient];
    }

    private function connectUsers(int $requesterId, int $requestedId): void
    {
        DB::table('connections')->insert([
            'requester_id' => $requesterId,
            'requested_id' => $requestedId,
            'status' => 'accepted',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function createSchema(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password')->nullable();
            $table->string('role')->nullable();
            $table->string('level')->nullable();
            $table->string('username')->nullable();
            $table->string('photo')->nullable();
            $table->string('referral_code', 20)->nullable();
            $table->timestamp('plan_expires_at')->nullable();
            $table->timestamps();
        });

        Schema::create('posts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('shared_to_user_id')->nullable();
            $table->text('content')->nullable();
            $table->string('visibility')->nullable();
            $table->boolean('is_pinned')->default(false);
            $table->timestamps();
        });

        Schema::create('post_media', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('post_id')->nullable();
            $table->string('file_path')->nullable();
            $table->timestamps();
        });

        Schema::create('post_reactions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('post_id')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('reaction')->nullable();
            $table->timestamps();
        });

        Schema::create('post_comments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('post_id')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->text('content')->nullable();
            $table->timestamps();
        });

        Schema::create('share_requests', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('post_id');
            $table->unsignedBigInteger('from_user_id');
            $table->unsignedBigInteger('to_user_id');
            $table->text('message')->nullable();
            $table->string('status')->default('pending');
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
        });

        Schema::create('connections', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('requester_id')->nullable();
            $table->unsignedBigInteger('requested_id')->nullable();
            $table->string('status')->nullable();
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
            $table->id();
            $table->unsignedBigInteger('conversation_id')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('role')->nullable();
            $table->timestamp('joined_at')->nullable();
            $table->timestamps();
        });

        Schema::create('messages', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('conversation_id')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->text('body')->nullable();
            $table->string('type')->nullable();
            $table->string('media_path')->nullable();
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
        });

        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('seller_id')->nullable();
            $table->string('status')->nullable();
            $table->timestamps();
        });

        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->timestamp('start_at')->nullable();
            $table->timestamps();
        });

        Schema::create('notifications', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('type');
            $table->morphs('notifiable');
            $table->text('data');
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
        });
    }

    private function disableAccessMiddleware(): void
    {
        $this->withoutMiddleware([
            EnsureUserHasActivePlan::class,
            CheckFeature::class,
        ]);
    }
}
