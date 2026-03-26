<?php

namespace Tests\Feature;

use App\Models\RedeemableItem;
use App\Models\SellerProduct;
use App\Models\SellerStore;
use App\Models\User;
use App\Services\LegalConsentService;
use App\Services\Marketplace\SellerStoreService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class SellerProductChannelsFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_store_and_points_channel_creates_linked_redeemable_item(): void
    {
        $seller = $this->createSeller('seller-channels@example.com');

        $this->actingAs($seller)
            ->post(route('panel.marketplace.products.store'), [
                'title' => 'Curso Premium PDF',
                'type' => 'digital',
                'sales_channel' => 'store_and_points',
                'status' => 'published',
                'price' => 99.90,
                'digital_url' => 'https://example.com/produto-premium',
                'description' => '<p>Descricao completa</p>',
            ])
            ->assertRedirect();

        $product = SellerProduct::query()->firstOrFail();
        $redeemableItem = RedeemableItem::query()->where('seller_product_id', $product->id)->first();

        $this->assertSame('store_and_points', $product->sales_channel);
        $this->assertNotNull($redeemableItem);
        $this->assertTrue((bool) $redeemableItem->is_active);
        $this->assertSame($seller->id, (int) $redeemableItem->provider_user_id);
        $this->assertSame('Curso Premium PDF', $redeemableItem->name);
    }

    public function test_external_only_channel_requires_url_and_deactivates_points_item(): void
    {
        $seller = $this->createSeller('seller-external@example.com');

        $this->actingAs($seller)
            ->post(route('panel.marketplace.products.store'), [
                'title' => 'Mentoria Black',
                'type' => 'digital',
                'sales_channel' => 'store_and_points',
                'status' => 'published',
                'price' => 149.90,
                'digital_url' => 'https://example.com/mentoria-black',
            ])
            ->assertRedirect();

        $product = SellerProduct::query()->firstOrFail();

        $this->actingAs($seller)
            ->from(route('panel.marketplace.products.edit', $product))
            ->put(route('panel.marketplace.products.update', $product), [
                'title' => 'Mentoria Black',
                'type' => 'digital',
                'sales_channel' => 'external_only',
                'status' => 'published',
                'price' => 149.90,
            ])
            ->assertRedirect(route('panel.marketplace.products.edit', $product))
            ->assertSessionHasErrors('external_checkout_url');

        $this->actingAs($seller)
            ->put(route('panel.marketplace.products.update', $product), [
                'title' => 'Mentoria Black',
                'type' => 'digital',
                'sales_channel' => 'external_only',
                'status' => 'published',
                'price' => 149.90,
                'external_checkout_url' => 'https://externo.example.com/mentoria-black',
            ])
            ->assertRedirect(route('panel.marketplace.products.edit', $product));

        $product->refresh();
        $redeemableItem = RedeemableItem::query()->firstOrFail();

        $this->assertSame('external_only', $product->sales_channel);
        $this->assertSame('https://externo.example.com/mentoria-black', $product->external_checkout_url);
        $this->assertFalse((bool) $redeemableItem->is_active);
    }

    public function test_points_only_product_cannot_be_added_to_store_cart(): void
    {
        $seller = $this->createSeller('seller-cart@example.com');
        $store = SellerStore::create([
            'user_id' => $seller->id,
            'slug' => 'seller-cart',
            'brand_name' => 'Seller Cart',
            'primary_color' => '#1F5EDB',
            'accent_color' => '#0F172A',
            'is_published' => true,
            'published_at' => now(),
            'slug_locked_at' => now(),
        ]);

        $product = SellerProduct::create([
            'seller_store_id' => $store->id,
            'user_id' => $seller->id,
            'slug' => 'produto-pontos',
            'type' => 'digital',
            'sales_channel' => 'points_only',
            'title' => 'Produto por Pontos',
            'price' => 59.90,
            'status' => 'published',
            'published_at' => now(),
        ]);

        $service = \Mockery::mock(SellerStoreService::class);
        $service->shouldReceive('isPubliclyAvailable')->andReturn(true);
        $this->app->instance(SellerStoreService::class, $service);

        $this->from(route('seller-stores.products.show', ['seller-cart', 'produto-pontos']))
            ->post(route('seller-products.cart.add', $product))
            ->assertRedirect(route('seller-stores.products.show', ['seller-cart', 'produto-pontos']))
            ->assertSessionHas('error');
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

        $user->forceFill([
            'lgpd_accepted_at' => now(),
            'lgpd_version' => app(LegalConsentService::class)->currentVersion(),
            'lgpd_accept_ip' => '127.0.0.1',
            'lgpd_accept_user_agent' => 'phpunit',
        ])->save();

        return $user;
    }
}
