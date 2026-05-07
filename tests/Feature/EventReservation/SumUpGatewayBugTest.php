<?php

namespace Tests\Feature\EventReservation;

use App\Models\Event;
use App\Models\EventRegistration;
use App\Models\GatewayAccount;
use App\Models\Order;
use App\Models\Setting;
use App\Models\User;
use App\Services\LegalConsentService;
use App\Services\Payment\MercadoPagoService;
use App\Services\Payment\SumUpService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Mockery;
use Tests\TestCase;

/**
 * Bug Condition Exploration Test - SumUp Active Gateway Not Used in Event Checkout
 * 
 * **Validates: Requirements 1.1, 1.2, 1.3, 1.4, 1.5, 1.6**
 * 
 * CRITICAL: This test MUST FAIL on unfixed code - failure confirms the bug exists.
 * DO NOT attempt to fix the test or the code when it fails.
 * 
 * This test encodes the expected behavior - it will validate the fix when it passes after implementation.
 * 
 * GOAL: Surface counterexamples that demonstrate the bug exists:
 * - Order created with gateway = 'mercadopago' instead of 'sumup'
 * - MercadoPagoService::createPreference() called instead of SumUpService::createCheckout()
 * - View rendered with Mercado Pago data instead of SumUp data
 */
class SumUpGatewayBugTest extends TestCase
{
    use RefreshDatabase;

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
     * Property 1: Bug Condition - SumUp Active Gateway Not Used in Event Checkout
     * 
     * **Validates: Requirements 1.1, 1.2, 1.3, 1.4, 1.5, 1.6**
     * 
     * EXPECTED OUTCOME ON UNFIXED CODE: This test FAILS
     * 
     * This test demonstrates the bug by creating a concrete scenario where:
     * - A seller has SumUp configured as active gateway
     * - A paid event is created by this seller
     * - A buyer attempts to reserve a ticket
     * 
     * The test expects the system to:
     * 1. Detect SumUp as the active gateway
     * 2. Create Order with gateway = 'sumup'
     * 3. Call SumUpService::createCheckout() instead of MercadoPagoService::createPreference()
     * 4. Render view with SumUp checkout data (checkout_id, sumupPublicKey)
     * 
     * COUNTEREXAMPLES EXPECTED (bug manifestation):
     * - System redirects with error "Pagamento indisponível" because it only checks for Mercado Pago
     * - OR Order created with gateway = 'mercadopago' instead of 'sumup'
     * - MercadoPagoService::createPreference() called instead of SumUpService::createCheckout()
     * - View rendered with Mercado Pago data (preferenceId) instead of SumUp data (checkout_id)
     */
    public function test_seller_with_sumup_active_gateway_should_use_sumup_for_event_checkout(): void
    {
        // Arrange: Create seller with SumUp as active gateway
        $seller = User::create([
            'name' => 'Seller with SumUp',
            'email' => 'seller-sumup@test.com',
            'password' => bcrypt('secret123'),
            'role' => 'member',
            'level' => 'iniciante',
        ]);

        // Configure SumUp as active gateway for seller
        GatewayAccount::create([
            'user_id' => $seller->id,
            'provider' => 'sumup',
            'access_token' => 'test_sumup_api_key_12345',
            'enabled' => true,
            'extra' => json_encode([
                'merchant_code' => 'MTEST123',
            ]),
        ]);

        // Create paid event by seller
        $event = Event::create([
            'user_id' => $seller->id,
            'title' => 'Paid Event with SumUp',
            'description' => 'Event that should use SumUp gateway',
            'start_at' => now()->addDays(7),
            'published' => true,
            'price' => 100.00,
        ]);

        // Create buyer
        $buyer = User::create([
            'name' => 'Event Buyer',
            'email' => 'buyer@test.com',
            'password' => bcrypt('secret123'),
            'role' => 'member',
            'level' => 'iniciante',
        ]);

        // Accept LGPD consent for buyer to bypass middleware
        DB::table('legal_consents')->insert([
            'user_id' => $buyer->id,
            'document_type' => 'lgpd',
            'document_version' => '1.0',
            'accepted' => true,
            'accepted_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Mock SumUpService to track if createCheckout is called
        $sumUpServiceMock = Mockery::mock(SumUpService::class);
        $sumUpServiceMock->shouldReceive('createCheckout')
            ->once()
            ->with(Mockery::type(Order::class), Mockery::type('array'))
            ->andReturn([
                'checkout_id' => 'sumup_checkout_abc123',
                'webhook_token' => 'webhook_token_xyz',
                'raw' => ['id' => 'sumup_checkout_abc123'],
            ]);
        $this->app->instance(SumUpService::class, $sumUpServiceMock);

        // Mock MercadoPagoService - in unfixed code, this WILL be called (demonstrating the bug)
        // After fix, this should NOT be called
        $mpServiceMock = Mockery::mock(MercadoPagoService::class);
        $mpServiceMock->shouldReceive('createPreference')
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

        // Assert: System should detect SumUp as active gateway and NOT redirect with error
        // EXPECTED BEHAVIOR: Should proceed to checkout (status 200)
        // ACTUAL BEHAVIOR (BUG): Redirects (status 302) with error "Pagamento indisponível" 
        // because system only checks mpEnabled and doesn't recognize SumUp
        
        // Document the counterexample: System rejects SumUp as invalid gateway
        if ($response->status() === 302) {
            // This is the bug manifestation - system redirects because it doesn't recognize SumUp
            $response->assertSessionHas('error');
            $errorMessage = session('error');
            
            $this->fail(
                "COUNTEREXAMPLE FOUND (Bug Confirmed): System redirected with error instead of processing checkout.\n\n" .
                "Session error message: '{$errorMessage}'\n\n" .
                "ROOT CAUSE: The system only checks for Mercado Pago (mpEnabled) in EventReservationController::reserve() " .
                "and doesn't recognize SumUp as a valid gateway.\n\n" .
                "EVIDENCE:\n" .
                "1. Seller has SumUp configured as active gateway (provider='sumup', enabled=true)\n" .
                "2. Event is paid (price=100.00)\n" .
                "3. System redirected with error instead of creating checkout\n" .
                "4. This happens because line ~127 in EventReservationController hardcodes: \$gatewayProvider = 'mercadopago'\n" .
                "5. And line ~119 only checks: \$paymentsConfigured = \$gateways['mpEnabled']\n\n" .
                "This demonstrates the bug described in the requirements - EventReservationController doesn't detect " .
                "the seller's active gateway and always assumes Mercado Pago."
            );
        }
        
        // If we reach here, the system allowed checkout to proceed
        $response->assertStatus(200);

        $order = Order::where('user_id', $buyer->id)->first();
        
        $this->assertNotNull($order, 
            'COUNTEREXAMPLE FOUND: Order was not created. ' .
            'System likely rejected the payment because it only checks for Mercado Pago gateway.'
        );
        
        // EXPECTED BEHAVIOR: Order should have gateway = 'sumup'
        // ACTUAL BEHAVIOR (BUG): Order has gateway = 'mercadopago'
        $this->assertEquals(
            'sumup',
            $order->gateway,
            "COUNTEREXAMPLE FOUND: Order created with gateway = '{$order->gateway}' instead of 'sumup'. " .
            "This confirms the bug - system hardcodes 'mercadopago' without detecting seller's active gateway."
        );

        // EXPECTED BEHAVIOR: SumUpService::createCheckout() should be called
        // ACTUAL BEHAVIOR (BUG): MercadoPagoService::createPreference() is called instead
        // (This is verified by the mock expectations - if MP is called, it demonstrates the bug)

        // EXPECTED BEHAVIOR: View should receive SumUp checkout data
        // ACTUAL BEHAVIOR (BUG): View receives Mercado Pago data
        $response->assertViewIs('checkout.transparent');
        $response->assertViewHas('order', function ($viewOrder) use ($order) {
            return $viewOrder->id === $order->id;
        });
        
        // View should have checkout_id (SumUp) instead of preferenceId (Mercado Pago)
        $response->assertViewHas('checkout_id', 
            "COUNTEREXAMPLE FOUND: View does not have 'checkout_id' (SumUp data). " .
            "System is rendering Mercado Pago checkout instead of SumUp checkout."
        );
        
        // View should NOT have preferenceId (Mercado Pago specific)
        $response->assertViewMissing('preferenceId',
            "COUNTEREXAMPLE FOUND: View has 'preferenceId' (Mercado Pago data). " .
            "This confirms the bug - system is using Mercado Pago instead of SumUp."
        );
    }

    /**
     * Additional test case: Verify checkout() method detects SumUp as active gateway
     * 
     * This test verifies that the checkout page correctly identifies SumUp as the active gateway
     * and passes the correct gateway information to the view.
     * 
     * EXPECTED OUTCOME ON UNFIXED CODE: This test FAILS because checkout page redirects
     * with error "organizador não configurou um método de pagamento" since it only checks
     * for Mercado Pago and doesn't recognize SumUp as a valid gateway.
     */
    public function test_checkout_page_should_detect_sumup_as_active_gateway(): void
    {
        // Arrange: Create seller with SumUp as active gateway
        $seller = User::create([
            'name' => 'Seller with SumUp',
            'email' => 'seller-sumup-checkout@test.com',
            'password' => bcrypt('secret123'),
            'role' => 'member',
            'level' => 'iniciante',
        ]);

        GatewayAccount::create([
            'user_id' => $seller->id,
            'provider' => 'sumup',
            'access_token' => 'test_sumup_api_key_67890',
            'enabled' => true,
            'extra' => json_encode([
                'merchant_code' => 'MTEST456',
            ]),
        ]);

        $event = Event::create([
            'user_id' => $seller->id,
            'title' => 'Paid Event Checkout Test',
            'description' => 'Testing checkout page gateway detection',
            'start_at' => now()->addDays(7),
            'published' => true,
            'price' => 50.00,
        ]);

        // Act: Access checkout page
        $response = $this->get(route('events.checkout', $event));

        // Assert: Checkout page should detect SumUp as active gateway
        // EXPECTED BEHAVIOR: Should show checkout page with SumUp enabled
        // ACTUAL BEHAVIOR (BUG): Redirects with error because only checks for Mercado Pago
        $response->assertStatus(200);
        $response->assertViewIs('events.checkout');
        
        // EXPECTED BEHAVIOR: View should indicate a gateway is enabled (SumUp)
        // ACTUAL BEHAVIOR (BUG): mpEnabled is false because it only checks Mercado Pago
        // After fix, this should be replaced with a generic 'gatewayEnabled' check
        $response->assertViewHas('preferredGateway', 'sumup');
    }
}
