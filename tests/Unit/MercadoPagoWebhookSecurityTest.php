<?php

namespace Tests\Unit;

use App\Http\Controllers\PaymentWebhookController;
use Illuminate\Http\Request;
use Tests\TestCase;

class MercadoPagoWebhookSecurityTest extends TestCase
{
    public function test_validates_mercado_pago_signature_when_secret_is_configured(): void
    {
        config()->set('payments.mercadopago.webhook_secret', 'secret-test');
        config()->set('payments.mercadopago.webhook_signature_required', true);
        config()->set('payments.mercadopago.allow_unsigned_webhooks', false);

        $dataId = '99999999';
        $requestId = '2066ca19-c6f1-498a-be75-1923005edd06';
        $timestamp = '1704908010';
        $manifest = "id:{$dataId};request-id:{$requestId};ts:{$timestamp};";
        $hash = hash_hmac('sha256', $manifest, 'secret-test');

        $request = Request::create('/api/v1/webhooks/mercadopago?data.id=' . $dataId, 'POST', [
            'type' => 'payment',
        ], [], [], [
            'HTTP_X_REQUEST_ID' => $requestId,
            'HTTP_X_SIGNATURE' => "ts={$timestamp},v1={$hash}",
        ]);

        $result = $this->invokeValidation($request);

        $this->assertTrue($result['valid']);
        $this->assertFalse($result['reject']);
        $this->assertSame('valid_signature', $result['reason']);
    }

    public function test_rejects_unsigned_post_when_signature_is_required(): void
    {
        config()->set('payments.mercadopago.webhook_secret', 'secret-test');
        config()->set('payments.mercadopago.webhook_signature_required', true);
        config()->set('payments.mercadopago.allow_unsigned_webhooks', false);

        $request = Request::create('/api/v1/webhooks/mercadopago', 'POST', [
            'type' => 'payment',
            'data' => ['id' => '99999999'],
        ]);

        $result = $this->invokeValidation($request);

        $this->assertFalse($result['valid']);
        $this->assertTrue($result['reject']);
        $this->assertSame('missing_signature_headers', $result['reason']);
    }

    private function invokeValidation(Request $request): array
    {
        $method = new \ReflectionMethod(PaymentWebhookController::class, 'validateMercadoPagoWebhookRequest');
        $method->setAccessible(true);

        return $method->invoke(app(PaymentWebhookController::class), $request);
    }
}
