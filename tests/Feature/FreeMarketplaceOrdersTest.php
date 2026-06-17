<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Event;
use App\Models\EventCoupon;
use App\Models\EventRegistration;
use App\Models\Mentorship;
use App\Models\Order;
use App\Models\Setting;
use App\Models\User;
use App\Services\LegalConsentService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Schema;
use Mockery;
use Tests\TestCase;

class FreeMarketplaceOrdersTest extends TestCase
{
    private string $sqlitePath;
    private string $originalDefaultConnection;
    private string $originalSqliteDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Notification::fake();
        Setting::flushRuntimeCache();

        $legalConsentMock = Mockery::mock(LegalConsentService::class);
        $legalConsentMock->shouldReceive('hasAcceptedCurrentVersion')->andReturn(true);
        $this->app->instance(LegalConsentService::class, $legalConsentMock);

        $this->originalDefaultConnection = (string) config('database.default');
        $this->originalSqliteDatabase = (string) config('database.connections.sqlite.database');
        $this->sqlitePath = database_path('testing-free-marketplace-orders.sqlite');

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
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password')->nullable();
            $table->string('role')->nullable();
            $table->string('level')->nullable();
            $table->unsignedBigInteger('plan_id')->nullable();
            $table->timestamp('plan_expires_at')->nullable();
            $table->json('extra_features')->nullable();
            $table->rememberToken()->nullable();
            $table->timestamps();
        });

        Schema::create('plans', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->nullable();
            $table->json('permissions')->nullable();
            $table->timestamps();
        });

        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('plan_id')->nullable();
            $table->string('status')->default('inactive');
            $table->timestamp('ends_at')->nullable();
            $table->timestamps();
        });

        Schema::create('connections', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('requester_id');
            $table->unsignedBigInteger('requested_id');
            $table->string('status')->default('pending');
            $table->timestamps();
        });

        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->string('group')->nullable();
            $table->timestamps();
        });

        Schema::create('rate_limit_blocks', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('ip_address', 45);
            $table->string('reason', 100);
            $table->timestamp('blocked_until');
            $table->unsignedInteger('attempts')->default(1);
            $table->timestamp('created_at')->useCurrent();
        });

        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('title');
            $table->text('description')->nullable();
            $table->timestamp('start_at')->nullable();
            $table->timestamp('end_at')->nullable();
            $table->decimal('price', 10, 2)->default(0);
            $table->decimal('flash_sale_price', 10, 2)->nullable();
            $table->timestamp('flash_sale_ends_at')->nullable();
            $table->decimal('batch_1_price', 10, 2)->nullable();
            $table->timestamp('batch_1_deadline')->nullable();
            $table->decimal('batch_2_price', 10, 2)->nullable();
            $table->timestamp('batch_2_deadline')->nullable();
            $table->decimal('batch_3_price', 10, 2)->nullable();
            $table->timestamp('batch_3_deadline')->nullable();
            $table->unsignedInteger('capacity')->nullable();
            $table->boolean('published')->default(false);
            $table->boolean('all_day')->default(false);
            $table->boolean('is_ticket_enabled')->default(false);
            $table->string('whatsapp_group_link')->nullable();
            $table->timestamps();
        });

        Schema::create('event_coupons', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('event_id');
            $table->string('code', 40);
            $table->string('type', 20)->default('free');
            $table->decimal('discount_value', 10, 2)->default(100);
            $table->unsignedInteger('max_uses')->nullable();
            $table->unsignedInteger('used_count')->default(0);
            $table->dateTime('starts_at')->nullable();
            $table->dateTime('expires_at')->nullable();
            $table->boolean('active')->default(true);
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
        });

        Schema::create('event_registrations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('event_id');
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('order_id')->nullable();
            $table->unsignedBigInteger('coupon_id')->nullable();
            $table->string('status')->default('pending');
            $table->string('payment_status', 30)->default('pending');
            $table->string('ticket_code')->nullable();
            $table->decimal('price', 10, 2)->default(0);
            $table->unsignedInteger('quantity')->default(1);
            $table->timestamp('check_in_at')->nullable();
            $table->timestamp('joined_group_at')->nullable();
            $table->timestamps();
        });

        Schema::create('event_exhibitor_registrations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('event_id');
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('order_id');
            $table->string('name');
            $table->string('email');
            $table->string('phone', 50)->nullable();
            $table->string('document', 30)->nullable();
            $table->string('company_name')->nullable();
            $table->string('company_document', 30)->nullable();
            $table->string('brand_name')->nullable();
            $table->text('description')->nullable();
            $table->unsignedInteger('quantity')->default(1);
            $table->decimal('unit_price', 10, 2)->default(0);
            $table->decimal('total_price', 10, 2)->default(0);
            $table->string('batch_label', 30)->nullable();
            $table->string('status', 30)->default('pending');
            $table->string('payment_status', 30)->default('pending');
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->text('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('courses', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('title');
            $table->string('slug')->nullable();
            $table->decimal('price', 10, 2)->default(0);
            $table->decimal('flash_sale_price', 10, 2)->nullable();
            $table->timestamp('flash_sale_ends_at')->nullable();
            $table->boolean('is_recurring')->default(false);
            $table->string('mp_plan_id')->nullable();
            $table->timestamps();
        });

        Schema::create('mentorships', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('mentor_id')->nullable();
            $table->string('title');
            $table->text('description')->nullable();
            $table->text('schedule')->nullable();
            $table->decimal('price', 10, 2)->default(0);
            $table->decimal('flash_sale_price', 10, 2)->nullable();
            $table->timestamp('flash_sale_ends_at')->nullable();
            $table->timestamps();
        });

        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('seller_id')->nullable();
            $table->string('status')->default('pending');
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->decimal('total_amount', 10, 2)->default(0);
            $table->decimal('fee_amount', 10, 2)->default(0);
            $table->decimal('platform_fee_amount', 10, 2)->default(0);
            $table->string('currency')->nullable();
            $table->string('gateway')->nullable();
            $table->string('payment_method')->nullable();
            $table->boolean('is_manual_approval')->nullable();
            $table->unsignedBigInteger('manual_approved_by')->nullable();
            $table->timestamp('manual_approved_at')->nullable();
            $table->unsignedBigInteger('gateway_account_id')->nullable();
            $table->text('metadata')->nullable();
            $table->string('transaction_id')->nullable();
            $table->timestamp('refunded_at')->nullable();
            $table->timestamps();
        });

        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('order_id');
            $table->string('item_type');
            $table->unsignedBigInteger('item_id');
            $table->string('title');
            $table->decimal('price', 10, 2)->default(0);
            $table->unsignedInteger('quantity')->default(1);
            $table->text('data')->nullable();
            $table->timestamps();
        });

        Schema::create('order_splits', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('order_id');
            $table->string('receiver_type');
            $table->unsignedBigInteger('receiver_id')->nullable();
            $table->decimal('amount', 15, 2)->default(0);
            $table->decimal('percentage', 5, 2)->default(0);
            $table->string('status')->default('pending');
            $table->string('pix_key')->nullable();
            $table->timestamps();
        });

        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('order_id')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->string('number')->nullable();
            $table->string('status')->default('issued');
            $table->string('currency')->nullable();
            $table->decimal('subtotal', 10, 2)->default(0);
            $table->decimal('discount_amount', 10, 2)->default(0);
            $table->decimal('total_amount', 10, 2)->default(0);
            $table->timestamp('issued_at')->nullable();
            $table->timestamp('due_at')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('email_sent_at')->nullable();
            $table->timestamp('email_queued_at')->nullable();
            $table->text('notes')->nullable();
            $table->text('metadata')->nullable();
            $table->timestamps();
        });

        Schema::create('invoice_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('invoice_id');
            $table->string('item_type')->nullable();
            $table->unsignedBigInteger('item_id')->nullable();
            $table->string('description');
            $table->unsignedInteger('quantity')->default(1);
            $table->decimal('unit_price', 10, 2)->default(0);
            $table->decimal('total_price', 10, 2)->default(0);
            $table->text('data')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('coupon_redemptions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('coupon_id')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('order_id')->nullable();
            $table->string('status')->default('reserved');
            $table->decimal('discount_amount', 10, 2)->default(0);
            $table->timestamp('reserved_until')->nullable();
            $table->timestamps();
        });

        Schema::create('enrollments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('enrollable_id');
            $table->string('enrollable_type');
            $table->string('status')->default('active');
            $table->timestamp('started_at')->nullable();
            $table->text('progress')->nullable();
            $table->timestamps();
        });

        Setting::query()->create([
            'key' => 'feature_events',
            'value' => '1',
            'group' => 'features',
        ]);
    }

    protected function tearDown(): void
    {
        DB::disconnect('sqlite');
        Setting::flushRuntimeCache();
        config()->set('database.default', $this->originalDefaultConnection);
        config()->set('database.connections.sqlite.database', $this->originalSqliteDatabase);

        if (file_exists($this->sqlitePath)) {
            @unlink($this->sqlitePath);
        }

        parent::tearDown();
    }

    public function test_free_event_creates_paid_order_and_registration(): void
    {
        $seller = User::create([
            'name' => 'Seller',
            'email' => 'seller-event@test.com',
            'role' => 'admin',
            'level' => 'superadmin',
        ]);

        $buyer = User::create([
            'name' => 'Buyer',
            'email' => 'buyer-event@test.com',
            'role' => 'member',
            'level' => 'iniciante',
        ]);

        $event = Event::create([
            'user_id' => $seller->id,
            'title' => 'Evento Gratuito',
            'description' => 'Evento livre',
            'start_at' => now()->addDay(),
            'published' => true,
            'price' => 0,
            'is_ticket_enabled' => true,
        ]);

        $response = $this
            ->actingAs($buyer)
            ->post(route('events.reserve', $event), ['quantity' => 1]);

        $response->assertRedirect(route('events.show', $event));

        $order = Order::query()->first();
        $this->assertNotNull($order);
        $this->assertSame('paid', $order->status);
        $this->assertSame(0.0, (float) $order->total_amount);
        $this->assertSame('event', $order->items()->first()->item_type);

        $registration = EventRegistration::query()->first();
        $this->assertNotNull($registration);
        $this->assertSame($order->id, $registration->order_id);
        $this->assertSame(EventRegistration::STATUS_PAID, $registration->status);
    }

    public function test_paid_event_coupon_confirms_free_registration_and_consumes_coupon(): void
    {
        $seller = User::create([
            'name' => 'Seller Coupon',
            'email' => 'seller-event-coupon@test.com',
            'role' => 'admin',
            'level' => 'superadmin',
        ]);

        $buyer = User::create([
            'name' => 'Buyer Coupon',
            'email' => 'buyer-event-coupon@test.com',
            'role' => 'member',
            'level' => 'iniciante',
        ]);

        $event = Event::create([
            'user_id' => $seller->id,
            'title' => 'Evento Pago com Cupom',
            'description' => 'Evento pago liberado por cupom',
            'start_at' => now()->addDay(),
            'published' => true,
            'price' => 150,
            'is_ticket_enabled' => true,
        ]);

        $coupon = EventCoupon::create([
            'event_id' => $event->id,
            'code' => 'CONVIDADO100',
            'type' => EventCoupon::TYPE_FREE,
            'discount_value' => 100,
            'max_uses' => 3,
            'active' => true,
            'created_by' => $seller->id,
        ]);

        $response = $this
            ->actingAs($buyer)
            ->post(route('events.reserve', $event), [
                'quantity' => 2,
                'coupon_code' => 'convidado100',
            ]);

        $response->assertRedirect(route('events.show', $event));
        $response->assertSessionHas('success');

        $order = Order::query()->where('user_id', $buyer->id)->first();
        $this->assertNotNull($order);
        $this->assertSame('paid', $order->status);
        $this->assertSame('free', $order->gateway);
        $this->assertSame(0.0, (float) $order->total_amount);
        $this->assertSame('CONVIDADO100', data_get($order->metadata, 'event_coupon.code'));
        $this->assertSame(300.0, (float) data_get($order->metadata, 'event_coupon.discount_amount'));

        $this->assertSame(2, (int) $coupon->fresh()->used_count);
        $this->assertSame(2, EventRegistration::query()->where('event_id', $event->id)->where('user_id', $buyer->id)->count());
        $this->assertSame(2, EventRegistration::query()
            ->where('event_id', $event->id)
            ->where('user_id', $buyer->id)
            ->where('coupon_id', $coupon->id)
            ->where('status', EventRegistration::STATUS_PAID)
            ->where('payment_status', EventRegistration::PAYMENT_FREE)
            ->count());
    }

    public function test_confirmed_registration_can_open_event_whatsapp_group(): void
    {
        $user = User::create([
            'name' => 'Buyer Group',
            'email' => 'buyer-event-group@test.com',
            'role' => 'member',
            'level' => 'iniciante',
        ]);

        $event = Event::create([
            'title' => 'Evento com Grupo',
            'description' => 'Evento com grupo privado',
            'start_at' => now()->addDay(),
            'published' => true,
            'price' => 0,
            'whatsapp_group_link' => 'https://chat.whatsapp.com/teste123',
        ]);

        $order = Order::create([
            'user_id' => $user->id,
            'status' => 'paid',
            'total_amount' => 0,
            'currency' => 'BRL',
            'gateway' => 'free',
        ]);

        $registration = EventRegistration::create([
            'event_id' => $event->id,
            'user_id' => $user->id,
            'order_id' => $order->id,
            'status' => EventRegistration::STATUS_PAID,
            'payment_status' => EventRegistration::PAYMENT_FREE,
            'price' => 0,
            'quantity' => 1,
        ]);

        $html = view('events.payment.success', compact('order', 'event', 'registration'))->render();
        $this->assertStringContainsString('Entrar no grupo do evento', $html);

        $response = $this
            ->actingAs($user)
            ->post(route('events.group.join', $event));

        $response->assertRedirect('https://chat.whatsapp.com/teste123');
        $this->assertNotNull($registration->fresh()->joined_group_at);
    }

    public function test_free_mentorship_creates_paid_order_and_enrollment(): void
    {
        $seller = User::create([
            'name' => 'Seller',
            'email' => 'seller-mentorship@test.com',
            'role' => 'admin',
            'level' => 'superadmin',
        ]);

        $buyer = User::create([
            'name' => 'Buyer',
            'email' => 'buyer-mentorship@test.com',
            'role' => 'member',
            'level' => 'iniciante',
        ]);

        $mentorship = Mentorship::create([
            'mentor_id' => $seller->id,
            'title' => 'Mentoria Gratuita',
            'description' => 'Sessao gratuita',
            'price' => 0,
        ]);

        $response = $this
            ->actingAs($buyer)
            ->post(route('mentorships.checkout.process', $mentorship), []);

        $response->assertRedirect(route('mentorships.show', $mentorship));

        $order = Order::query()->where('user_id', $buyer->id)->latest('id')->first();
        $this->assertNotNull($order);
        $this->assertSame('paid', $order->status);
        $this->assertSame(0.0, (float) $order->total_amount);
        $this->assertSame('mentorship', $order->items()->first()->item_type);

        $this->assertDatabaseHas('enrollments', [
            'user_id' => $buyer->id,
            'enrollable_id' => $mentorship->id,
            'enrollable_type' => Mentorship::class,
            'status' => 'active',
        ]);
    }

    public function test_free_course_creates_paid_order_and_enrollment(): void
    {
        $seller = User::create([
            'name' => 'Seller',
            'email' => 'seller-course@test.com',
            'role' => 'admin',
            'level' => 'superadmin',
        ]);

        $buyer = User::create([
            'name' => 'Buyer',
            'email' => 'buyer-course@test.com',
            'role' => 'member',
            'level' => 'iniciante',
        ]);
        $buyer->forceFill(['email_verified_at' => now()])->save();

        $course = Course::create([
            'user_id' => $seller->id,
            'title' => 'Curso Gratuito',
            'slug' => 'curso-gratuito',
            'price' => 0,
        ]);

        $response = $this
            ->actingAs($buyer)
            ->post(route('checkout.process', $course), []);

        $response->assertRedirect(route('courses.show', $course->slug ?: $course->id));

        $order = Order::query()->where('user_id', $buyer->id)->latest('id')->first();
        $this->assertNotNull($order);
        $this->assertSame('paid', $order->status);
        $this->assertSame(0.0, (float) $order->total_amount);
        $this->assertSame('course', $order->items()->first()->item_type);

        $this->assertDatabaseHas('enrollments', [
            'user_id' => $buyer->id,
            'enrollable_id' => $course->id,
            'enrollable_type' => Course::class,
            'status' => 'active',
        ]);
    }
}
