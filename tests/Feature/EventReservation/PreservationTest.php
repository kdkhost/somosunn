<?php

namespace Tests\Feature\EventReservation;

use App\Models\Coupon;
use App\Models\Event;
use App\Models\EventRegistration;
use App\Models\GatewayAccount;
use App\Models\Order;
use App\Models\Setting;
use App\Models\User;
use App\Services\LegalConsentService;
use App\Services\Payment\MercadoPagoService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Mockery;
use Tests\TestCase;

/**
 * Preservation Property Tests - Mercado Pago and Free Events Unchanged
 * 
 * **Validates: Requirements 3.1, 3.2, 3.3, 3.4, 3.5, 3.6, 3.7, 3.8**
 * 
 * CRITICAL: These tests run on UNFIXED code to observe baseline behavior.
 * They MUST PASS on unfixed code - this confirms the behavior we need to preserve.
 * 
 * GOAL: Capture observed behavior patterns for:
 * - Mercado Pago active gateway events
 * - Free events
 * - Coupon application
 * - Platform fee calculation
 * 
 * These tests will continue to pass after the fix, ensuring no regressions.
 */
class PreservationTest extends TestCase
{
    use RefreshDatabase;

    private static int $userCounter = 0;
    private static int $eventCounter = 0;

    protected function setUp(): void
    {
        parent::setUp();

        Notification::fake();
        Setting::flushRuntimeCache();

        // Mock LegalConsentService to bypass LGPD consent check
        $legalConsentMock = Mockery::mock(LegalConsentService::class);
        $legalConsentMock->shouldReceive('hasAcceptedCurrentVersion')->andReturn(true);
        $this->app->instance(LegalConsentService::class, $legalConsentMock);

        // Enable feature_events
        Setting::updateOrCreate(['key' => 'feature_events'], ['value' => '1']);
    }

    protected function tearDown(): void
    {
        Setting::flushRuntimeCache();
        parent::tearDown();
    }

    /**
     * Helper method to create a user without using factories
     */
    private function createUser(array $attributes = []): User
    {
        self::$userCounter++;
        return User::create(array_merge([
            'name' => 'Test User ' . self::$userCounter,
            'email' => 'user' . self::$userCounter . '@test.com',
            'password' => bcrypt('secret123'),
            'role' => 'member',
            'level' => 'iniciante',
        ], $attributes));
    }

    /**
     * Helper method to create an event without using factories
     */
    private function createEvent(int $userId, array $attributes = []): Event
    {
        self::$eventCounter++;
        return Event::create(array_merge([
            'user_id' => $userId,
            'title' => 'Test Event ' . self::$eventCounter,
            'published' => true,
            'price' => 100.00,
            'start_at' => now()->addDays(7),
        ], $attributes));
    }

    /**
     * Property 2.1: Mercado Pago Active - Order Gateway Preserved
     * 
     * **Validates: Requirements 3.1**
     * 
     * OBSERVATION: When seller has Mercado Pago as active gateway,
     * the system creates Order with gateway = 'mercadopago'.
     * 
     * This behavior MUST be preserved after the fix.
     */
    public function test_mercado_pago_active_creates_order_with_mercadopago_gateway(): void
    {
        // Arrange: Create seller with Mercado Pago as active gateway
        $seller = User::create([
            'name' => 'Seller with Mercado Pago',
            'email' => 'seller-mp-1@test.com',
            'password' => bcrypt('secret123'),
            'role' => 'member',
            'level' => 'iniciante',
        ]);

        GatewayAccount::create([
            'user_id' => $seller->id,
            'provider' => 'mercadopago',
            'public_key' => 'TEST-mp-public-key-123',
            'access_token' => 'TEST-mp-access-token-456',
            'enabled' => true,
        ]);

        $event = Event::create([
            'user_id' => $seller->id,
            'title' => 'Paid Event with Mercado Pago',
            'published' => true,
            'price' => 100.00,
            'start_at' => now()->addDays(7),
        ]);

        $buyer = User::create([
            'name' => 'Buyer 1',
            'email' => 'buyer-1@test.com',
            'password' => bcrypt('secret123'),
            'role' => 'member',
            'level' => 'iniciante',
        ]);

        // Accept LGPD consent
        DB::table('legal_consents')->insert([
            'user_id' => $buyer->id,
            'document_type' => 'lgpd',
            'document_version' => '1.0',
            'accepted' => true,
            'accepted_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Mock MercadoPagoService
        $mpServiceMock = Mockery::mock(MercadoPagoService::class);
        $mpServiceMock->shouldReceive('createPreference')
            ->once()
            ->andReturn([
                'id' => 'mp_preference_123',
                'init_point' => 'https://mercadopago.com/checkout',
            ]);
        $this->app->instance(MercadoPagoService::class, $mpServiceMock);

        // Act: Buyer reserves ticket
        $response = $this
            ->actingAs($buyer)
            ->post(route('events.reserve', $event), [
                'quantity' => 1,
            ]);

        // Assert: Order should be created with gateway = 'mercadopago'
        $response->assertStatus(200);
        
        $order = Order::where('user_id', $buyer->id)->first();
        $this->assertNotNull($order, 'Order should be created');
        $this->assertEquals('mercadopago', $order->gateway, 
            'Order gateway should be "mercadopago" for Mercado Pago active gateway'
        );
    }

    /**
     * Property 2.2: Mercado Pago Active - Service Call Preserved
     * 
     * **Validates: Requirements 3.1**
     * 
     * OBSERVATION: When seller has Mercado Pago as active gateway,
     * MercadoPagoService::createPreference() is called.
     * 
     * This behavior MUST be preserved after the fix.
     */
    public function test_mercado_pago_active_calls_mercadopago_service(): void
    {
        // Arrange
        $seller = $this->createUser([
            'role' => 'member',
            'level' => 'iniciante',
        ]);

        GatewayAccount::create([
            'user_id' => $seller->id,
            'provider' => 'mercadopago',
            'public_key' => 'TEST-mp-public-key-789',
            'access_token' => 'TEST-mp-access-token-012',
            'enabled' => true,
        ]);

        $event = $this->createEvent($seller->id, [
            'title' => 'Event Testing MP Service Call',
            'published' => true,
            'price' => 50.00,
            'start_at' => now()->addDays(5),
        ]);

        $buyer = $this->createUser([
            'role' => 'member',
            'level' => 'iniciante',
        ]);

        DB::table('legal_consents')->insert([
            'user_id' => $buyer->id,
            'document_type' => 'lgpd',
            'document_version' => '1.0',
            'accepted' => true,
            'accepted_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Mock MercadoPagoService - verify it's called
        $mpServiceMock = Mockery::mock(MercadoPagoService::class);
        $mpServiceMock->shouldReceive('createPreference')
            ->once()
            ->with(Mockery::type(Order::class), Mockery::type('array'))
            ->andReturn([
                'id' => 'mp_preference_456',
                'init_point' => 'https://mercadopago.com/checkout/456',
            ]);
        $this->app->instance(MercadoPagoService::class, $mpServiceMock);

        // Act
        $response = $this
            ->actingAs($buyer)
            ->post(route('events.reserve', $event), [
                'quantity' => 1,
            ]);

        // Assert: MercadoPagoService::createPreference() should be called
        // (verified by Mockery expectations)
        $response->assertStatus(200);
    }

    /**
     * Property 2.3: Mercado Pago Active - View Data Preserved
     * 
     * **Validates: Requirements 3.6**
     * 
     * OBSERVATION: When seller has Mercado Pago as active gateway,
     * view receives preferenceId and publicKey from Mercado Pago.
     * 
     * This behavior MUST be preserved after the fix.
     */
    public function test_mercado_pago_active_view_receives_preference_id_and_public_key(): void
    {
        // Arrange
        $seller = $this->createUser([
            'role' => 'member',
            'level' => 'iniciante',
        ]);

        GatewayAccount::create([
            'user_id' => $seller->id,
            'provider' => 'mercadopago',
            'public_key' => 'TEST-mp-public-key-view',
            'access_token' => 'TEST-mp-access-token-view',
            'enabled' => true,
        ]);

        $event = $this->createEvent($seller->id, [
            'title' => 'Event Testing MP View Data',
            'published' => true,
            'price' => 75.00,
            'start_at' => now()->addDays(10),
        ]);

        $buyer = $this->createUser([
            'role' => 'member',
            'level' => 'iniciante',
        ]);

        DB::table('legal_consents')->insert([
            'user_id' => $buyer->id,
            'document_type' => 'lgpd',
            'document_version' => '1.0',
            'accepted' => true,
            'accepted_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $mpServiceMock = Mockery::mock(MercadoPagoService::class);
        $mpServiceMock->shouldReceive('createPreference')
            ->once()
            ->andReturn([
                'id' => 'mp_preference_view_789',
                'init_point' => 'https://mercadopago.com/checkout/789',
            ]);
        $this->app->instance(MercadoPagoService::class, $mpServiceMock);

        // Act
        $response = $this
            ->actingAs($buyer)
            ->post(route('events.reserve', $event), [
                'quantity' => 1,
            ]);

        // Assert: View should receive preferenceId and publicKey
        $response->assertStatus(200);
        $response->assertViewIs('checkout.transparent');
        $response->assertViewHas('preferenceId', 'mp_preference_view_789');
        $response->assertViewHas('publicKey');
        
        // Verify publicKey is from seller's Mercado Pago account
        $publicKey = $response->viewData('publicKey');
        $this->assertEquals('TEST-mp-public-key-view', $publicKey);
    }

    /**
     * Property 2.4: Free Event - Gateway Preserved
     * 
     * **Validates: Requirements 3.2**
     * 
     * OBSERVATION: When event has effective_price = 0,
     * Order is created with gateway = 'free'.
     * 
     * This behavior MUST be preserved after the fix.
     */
    public function test_free_event_creates_order_with_free_gateway(): void
    {
        // Arrange
        $seller = $this->createUser([
            'role' => 'member',
            'level' => 'iniciante',
        ]);

        $event = $this->createEvent($seller->id, [
            'title' => 'Free Event',
            'published' => true,
            'price' => 0.00,
            'start_at' => now()->addDays(3),
        ]);

        $buyer = $this->createUser([
            'role' => 'member',
            'level' => 'iniciante',
        ]);

        DB::table('legal_consents')->insert([
            'user_id' => $buyer->id,
            'document_type' => 'lgpd',
            'document_version' => '1.0',
            'accepted' => true,
            'accepted_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Act
        $response = $this
            ->actingAs($buyer)
            ->post(route('events.reserve', $event), [
                'quantity' => 1,
            ]);

        // Assert: Order should be created with gateway = 'free'
        $response->assertRedirect(route('events.show', $event));
        $response->assertSessionHas('success');
        
        $order = Order::where('user_id', $buyer->id)->first();
        $this->assertNotNull($order, 'Order should be created for free event');
        $this->assertEquals('free', $order->gateway, 
            'Order gateway should be "free" for free events'
        );
        $this->assertEquals(0, $order->total_amount, 
            'Order total_amount should be 0 for free events'
        );
    }

    /**
     * Property 2.5: Free Event - Immediate Settlement Preserved
     * 
     * **Validates: Requirements 3.2**
     * 
     * OBSERVATION: When event has effective_price = 0,
     * Order is settled immediately with status = 'paid'.
     * 
     * This behavior MUST be preserved after the fix.
     */
    public function test_free_event_order_is_settled_immediately(): void
    {
        // Arrange
        $seller = $this->createUser([
            'role' => 'member',
            'level' => 'iniciante',
        ]);

        $event = $this->createEvent($seller->id, [
            'title' => 'Free Event Immediate Settlement',
            'published' => true,
            'price' => 0.00,
            'start_at' => now()->addDays(2),
        ]);

        $buyer = $this->createUser([
            'role' => 'member',
            'level' => 'iniciante',
        ]);

        DB::table('legal_consents')->insert([
            'user_id' => $buyer->id,
            'document_type' => 'lgpd',
            'document_version' => '1.0',
            'accepted' => true,
            'accepted_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Act
        $response = $this
            ->actingAs($buyer)
            ->post(route('events.reserve', $event), [
                'quantity' => 1,
            ]);

        // Assert: Order should be settled immediately
        $response->assertRedirect(route('events.show', $event));
        
        $order = Order::where('user_id', $buyer->id)->first();
        $this->assertNotNull($order);
        $this->assertEquals('paid', $order->status, 
            'Free event order should be settled immediately with status = "paid"'
        );
        
        // Verify EventRegistration is confirmed
        $registration = EventRegistration::where('event_id', $event->id)
            ->where('user_id', $buyer->id)
            ->first();
        $this->assertNotNull($registration);
        $this->assertEquals('confirmed', $registration->status, 
            'Free event registration should be confirmed immediately'
        );
    }

    /**
     * Property 2.6: Coupon Application Preserved
     * 
     * **Validates: Requirements 3.4**
     * 
     * OBSERVATION: Coupon discounts are applied correctly to order total.
     * 
     * This behavior MUST be preserved after the fix.
     */
    public function test_coupon_discount_is_applied_correctly(): void
    {
        // Arrange
        $seller = $this->createUser([
            'role' => 'member',
            'level' => 'iniciante',
        ]);

        GatewayAccount::create([
            'user_id' => $seller->id,
            'provider' => 'mercadopago',
            'public_key' => 'TEST-mp-public-key-coupon',
            'access_token' => 'TEST-mp-access-token-coupon',
            'enabled' => true,
        ]);

        $event = $this->createEvent($seller->id, [
            'title' => 'Event with Coupon',
            'published' => true,
            'price' => 100.00,
            'start_at' => now()->addDays(15),
        ]);

        // Create a percentage discount coupon
        $coupon = Coupon::create([
            'code' => 'DISCOUNT20',
            'discount_type' => 'percentage',
            'discount_value' => 20.00,
            'valid_from' => now()->subDay(),
            'valid_until' => now()->addMonth(),
            'usage_limit' => 100,
            'times_used' => 0,
            'applicable_to' => 'event',
            'applicable_id' => $event->id,
            'enabled' => true,
        ]);

        $buyer = $this->createUser([
            'role' => 'member',
            'level' => 'iniciante',
        ]);

        DB::table('legal_consents')->insert([
            'user_id' => $buyer->id,
            'document_type' => 'lgpd',
            'document_version' => '1.0',
            'accepted' => true,
            'accepted_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $mpServiceMock = Mockery::mock(MercadoPagoService::class);
        $mpServiceMock->shouldReceive('createPreference')
            ->once()
            ->andReturn([
                'id' => 'mp_preference_coupon',
                'init_point' => 'https://mercadopago.com/checkout/coupon',
            ]);
        $this->app->instance(MercadoPagoService::class, $mpServiceMock);

        // Act: Apply coupon
        $response = $this
            ->actingAs($buyer)
            ->post(route('events.reserve', $event), [
                'quantity' => 1,
                'coupon_code' => 'DISCOUNT20',
            ]);

        // Assert: Coupon discount should be applied
        $response->assertStatus(200);
        
        $order = Order::where('user_id', $buyer->id)->first();
        $this->assertNotNull($order);
        
        // Original price: 100.00, 20% discount = 20.00, final: 80.00
        $this->assertEquals(80.00, $order->total_amount, 
            'Order total should reflect 20% coupon discount (100 - 20 = 80)'
        );
        
        // Verify coupon metadata is stored
        $this->assertArrayHasKey('coupon', $order->metadata);
        $this->assertEquals('DISCOUNT20', $order->metadata['coupon']['code']);
        $this->assertEquals(20.00, $order->metadata['coupon']['discount_amount']);
    }

    /**
     * Property 2.7: Platform Fee Calculation Preserved
     * 
     * **Validates: Requirements 3.4**
     * 
     * OBSERVATION: Platform fees are calculated correctly based on order total.
     * 
     * This behavior MUST be preserved after the fix.
     */
    public function test_platform_fee_is_calculated_correctly(): void
    {
        // Arrange
        $seller = $this->createUser([
            'role' => 'member',
            'level' => 'iniciante',
        ]);

        GatewayAccount::create([
            'user_id' => $seller->id,
            'provider' => 'mercadopago',
            'public_key' => 'TEST-mp-public-key-fee',
            'access_token' => 'TEST-mp-access-token-fee',
            'enabled' => true,
        ]);

        $event = $this->createEvent($seller->id, [
            'title' => 'Event Testing Platform Fee',
            'published' => true,
            'price' => 200.00,
            'start_at' => now()->addDays(20),
        ]);

        $buyer = $this->createUser([
            'role' => 'member',
            'level' => 'iniciante',
        ]);

        DB::table('legal_consents')->insert([
            'user_id' => $buyer->id,
            'document_type' => 'lgpd',
            'document_version' => '1.0',
            'accepted' => true,
            'accepted_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $mpServiceMock = Mockery::mock(MercadoPagoService::class);
        $mpServiceMock->shouldReceive('createPreference')
            ->once()
            ->andReturn([
                'id' => 'mp_preference_fee',
                'init_point' => 'https://mercadopago.com/checkout/fee',
            ]);
        $this->app->instance(MercadoPagoService::class, $mpServiceMock);

        // Act
        $response = $this
            ->actingAs($buyer)
            ->post(route('events.reserve', $event), [
                'quantity' => 1,
            ]);

        // Assert: Platform fee should be calculated
        $response->assertStatus(200);
        
        $order = Order::where('user_id', $buyer->id)->first();
        $this->assertNotNull($order);
        
        // Verify platform fee is calculated (should be > 0 for paid orders)
        $this->assertGreaterThan(0, $order->platform_fee_amount, 
            'Platform fee should be calculated for paid orders'
        );
        
        // Verify platform fee metadata is stored
        $this->assertArrayHasKey('platform_fee_percent', $order->metadata);
        $this->assertIsNumeric($order->metadata['platform_fee_percent']);
    }

    /**
     * Property 2.8: Multiple Quantity Order Preserved
     * 
     * **Validates: Requirements 3.4, 3.5**
     * 
     * OBSERVATION: Orders with quantity > 1 are handled correctly.
     * 
     * This behavior MUST be preserved after the fix.
     */
    public function test_multiple_quantity_order_is_handled_correctly(): void
    {
        // Arrange
        $seller = $this->createUser([
            'role' => 'member',
            'level' => 'iniciante',
        ]);

        GatewayAccount::create([
            'user_id' => $seller->id,
            'provider' => 'mercadopago',
            'public_key' => 'TEST-mp-public-key-qty',
            'access_token' => 'TEST-mp-access-token-qty',
            'enabled' => true,
        ]);

        $event = $this->createEvent($seller->id, [
            'title' => 'Event Testing Multiple Quantity',
            'published' => true,
            'price' => 50.00,
            'capacity' => 100,
            'start_at' => now()->addDays(25),
        ]);

        $buyer = $this->createUser([
            'role' => 'member',
            'level' => 'iniciante',
        ]);

        DB::table('legal_consents')->insert([
            'user_id' => $buyer->id,
            'document_type' => 'lgpd',
            'document_version' => '1.0',
            'accepted' => true,
            'accepted_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $mpServiceMock = Mockery::mock(MercadoPagoService::class);
        $mpServiceMock->shouldReceive('createPreference')
            ->once()
            ->andReturn([
                'id' => 'mp_preference_qty',
                'init_point' => 'https://mercadopago.com/checkout/qty',
            ]);
        $this->app->instance(MercadoPagoService::class, $mpServiceMock);

        // Act: Order 3 tickets
        $response = $this
            ->actingAs($buyer)
            ->post(route('events.reserve', $event), [
                'quantity' => 3,
            ]);

        // Assert: Order should reflect correct quantity and total
        $response->assertStatus(200);
        
        $order = Order::where('user_id', $buyer->id)->first();
        $this->assertNotNull($order);
        
        // Total should be 50.00 * 3 = 150.00
        $this->assertEquals(150.00, $order->total_amount, 
            'Order total should be price * quantity (50 * 3 = 150)'
        );
        
        // Verify order items
        $orderItem = $order->items()->first();
        $this->assertNotNull($orderItem);
        $this->assertEquals(3, $orderItem->quantity, 
            'Order item should have quantity = 3'
        );
    }

    /**
     * Property 2.9: Checkout Page Gateway Detection Preserved
     * 
     * **Validates: Requirements 3.1, 3.7**
     * 
     * OBSERVATION: Checkout page correctly detects Mercado Pago as active gateway
     * and passes mpEnabled and preferredGateway to view.
     * 
     * This behavior MUST be preserved after the fix.
     */
    public function test_checkout_page_detects_mercado_pago_gateway(): void
    {
        // Arrange
        $seller = $this->createUser([
            'role' => 'member',
            'level' => 'iniciante',
        ]);

        GatewayAccount::create([
            'user_id' => $seller->id,
            'provider' => 'mercadopago',
            'public_key' => 'TEST-mp-public-key-checkout',
            'access_token' => 'TEST-mp-access-token-checkout',
            'enabled' => true,
        ]);

        $event = $this->createEvent($seller->id, [
            'title' => 'Event Testing Checkout Page',
            'published' => true,
            'price' => 60.00,
            'start_at' => now()->addDays(30),
        ]);

        // Act: Access checkout page
        $response = $this->get(route('events.checkout', $event));

        // Assert: Checkout page should detect Mercado Pago
        $response->assertStatus(200);
        $response->assertViewIs('events.checkout');
        $response->assertViewHas('mpEnabled', true);
        $response->assertViewHas('preferredGateway', 'mercadopago');
    }

    /**
     * Property 2.10: Seller Credentials Priority Preserved
     * 
     * **Validates: Requirements 3.3, 3.7**
     * 
     * OBSERVATION: System prioritizes seller credentials over global credentials.
     * 
     * This behavior MUST be preserved after the fix.
     */
    public function test_seller_credentials_are_prioritized_over_global(): void
    {
        // Arrange: Set global Mercado Pago credentials
        Setting::updateOrCreate(['key' => 'mercadopago_env'], ['value' => 'sandbox']);
        Setting::updateOrCreate(['key' => 'mercadopago_sandbox_public_key'], ['value' => 'GLOBAL-mp-public-key']);
        Setting::updateOrCreate(['key' => 'mercadopago_sandbox_access_token'], ['value' => 'GLOBAL-mp-access-token']);

        $seller = $this->createUser([
            'role' => 'member',
            'level' => 'iniciante',
        ]);

        // Seller has their own Mercado Pago credentials
        GatewayAccount::create([
            'user_id' => $seller->id,
            'provider' => 'mercadopago',
            'public_key' => 'SELLER-mp-public-key',
            'access_token' => 'SELLER-mp-access-token',
            'enabled' => true,
        ]);

        $event = $this->createEvent($seller->id, [
            'title' => 'Event Testing Credentials Priority',
            'published' => true,
            'price' => 80.00,
            'start_at' => now()->addDays(35),
        ]);

        $buyer = $this->createUser([
            'role' => 'member',
            'level' => 'iniciante',
        ]);

        DB::table('legal_consents')->insert([
            'user_id' => $buyer->id,
            'document_type' => 'lgpd',
            'document_version' => '1.0',
            'accepted' => true,
            'accepted_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $mpServiceMock = Mockery::mock(MercadoPagoService::class);
        $mpServiceMock->shouldReceive('createPreference')
            ->once()
            ->andReturn([
                'id' => 'mp_preference_priority',
                'init_point' => 'https://mercadopago.com/checkout/priority',
            ]);
        $this->app->instance(MercadoPagoService::class, $mpServiceMock);

        // Act
        $response = $this
            ->actingAs($buyer)
            ->post(route('events.reserve', $event), [
                'quantity' => 1,
            ]);

        // Assert: Seller's public key should be used (not global)
        $response->assertStatus(200);
        $response->assertViewIs('checkout.transparent');
        
        $publicKey = $response->viewData('publicKey');
        $this->assertEquals('SELLER-mp-public-key', $publicKey, 
            'Seller credentials should be prioritized over global credentials'
        );
    }
}
