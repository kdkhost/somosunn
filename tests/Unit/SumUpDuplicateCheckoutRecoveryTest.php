<?php

namespace Tests\Unit;

use App\Services\Payment\SumUpService;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class SumUpDuplicateCheckoutRecoveryTest extends TestCase
{
    public function test_it_recovers_compatible_pending_checkout_by_reference(): void
    {
        Http::fake([
            'https://api.sumup.com/v0.1/checkouts*' => Http::response([
                [
                    'id' => 'checkout-existing',
                    'checkout_reference' => 'ORDER-220',
                    'status' => 'PENDING',
                    'amount' => 37,
                    'currency' => 'BRL',
                ],
            ]),
        ]);

        $checkout = app(SumUpService::class)->findCheckoutByReference(
            'ORDER-220',
            'secret-key',
            37,
            'BRL'
        );

        $this->assertSame('checkout-existing', $checkout['id']);
        Http::assertSent(fn ($request) => str_contains($request->url(), 'checkout_reference=ORDER-220'));
    }

    public function test_it_rejects_checkout_with_incompatible_amount_or_terminal_status(): void
    {
        Http::fake([
            'https://api.sumup.com/v0.1/checkouts*' => Http::response([
                [
                    'id' => 'checkout-wrong-amount',
                    'checkout_reference' => 'ORDER-220',
                    'status' => 'PENDING',
                    'amount' => 99,
                    'currency' => 'BRL',
                ],
                [
                    'id' => 'checkout-failed',
                    'checkout_reference' => 'ORDER-220',
                    'status' => 'FAILED',
                    'amount' => 37,
                    'currency' => 'BRL',
                ],
            ]),
        ]);

        $checkout = app(SumUpService::class)->findCheckoutByReference(
            'ORDER-220',
            'secret-key',
            37,
            'BRL'
        );

        $this->assertNull($checkout);
    }
}
