<?php

namespace Tests\Feature;

use App\Models\ReferralLinkEvent;
use App\Models\ReferralLinkVisit;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Routing\Middleware\ThrottleRequests;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class AffiliatePromoApiTest extends TestCase
{
    private string $sqlitePath;

    protected function setUp(): void
    {
        parent::setUp();

        $this->sqlitePath = database_path('testing-affiliate-promo-api.sqlite');

        if (file_exists($this->sqlitePath)) {
            @unlink($this->sqlitePath);
        }

        touch($this->sqlitePath);

        config()->set('database.default', 'sqlite');
        config()->set('database.connections.sqlite.database', $this->sqlitePath);

        DB::purge('sqlite');
        DB::reconnect('sqlite');
        $this->withoutMiddleware(ThrottleRequests::class);

        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password');
            $table->string('photo')->nullable();
            $table->string('role')->nullable();
            $table->string('level')->nullable();
            $table->string('referral_code', 20)->nullable()->unique();
            $table->unsignedBigInteger('referred_by')->nullable();
            $table->unsignedBigInteger('plan_id')->nullable();
            $table->timestamp('plan_expires_at')->nullable();
            $table->timestamps();
        });

        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->string('group')->nullable();
            $table->timestamps();
        });

        Schema::create('site_contents', function (Blueprint $table) {
            $table->id();
            $table->string('slug');
            $table->string('key');
            $table->longText('value')->nullable();
            $table->string('type')->default('text');
            $table->timestamps();
        });

        Schema::create('plans', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->nullable();
            $table->decimal('price', 10, 2)->default(0);
            $table->string('description')->nullable();
            $table->string('image')->nullable();
            $table->boolean('highlight')->default(false);
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_active')->default(true);
            $table->boolean('is_free')->default(false);
            $table->json('permissions')->nullable();
            $table->timestamps();
        });

        Schema::create('courses', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('title');
            $table->string('slug')->nullable();
            $table->decimal('price', 10, 2)->default(0);
            $table->decimal('flash_sale_price', 10, 2)->nullable();
            $table->timestamp('flash_sale_ends_at')->nullable();
            $table->string('thumbnail')->nullable();
            $table->text('short_description')->nullable();
            $table->longText('full_description')->nullable();
            $table->string('status')->nullable();
            $table->boolean('published')->default(false);
            $table->boolean('is_featured')->default(false);
            $table->timestamps();
        });

        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('title');
            $table->string('speaker')->nullable();
            $table->longText('description')->nullable();
            $table->string('image')->nullable();
            $table->timestamp('start_at')->nullable();
            $table->timestamp('end_at')->nullable();
            $table->decimal('price', 10, 2)->default(0);
            $table->decimal('flash_sale_price', 10, 2)->nullable();
            $table->timestamp('flash_sale_ends_at')->nullable();
            $table->boolean('published')->default(false);
            $table->timestamps();
        });

        Schema::create('mentorships', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->unsignedBigInteger('mentor_id')->nullable();
            $table->longText('description')->nullable();
            $table->string('image')->nullable();
            $table->decimal('price', 10, 2)->default(0);
            $table->decimal('flash_sale_price', 10, 2)->nullable();
            $table->timestamp('flash_sale_ends_at')->nullable();
            $table->timestamps();
        });

        Schema::create('testimonials', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('author_name')->nullable();
            $table->string('author_title')->nullable();
            $table->integer('rating')->default(5);
            $table->longText('content')->nullable();
            $table->string('status')->default('approved');
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('referral_link_visits', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('referrer_user_id')->nullable();
            $table->string('referral_code', 20)->nullable();
            $table->unsignedBigInteger('registered_user_id')->nullable();
            $table->string('landing_page_path', 255)->nullable();
            $table->text('landing_page_url')->nullable();
            $table->text('referrer_url')->nullable();
            $table->string('utm_source', 120)->nullable();
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
        Carbon::setTestNow();
        DB::disconnect('sqlite');

        if (isset($this->sqlitePath) && file_exists($this->sqlitePath)) {
            @unlink($this->sqlitePath);
        }

        parent::tearDown();
    }

    public function test_affiliate_api_exposes_materials_offers_and_landing_payload(): void
    {
        Carbon::setTestNow('2026-03-06 20:00:00');

        DB::table('settings')->insert([
            ['key' => 'app_name', 'value' => 'Somos UNN', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'logo_front', 'value' => 'uploads/logo-front.png', 'created_at' => now(), 'updated_at' => now()],
        ]);

        DB::table('site_contents')->insert([
            ['slug' => 'home', 'key' => 'hero_title', 'value' => 'Networking que gera negócios', 'type' => 'text', 'created_at' => now(), 'updated_at' => now()],
            ['slug' => 'home', 'key' => 'hero_subtitle', 'value' => 'Conecte-se com decisores e oportunidades reais.', 'type' => 'text', 'created_at' => now(), 'updated_at' => now()],
            ['slug' => 'home', 'key' => 'hero_text', 'value' => 'Use a comunidade para vender, aprender e crescer.', 'type' => 'text', 'created_at' => now(), 'updated_at' => now()],
            ['slug' => 'about', 'key' => 'manifesto', 'value' => 'Uma comunidade criada para acelerar relacionamento e negócios.', 'type' => 'text', 'created_at' => now(), 'updated_at' => now()],
            ['slug' => 'footer', 'key' => 'linkedin_url', 'value' => 'https://linkedin.com/company/somosunn', 'type' => 'text', 'created_at' => now(), 'updated_at' => now()],
        ]);

        $user = User::create([
            'name' => 'Afiliado API',
            'email' => 'afiliado-api@example.com',
            'password' => Hash::make('password'),
        ]);

        DB::table('plans')->insert([
            'name' => 'Plano Elite',
            'slug' => 'elite',
            'price' => 199.90,
            'description' => 'Plano premium com benefícios comerciais.',
            'is_featured' => true,
            'is_active' => true,
            'is_free' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('courses')->insert([
            'title' => 'Curso de Negociação',
            'slug' => 'curso-negociacao',
            'price' => 97.00,
            'short_description' => 'Aprenda a negociar com segurança.',
            'status' => 'published',
            'published' => true,
            'is_featured' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('events')->insert([
            'title' => 'Encontro de Networking',
            'description' => 'Evento com empresários e investidores.',
            'price' => 49.90,
            'published' => true,
            'start_at' => now()->addWeek(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('mentorships')->insert([
            'title' => 'Mentoria Comercial',
            'description' => 'Mentoria para escalar reuniões e fechamento.',
            'price' => 297.00,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('testimonials')->insert([
            'author_name' => 'Cliente Prova',
            'author_title' => 'Empresário',
            'rating' => 5,
            'content' => 'A comunidade ajudou a gerar novas vendas em poucos dias.',
            'status' => 'approved',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->actingAs($user, 'sanctum');

        $overview = $this->getJson('/api/v1/affiliate/overview')
            ->assertOk()
            ->assertJsonPath('branding.site_name', 'Somos UNN')
            ->assertJsonStructure([
                'referral' => ['code', 'register_url', 'home_url'],
                'branding' => ['hero_title', 'hero_subtitle', 'hero_text'],
                'summary',
                'api' => ['base_url', 'endpoints'],
            ]);

        $generatedCode = $overview->json('referral.code');
        $this->assertNotEmpty($generatedCode);

        $this->getJson('/api/v1/affiliate/materials')
            ->assertOk()
            ->assertJsonPath('materials.0.title', 'Convite rápido')
            ->assertJsonPath('social_links.linkedin', 'https://linkedin.com/company/somosunn');

        $this->getJson('/api/v1/affiliate/offers')
            ->assertOk()
            ->assertJsonPath('offers.plans.0.title', 'Plano Elite')
            ->assertJsonPath('offers.courses.0.title', 'Curso de Negociação');

        $this->getJson('/api/v1/affiliate/landing-page')
            ->assertOk()
            ->assertJsonPath('landing_page.hero.title', 'Networking que gera negócios')
            ->assertJsonCount(4, 'landing_page.benefits');
    }

    public function test_affiliate_api_exposes_analytics_for_external_dashboards(): void
    {
        Carbon::setTestNow('2026-03-06 20:30:00');

        $user = User::create([
            'name' => 'Afiliado Analytics API',
            'email' => 'afiliado-analytics-api@example.com',
            'password' => Hash::make('password'),
            'referral_code' => 'UNNAPI01',
        ]);

        $lead = User::create([
            'name' => 'Lead API',
            'email' => 'lead-api@example.com',
            'password' => Hash::make('password'),
            'referred_by' => $user->id,
        ]);

        $visit = ReferralLinkVisit::create([
            'referrer_user_id' => $user->id,
            'referral_code' => $user->referral_code,
            'registered_user_id' => $lead->id,
            'landing_page_path' => '/lp/externa',
            'landing_page_url' => 'https://externo.com/lp/externa',
            'referrer_url' => 'https://google.com/search?q=unn',
            'utm_source' => 'google',
            'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 Chrome/122.0.0.0 Safari/537.36',
            'country' => 'BR',
            'region' => 'RJ',
            'city' => 'Rio de Janeiro',
            'clicks_count' => 4,
            'pageviews_count' => 7,
            'checkout_started_count' => 1,
            'purchases_count' => 1,
            'total_revenue_amount' => 147.90,
            'first_visited_at' => now()->subMinutes(12),
            'registered_at' => now()->subMinutes(10),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        ReferralLinkEvent::create([
            'referral_link_visit_id' => $visit->id,
            'referrer_user_id' => $user->id,
            'registered_user_id' => $lead->id,
            'event_type' => 'purchase',
            'channel' => 'google',
            'page_path' => '/checkout/3',
            'page_url' => 'https://somosunn.com.br/checkout/3',
            'amount' => 147.90,
            'occurred_at' => now()->subMinutes(5),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->actingAs($user, 'sanctum');

        $this->getJson('/api/v1/affiliate/analytics?per_page=10&visit_limit=5')
            ->assertOk()
            ->assertJsonPath('summary.clicks', 4)
            ->assertJsonPath('summary.pageviews', 7)
            ->assertJsonPath('summary.purchases', 1)
            ->assertJsonPath('channel_funnels.0.channel', 'Google')
            ->assertJsonPath('detailed_events.meta.total', 1)
            ->assertJsonPath('detailed_events.data.0.event_label', 'Compra confirmada')
            ->assertJsonPath('detailed_events.data.0.location_label', 'Rio de Janeiro / RJ / BR');
    }
}
