<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Plan;
use App\Models\ReferralLinkEvent;
use App\Models\ReferralLinkVisit;
use App\Models\User;
use App\Services\AffiliateTrackingService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class AffiliateTrackingServiceTest extends TestCase
{
    private string $sqlitePath;

    protected function setUp(): void
    {
        parent::setUp();

        $this->sqlitePath = database_path('testing-affiliate-tracking.sqlite');

        if (file_exists($this->sqlitePath)) {
            @unlink($this->sqlitePath);
        }

        touch($this->sqlitePath);

        config()->set('database.default', 'sqlite');
        config()->set('database.connections.sqlite.database', $this->sqlitePath);
        config()->set('session.driver', 'array');

        DB::purge('sqlite');
        DB::reconnect('sqlite');

        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password');
            $table->string('referral_code', 20)->nullable()->unique();
            $table->unsignedBigInteger('referred_by')->nullable();
            $table->timestamps();
        });

        Schema::create('plans', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->nullable();
            $table->decimal('price', 10, 2)->default(0);
            $table->boolean('is_free')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('seller_id')->nullable();
            $table->string('status')->nullable();
            $table->timestamp('paid_at')->nullable();
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
            $table->json('metadata')->nullable();
            $table->string('transaction_id')->nullable();
            $table->timestamp('refunded_at')->nullable();
            $table->timestamps();
        });

        Schema::create('referral_link_visits', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('referrer_user_id')->nullable();
            $table->string('referral_code', 20)->nullable();
            $table->string('session_id', 120)->nullable();
            $table->string('visitor_token', 64)->nullable();
            $table->unsignedBigInteger('registered_user_id')->nullable();
            $table->string('landing_page_path', 255)->nullable();
            $table->text('landing_page_url')->nullable();
            $table->string('first_page_path', 255)->nullable();
            $table->text('first_page_url')->nullable();
            $table->string('last_page_path', 255)->nullable();
            $table->text('last_page_url')->nullable();
            $table->text('referrer_url')->nullable();
            $table->string('utm_source', 120)->nullable();
            $table->string('utm_medium', 120)->nullable();
            $table->string('utm_campaign', 120)->nullable();
            $table->string('utm_content', 120)->nullable();
            $table->string('utm_term', 120)->nullable();
            $table->string('ip_hash', 64)->nullable();
            $table->text('user_agent')->nullable();
            $table->string('country', 10)->nullable();
            $table->string('region', 120)->nullable();
            $table->string('city', 120)->nullable();
            $table->unsignedInteger('clicks_count')->default(0);
            $table->unsignedInteger('pageviews_count')->default(0);
            $table->unsignedInteger('checkout_started_count')->default(0);
            $table->unsignedInteger('purchases_count')->default(0);
            $table->timestamp('first_visited_at')->nullable();
            $table->timestamp('last_visited_at')->nullable();
            $table->timestamp('registered_at')->nullable();
            $table->timestamp('first_checkout_started_at')->nullable();
            $table->timestamp('first_purchase_at')->nullable();
            $table->timestamp('last_purchase_at')->nullable();
            $table->unsignedBigInteger('first_order_id')->nullable();
            $table->unsignedBigInteger('first_paid_order_id')->nullable();
            $table->unsignedBigInteger('first_plan_id')->nullable();
            $table->decimal('total_revenue_amount', 12, 2)->default(0);
            $table->timestamps();
        });

        Schema::create('referral_link_events', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('referral_link_visit_id')->nullable();
            $table->unsignedBigInteger('referrer_user_id')->nullable();
            $table->unsignedBigInteger('actor_user_id')->nullable();
            $table->unsignedBigInteger('registered_user_id')->nullable();
            $table->string('event_type', 40);
            $table->string('channel', 40)->nullable();
            $table->string('page_path', 255)->nullable();
            $table->text('page_url')->nullable();
            $table->unsignedBigInteger('order_id')->nullable();
            $table->unsignedBigInteger('plan_id')->nullable();
            $table->decimal('amount', 12, 2)->nullable();
            $table->json('metadata')->nullable();
            $table->timestamp('occurred_at')->nullable();
            $table->timestamps();
        });
    }

    protected function tearDown(): void
    {
        DB::disconnect('sqlite');

        if (isset($this->sqlitePath) && file_exists($this->sqlitePath)) {
            @unlink($this->sqlitePath);
        }

        parent::tearDown();
    }

    public function test_service_tracks_clicks_visits_registration_checkout_purchase_and_shares(): void
    {
        $tracking = app(AffiliateTrackingService::class);

        $referrer = User::create([
            'name' => 'Afiliado',
            'email' => 'afiliado@example.com',
            'password' => Hash::make('password'),
            'referral_code' => 'UNNTESTE',
        ]);

        $request = Request::create('/register?ref=UNNTESTE&utm_source=whatsapp', 'GET');
        $request->headers->set('user-agent', 'PHPUnit');
        $session = app('session')->driver();
        $session->start();
        $request->setLaravelSession($session);

        $tracking->captureIncomingReferral($request);
        $tracking->trackPageView($request, response('ok', 200, ['Content-Type' => 'text/html']));

        $visit = ReferralLinkVisit::query()->first();
        $this->assertNotNull($visit);
        $this->assertSame($referrer->id, (int) $visit->referrer_user_id);
        $this->assertSame(1, (int) $visit->clicks_count);
        $this->assertSame(1, (int) $visit->pageviews_count);
        $this->assertSame('whatsapp', $visit->utm_source);
        $this->assertEqualsCanonicalizing(
            ['click', 'visit'],
            ReferralLinkEvent::query()->pluck('event_type')->all()
        );

        $lead = User::create([
            'name' => 'Lead',
            'email' => 'lead@example.com',
            'password' => Hash::make('password'),
        ]);

        $tracking->attachRegisteredUser($request, $lead, 'UNNTESTE');

        $visit->refresh();
        $lead->refresh();

        $this->assertSame($referrer->id, (int) $lead->referred_by);
        $this->assertSame($lead->id, (int) $visit->registered_user_id);
        $this->assertSame(1, ReferralLinkEvent::query()->where('event_type', 'register')->count());

        $plan = Plan::create([
            'name' => 'Plano Premium',
            'slug' => 'premium',
            'price' => 199.90,
            'is_free' => false,
            'is_active' => true,
        ]);

        $order = Order::create([
            'user_id' => $lead->id,
            'status' => 'pending',
            'total_amount' => 199.90,
            'fee_amount' => 0,
            'platform_fee_amount' => 0,
            'currency' => 'BRL',
            'gateway' => 'mercadopago',
            'metadata' => [],
        ]);

        $tracking->recordCheckoutStarted($request, $order, $plan);

        $order->refresh();
        $this->assertSame($visit->id, (int) data_get($order->metadata, 'referral_tracking.visit_id'));
        $this->assertSame(1, ReferralLinkEvent::query()->where('event_type', 'checkout_started')->count());

        $order->update([
            'status' => 'paid',
            'paid_at' => now(),
        ]);

        $tracking->recordPaidOrder($order, $plan);

        $visit->refresh();
        $this->assertSame(1, (int) $visit->checkout_started_count);
        $this->assertSame(1, (int) $visit->purchases_count);
        $this->assertSame('199.90', number_format((float) $visit->total_revenue_amount, 2, '.', ''));
        $this->assertSame(1, ReferralLinkEvent::query()->where('event_type', 'purchase')->count());

        $this->assertSame('share', $tracking->recordShareAction($referrer, 'share', 'whatsapp', ['context' => 'test']));
        $this->assertSame('reshare', $tracking->recordShareAction($referrer, 'share', 'whatsapp', ['context' => 'test']));
        $this->assertSame('copy', $tracking->recordShareAction($referrer, 'copy', 'copy', ['context' => 'test']));

        $this->assertSame(1, ReferralLinkEvent::query()->where('event_type', 'share')->count());
        $this->assertSame(1, ReferralLinkEvent::query()->where('event_type', 'reshare')->count());
        $this->assertSame(1, ReferralLinkEvent::query()->where('event_type', 'copy')->count());
        $this->assertSame(8, ReferralLinkEvent::query()->count());
    }
}
