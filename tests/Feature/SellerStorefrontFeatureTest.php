<?php

namespace Tests\Feature;

use App\Models\SellerProduct;
use App\Models\SellerStore;
use App\Models\User;
use App\Services\LegalConsentService;
use App\Services\Marketplace\SellerStoreService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class SellerStorefrontFeatureTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Queue::fake();
    }

    public function test_published_store_and_product_appear_on_marketplace_with_store_cta(): void
    {
        $seller = $this->createSeller('seller-marketplace@example.com');
        $store = $this->publishStoreFor($seller, 'Loja Solar', 'loja-solar');

        SellerProduct::create([
            'seller_store_id' => $store->id,
            'user_id' => $seller->id,
            'slug' => 'ebook-solar',
            'type' => 'digital',
            'title' => 'Ebook Solar',
            'excerpt' => 'Guia pratico para energia solar.',
            'price' => 49.90,
            'status' => 'published',
            'digital_delivery_type' => 'url',
            'digital_url' => 'https://example.com/ebook-solar',
            'published_at' => now(),
        ]);

        $this->bindSellerStoreService([
            'publishedStoresByUserIds' => collect([$seller->id => $store]),
        ]);

        $response = $this->get(route('marketplace.index'));

        $response->assertOk();
        $response->assertSee('Ebook Solar');
        $response->assertSee('Anunciado e vendido por');
        $response->assertSee('Ver mais desse vendedor');
        $response->assertSee(route('seller-stores.show', $store->slug), false);
    }

    public function test_store_route_returns_404_when_seller_loses_active_plan(): void
    {
        $seller = $this->createSeller('seller-store@example.com');
        $store = $this->publishStoreFor($seller, 'Marca Viva', 'marca-viva');

        $this->bindSellerStoreService([
            'isPubliclyAvailable' => true,
            'storefrontPayload' => [
                'products' => collect(),
                'courses' => collect(),
                'mentorships' => collect(),
                'events' => collect(),
            ],
        ]);
        $this->get(route('seller-stores.show', $store->slug))->assertOk();

        $this->bindSellerStoreService([
            'isPubliclyAvailable' => false,
        ]);

        $this->get(route('seller-stores.show', $store->slug))->assertNotFound();
    }

    public function test_superadmin_platform_store_remains_public_without_active_plan(): void
    {
        $superAdmin = User::create([
            'name' => 'Super Admin',
            'email' => 'superadmin-store@example.com',
            'password' => Hash::make('password123'),
            'role' => 'superadmin',
        ]);

        $store = app(SellerStoreService::class)->ensureForUser($superAdmin);

        $store->forceFill([
            'is_published' => true,
            'published_at' => now(),
        ])->save();

        $this->assertTrue($store->fresh()->is_platform_store);
        $this->assertNotEmpty($store->fresh()->slug);

        $this->get(route('seller-stores.show', $store->slug))
            ->assertOk()
            ->assertSee($store->brand_name);
    }

    public function test_store_slug_cannot_use_reserved_keyword_or_change_after_publish(): void
    {
        $seller = $this->createSeller('seller-slug@example.com');

        $this->actingAs($seller)
            ->from(route('panel.marketplace.store.edit'))
            ->post(route('panel.marketplace.store.update'), [
                'brand_name' => 'Minha Marca',
                'slug' => 'checkout',
                'is_published' => 1,
            ])
            ->assertRedirect(route('panel.marketplace.store.edit'));

        $reservedAttempt = SellerStore::where('user_id', $seller->id)->first();
        $this->assertTrue($reservedAttempt === null || $reservedAttempt->slug === null);

        $this->actingAs($seller)
            ->post(route('panel.marketplace.store.update'), [
                'brand_name' => 'Minha Marca',
                'slug' => 'minha-marca',
                'is_published' => 1,
            ])
            ->assertRedirect(route('panel.marketplace.store.edit'));

        $store = SellerStore::where('user_id', $seller->id)->firstOrFail();

        $this->assertSame('minha-marca', $store->slug);
        $this->assertNotNull($store->slug_locked_at);
        $this->assertTrue($store->is_published);

        $this->actingAs($seller)
            ->from(route('panel.marketplace.store.edit'))
            ->post(route('panel.marketplace.store.update'), [
                'brand_name' => 'Minha Marca',
                'slug' => 'slug-alterado',
                'is_published' => 1,
            ])
            ->assertRedirect(route('panel.marketplace.store.edit'));

        $this->assertSame('minha-marca', $store->fresh()->slug);
    }

    public function test_admin_can_list_marketplace_stores_and_products(): void
    {
        $admin = User::create([
            'name' => 'Admin Marketplace',
            'email' => 'admin-marketplace@example.com',
            'password' => Hash::make('password123'),
            'role' => 'admin',
        ]);

        $seller = $this->createSeller('seller-admin-view@example.com');
        $store = $this->publishStoreFor($seller, 'Atelie Azul', 'atelie-azul');

        SellerProduct::create([
            'seller_store_id' => $store->id,
            'user_id' => $seller->id,
            'slug' => 'planner-premium',
            'type' => 'digital',
            'title' => 'Planner Premium',
            'price' => 59.90,
            'status' => 'published',
            'digital_delivery_type' => 'url',
            'digital_url' => 'https://example.com/planner-premium',
            'published_at' => now(),
        ]);

        $this->actingAs($admin)
            ->get(route('panel.admin.marketplace.stores.index'))
            ->assertOk()
            ->assertSee('Atelie Azul');

        $this->actingAs($admin)
            ->get(route('panel.admin.marketplace.products.index'))
            ->assertOk()
            ->assertSee('Planner Premium');
    }

    private function createSeller(string $email): User
    {
        $user = User::create([
            'name' => 'Seller ' . substr(md5($email), 0, 6),
            'email' => $email,
            'password' => Hash::make('password123'),
            'role' => 'admin',
            'extra_features' => ['marketplace.sell', 'marketplace.buy'],
        ]);

        $this->acceptLgpd($user);

        return $user;
    }

    private function publishStoreFor(User $seller, string $brandName, string $slug): SellerStore
    {
        return SellerStore::create([
            'user_id' => $seller->id,
            'slug' => $slug,
            'brand_name' => $brandName,
            'support_email' => $seller->email,
            'primary_color' => '#1F5EDB',
            'accent_color' => '#0F172A',
            'is_published' => true,
            'published_at' => now(),
            'slug_locked_at' => now(),
        ]);
    }

    private function bindSellerStoreService(array $overrides = []): void
    {
        $service = \Mockery::mock(SellerStoreService::class);
        $service->shouldReceive('publishedStoresByUserIds')
            ->andReturn($overrides['publishedStoresByUserIds'] ?? collect());
        $service->shouldReceive('isPubliclyAvailable')
            ->andReturn($overrides['isPubliclyAvailable'] ?? true);
        $service->shouldReceive('storefrontPayload')
            ->andReturn($overrides['storefrontPayload'] ?? [
                'products' => collect(),
                'courses' => collect(),
                'mentorships' => collect(),
                'events' => collect(),
            ]);

        $this->app->instance(SellerStoreService::class, $service);
    }

    private function acceptLgpd(User $user): void
    {
        $user->forceFill([
            'lgpd_accepted_at' => now(),
            'lgpd_version' => app(LegalConsentService::class)->currentVersion(),
            'lgpd_accept_ip' => '127.0.0.1',
            'lgpd_accept_user_agent' => 'phpunit',
        ])->save();
    }
}
