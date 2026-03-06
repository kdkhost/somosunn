<?php

namespace Tests\Feature;

use App\Exceptions\PaymentGatewayException;
use App\Models\Order;
use App\Models\User;
use App\Services\Payment\PagSeguroService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class PagSeguroCheckoutFallbackTest extends TestCase
{
    use DatabaseTransactions;

    public function test_checkout_process_payment_returns_structured_error_when_pagseguro_pix_requires_whitelist()
    {
        $user = User::create([
            'name' => 'Cliente Pix',
            'email' => 'cliente.pix.' . uniqid() . '@test.com',
            'password' => bcrypt('password'),
        ]);

        $order = Order::create([
            'user_id' => $user->id,
            'order_number' => 'ORD-PIX-' . rand(1000, 9999),
            'status' => 'pending',
            'gateway' => 'pagseguro',
            'total_amount' => 147.90,
        ]);

        $pagSeguroStub = new class extends PagSeguroService {
            public int $markPixUnavailableCalls = 0;

            public function createPixPayment(Order $order): array
            {
                throw new PaymentGatewayException(
                    'O Pix do PagSeguro nao esta liberado para esta conta no momento. Solicite a liberacao de whitelist no PagSeguro ou use outro metodo de pagamento disponivel.',
                    'pagseguro_pix_whitelist_required',
                    422
                );
            }

            public function markPixAsUnavailable(Order $order, int $minutes = 360): void
            {
                $this->markPixUnavailableCalls++;
            }
        };

        $this->app->instance(PagSeguroService::class, $pagSeguroStub);

        $response = $this->actingAs($user)->postJson(route('checkout.process_payment'), [
            'order_id' => $order->id,
            'formData' => [
                'payment_method_id' => 'pix',
                'payer' => [
                    'email' => $user->email,
                ],
            ],
        ]);

        $response
            ->assertStatus(422)
            ->assertJson([
                'error_code' => 'pagseguro_pix_whitelist_required',
                'pix_disabled' => true,
            ]);

        $this->assertSame(1, $pagSeguroStub->markPixUnavailableCalls);
    }
}
