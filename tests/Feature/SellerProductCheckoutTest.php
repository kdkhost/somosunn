<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\SellerProduct;
use App\Models\SellerStore;
use App\Models\Setting;
use App\Models\User;
use App\Services\LegalConsentService;
use App\Services\Marketplace\CorreiosShippingService;
use App\Services\Marketplace\SellerProductCartService;
use App\Services\Marketplace\SellerStoreService;
use App\Services\OrderSettlementService;
use App\Services\Payment\MercadoPagoService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class SellerProductCheckoutTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Queue::fake();
        Notification::fake();
        Setting::flushRuntimeCache();
    }

    public function test_cart_rejects_items_from_different_sellers(): void
    {
        $firstSeller = $this->createSeller('seller-cart-1@example.com');
        $firstStore = $this->publishStoreFor($firstSeller, 'Loja Um', 'loja-um');
        $firstProduct = $this->createDigitalProduct($firstSeller, $firstStore, 'Produto Um', 'produto-um', 29.90);

        $secondSeller = $this->createSeller('seller-cart-2@example.com');
        $secondStore = $this->publishStoreFor($secondSeller, 'Loja Dois', 'loja-dois');
        $secondProduct = $this->createDigitalProduct($secondSeller, $secondStore, 'Produto Dois', 'produto-dois', 39.90);

        $this->bindSellerStoreService();

        $cartService = app(SellerProductCartService::class);
        session()->start();

        $this->assertSame('added', $cartService->add($firstProduct, 1)['status']);
        $result = $cartService->add($secondProduct, 1);

        $this->assertSame('conflict', $result['status']);

        session()->flash('cart_replace_candidate', [
            'product_id' => $secondProduct->id,
            'title' => $secondProduct->title,
            'add_url' => route('seller-products.cart.add', $secondProduct),
        ]);

        $cartResponse = $this->get(route('seller-products.cart.show'));
        $cartResponse->assertOk();
        $cartResponse->assertSee('Produto Um');
        $cartResponse->assertSee('Substituir carrinho pelo produto Produto Dois');
    }

    public function test_physical_product_checkout_persists_shipment_and_marks_paid_when_order_is_free(): void
    {
        $seller = $this->createSeller('seller-physical@example.com');
        $seller->forceFill([
            'cep' => '20000000',
            'address' => 'Rua do Seller',
            'number' => '100',
            'city' => 'Rio de Janeiro',
            'state' => 'RJ',
        ])->save();

        $store = $this->publishStoreFor($seller, 'Loja Fisica', 'loja-fisica');
        $product = SellerProduct::create([
            'seller_store_id' => $store->id,
            'user_id' => $seller->id,
            'slug' => 'camiseta-premium',
            'type' => 'physical',
            'title' => 'Camiseta Premium',
            'price' => 0,
            'stock' => 5,
            'weight_grams' => 500,
            'height_cm' => 4,
            'width_cm' => 20,
            'length_cm' => 30,
            'status' => 'published',
            'published_at' => now(),
        ]);

        $buyer = $this->createBuyer('buyer-physical@example.com');
        $this->bindSellerStoreService();

        $this->mock(CorreiosShippingService::class, function ($mock) {
            $mock->shouldReceive('quote')
                ->once()
                ->andReturn([
                    [
                        'service_code' => '03220',
                        'service_name' => 'SEDEX',
                        'amount' => 0.0,
                        'delivery_days' => 3,
                        'payload' => ['mock' => true],
                    ],
                ]);
        });

        $response = $this->actingAs($buyer)
            ->withSession($this->cartSessionPayload($seller, $store, $product))
            ->post(route('seller-products.checkout.process'), [
                'recipient_name' => 'Comprador Fisico',
                'recipient_email' => 'buyer-physical@example.com',
                'recipient_phone' => '21999999999',
                'postal_code' => '22041001',
                'address_line' => 'Av Atlantica',
                'number' => '500',
                'complement' => 'Apto 10',
                'neighborhood' => 'Copacabana',
                'city' => 'Rio de Janeiro',
                'state' => 'RJ',
                'shipping_service_code' => '03220',
            ]);

        $order = Order::query()->with(['items', 'shipment'])->latest('id')->first();

        $response->assertRedirect(route('checkout.success', $order));
        $this->assertNotNull($order);
        $this->assertSame('paid', $order->status);
        $this->assertSame('free', $order->gateway);
        $this->assertSame('seller_product', data_get($order->metadata, 'sale_type'));
        $this->assertSame(1, $order->items()->where('item_type', 'seller_product')->count());
        $this->assertNotNull($order->shipment);
        $this->assertSame('03220', $order->shipment->service_code);
        $this->assertSame('SEDEX', $order->shipment->service_name);
        $this->assertSame(4, (int) $product->fresh()->stock);
    }

    public function test_digital_product_checkout_creates_pending_order_and_download_is_available_after_payment(): void
    {
        Setting::set('mercadopago_env', 'production');
        Setting::set('mercadopago_prod_public_key', 'APP_USR-TEST-PUBLIC');
        Setting::set('mercadopago_prod_access_token', 'APP_USR-TEST-TOKEN');

        $seller = $this->createSeller('seller-digital@example.com');
        $store = $this->publishStoreFor($seller, 'Loja Digital', 'loja-digital');
        $product = $this->createDigitalProduct($seller, $store, 'Curso Pocket', 'curso-pocket', 79.90);

        $buyer = $this->createBuyer('buyer-digital@example.com');
        $this->bindSellerStoreService();

        $this->mock(MercadoPagoService::class, function ($mock) {
            $mock->shouldReceive('createPreference')
                ->once()
                ->andReturn([
                    'id' => 'pref-test-123',
                    'init_point' => 'https://example.com/init',
                    'sandbox_init_point' => 'https://example.com/sandbox',
                ]);
        });

        $response = $this->actingAs($buyer)
            ->withSession($this->cartSessionPayload($seller, $store, $product))
            ->post(route('seller-products.checkout.process'), [
                'gateway_provider' => 'mercadopago',
            ]);

        $order = Order::query()->with('items')->latest('id')->first();

        $response->assertOk();
        $this->assertNotNull($order);
        $this->assertSame('pending', $order->status);
        $this->assertSame('mercadopago', $order->gateway);
        $this->assertSame('marketplace', data_get($order->metadata, 'context'));
        $this->assertSame('seller_product', data_get($order->metadata, 'sale_type'));
        $this->assertSame('loja-digital', data_get($order->metadata, 'store.slug'));
        $this->assertSame('curso-pocket', data_get($order->items->first(), 'data.product_slug'));

        app(OrderSettlementService::class)->settleAsPaid($order, [
            'transaction_id' => 'DIGITAL-TEST-001',
            'payment_method' => 'pix',
        ]);

        $downloadResponse = $this->actingAs($buyer)
            ->get(route('panel.purchases.download', [$order->fresh(), $order->items()->first()]));

        $downloadResponse->assertRedirect('https://example.com/curso-pocket');
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

    private function createBuyer(string $email): User
    {
        $user = User::create([
            'name' => 'Buyer ' . substr(md5($email), 0, 6),
            'email' => $email,
            'password' => Hash::make('password123'),
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

    private function createDigitalProduct(User $seller, SellerStore $store, string $title, string $slug, float $price): SellerProduct
    {
        return SellerProduct::create([
            'seller_store_id' => $store->id,
            'user_id' => $seller->id,
            'slug' => $slug,
            'type' => 'digital',
            'title' => $title,
            'excerpt' => 'Produto digital de teste.',
            'price' => $price,
            'status' => 'published',
            'digital_delivery_type' => 'url',
            'digital_url' => 'https://example.com/' . $slug,
            'published_at' => now(),
        ]);
    }

    private function cartSessionPayload(User $seller, SellerStore $store, SellerProduct $product, int $quantity = 1): array
    {
        return [
            'seller_product_cart_v1' => [
                'seller_id' => $seller->id,
                'store_id' => $store->id,
                'items' => [
                    $product->id => [
                        'product_id' => $product->id,
                        'quantity' => $quantity,
                    ],
                ],
            ],
        ];
    }

    private function bindSellerStoreService(): void
    {
        $service = \Mockery::mock(SellerStoreService::class);
        $service->shouldReceive('isPubliclyAvailable')->andReturn(true);

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
