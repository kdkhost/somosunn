<?php

namespace App\Http\Controllers;

use App\Services\SumUpService;
use App\Models\Order;
use App\Models\Setting;
use App\Rules\ValidEmailAddress;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class SumUpController extends Controller
{
    protected $sumupService;

    public function __construct(SumUpService $sumupService)
    {
        $this->sumupService = $sumupService;
    }

    /**
     * Cria um checkout SumUp
     */
    public function createCheckout(Request $request)
    {
        try {
            if ($request->filled('customer_email')) {
                $request->merge(['customer_email' => mb_strtolower(trim((string) $request->input('customer_email')))]);
            }

            $data = $request->validate([
                'amount' => 'required|numeric|min:0.01',
                'description' => 'required|string|max:255',
                'reference' => 'required|string|max:100',
                'return_url' => 'nullable|url',
                'customer_email' => ['nullable', new ValidEmailAddress()],
                'product_type' => 'nullable|string',
                'user_type' => 'nullable|string',
            ]);

            // Verificar se o SumUp está habilitado
            if (!$this->sumupService->isEnabled()) {
                return response()->json([
                    'success' => false,
                    'message' => 'SumUp não está habilitado ou configurado.'
                ], 400);
            }

            // Verificar permissões por tipo de usuário
            if (!empty($data['user_type']) && !$this->sumupService->isAllowedForUser($data['user_type'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'SumUp não está disponível para este tipo de usuário.'
                ], 403);
            }

            // Verificar permissões por tipo de produto
            if (!empty($data['product_type']) && !$this->sumupService->isAllowedForProduct($data['product_type'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'SumUp não está disponível para este tipo de produto.'
                ], 403);
            }

            // Verificar limites de valor
            if (!$this->sumupService->isAmountAllowed($data['amount'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Valor fora dos limites permitidos para SumUp.'
                ], 400);
            }

            // Calcular taxas
            $fees = $this->sumupService->calculateFees($data['amount']);
            
            $checkoutData = [
                'reference' => $data['reference'],
                'amount' => $fees['gross_amount'], // Usar valor com taxa se repassada ao cliente
                'currency' => 'BRL',
                'description' => $data['description'],
                'return_url' => $data['return_url'] ?? route('checkout.success'),
            ];

            if (!empty($data['customer_email'])) {
                $checkoutData['customer'] = [
                    'email' => $data['customer_email']
                ];
            }

            $result = $this->sumupService->createCheckout($checkoutData);

            if ($result['success']) {
                return response()->json([
                    'success' => true,
                    'data' => $result['data'],
                    'fees' => $fees
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => $result['message']
                ], 400);
            }

        } catch (\Exception $e) {
            Log::error('SumUp checkout creation failed', [
                'error' => $e->getMessage(),
                'request' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erro interno do servidor.'
            ], 500);
        }
    }

    /**
     * Consulta status de um checkout
     */
    public function getCheckout($checkoutId)
    {
        try {
            $result = $this->sumupService->getCheckout($checkoutId);

            if ($result['success']) {
                return response()->json([
                    'success' => true,
                    'data' => $result['data']
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => $result['message']
                ], 400);
            }

        } catch (\Exception $e) {
            Log::error('SumUp checkout query failed', [
                'error' => $e->getMessage(),
                'checkout_id' => $checkoutId
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erro interno do servidor.'
            ], 500);
        }
    }

    /**
     * Webhook do SumUp
     */
    public function webhook(Request $request)
    {
        // GET = acesso via navegador ou health-check do gateway
        if ($request->isMethod('GET')) {
            return response('OK', 200);
        }

        try {
            $payload = $request->getContent();
            $signature = $request->header('X-SumUp-Signature');

            // Validar webhook
            if (!$this->sumupService->validateWebhook($payload, $signature)) {
                Log::warning('SumUp webhook validation failed', [
                    'signature' => $signature,
                    'payload' => $payload
                ]);
                return response('Unauthorized', 401);
            }

            $data = json_decode($payload, true);
            
            if (!$data) {
                Log::error('SumUp webhook invalid JSON', ['payload' => $payload]);
                return response('Invalid JSON', 400);
            }

            Log::info('SumUp webhook received', $data);

            // Processar diferentes tipos de eventos
            $eventType = $data['event_type'] ?? null;
            $checkoutId = $data['checkout_id'] ?? null;
            $transactionId = $data['transaction_id'] ?? null;

            if (!$checkoutId) {
                Log::error('SumUp webhook missing checkout_id', $data);
                return response('Missing checkout_id', 400);
            }

            // Buscar pedido pelo checkout_id ou transaction_id
            $order = Order::where('gateway_transaction_id', $checkoutId)
                         ->orWhere('gateway_transaction_id', $transactionId)
                         ->first();

            if (!$order) {
                Log::warning('SumUp webhook order not found', [
                    'checkout_id' => $checkoutId,
                    'transaction_id' => $transactionId
                ]);
                return response('Order not found', 404);
            }

            // Processar evento baseado no tipo
            switch ($eventType) {
                case 'CHECKOUT_PAID':
                    $this->handleCheckoutPaid($order, $data);
                    break;
                    
                case 'CHECKOUT_FAILED':
                    $this->handleCheckoutFailed($order, $data);
                    break;
                    
                case 'CHECKOUT_CANCELLED':
                    $this->handleCheckoutCancelled($order, $data);
                    break;
                    
                default:
                    Log::info('SumUp webhook unhandled event type', [
                        'event_type' => $eventType,
                        'data' => $data
                    ]);
            }

            return response('OK', 200);

        } catch (\Exception $e) {
            Log::error('SumUp webhook processing failed', [
                'error' => $e->getMessage(),
                'payload' => $request->getContent()
            ]);

            return response('Internal Server Error', 500);
        }
    }

    /**
     * Processa pagamento aprovado
     */
    protected function handleCheckoutPaid(Order $order, array $data)
    {
        if ($order->status === 'paid') {
            Log::info('SumUp order already paid', ['order_id' => $order->id]);
            return;
        }

        $order->update([
            'status' => 'paid',
            'gateway' => 'sumup',
            'gateway_transaction_id' => $data['transaction_id'] ?? $data['checkout_id'],
            'paid_at' => now(),
            'metadata' => array_merge($order->metadata ?? [], [
                'sumup_webhook_data' => $data,
                'payment_method' => $data['payment_type'] ?? 'unknown'
            ])
        ]);

        Log::info('SumUp order paid', [
            'order_id' => $order->id,
            'transaction_id' => $data['transaction_id'] ?? $data['checkout_id']
        ]);

        app(\App\Services\OrderControlCopyDispatcher::class)->dispatch($order);

        // Disparar eventos de pagamento aprovado
        // event(new OrderPaid($order));
    }

    /**
     * Processa pagamento falhado
     */
    protected function handleCheckoutFailed(Order $order, array $data)
    {
        $order->update([
            'status' => 'failed',
            'metadata' => array_merge($order->metadata ?? [], [
                'sumup_webhook_data' => $data,
                'failure_reason' => $data['failure_reason'] ?? 'unknown'
            ])
        ]);

        Log::info('SumUp order failed', [
            'order_id' => $order->id,
            'reason' => $data['failure_reason'] ?? 'unknown'
        ]);
    }

    /**
     * Processa pagamento cancelado
     */
    protected function handleCheckoutCancelled(Order $order, array $data)
    {
        $order->update([
            'status' => 'cancelled',
            'metadata' => array_merge($order->metadata ?? [], [
                'sumup_webhook_data' => $data
            ])
        ]);

        app(\App\Services\OrderAccessRevocationService::class)->revoke($order->fresh(['items', 'user']), 'gateway_cancelled');

        Log::info('SumUp order cancelled', ['order_id' => $order->id]);
    }

    /**
     * Calcula parcelamento
     */
    public function calculateInstallments(Request $request)
    {
        try {
            $data = $request->validate([
                'amount' => 'required|numeric|min:0.01',
                'installments' => 'required|integer|min:1|max:12'
            ]);

            $calculation = $this->sumupService->calculateInstallments(
                $data['amount'], 
                $data['installments']
            );

            return response()->json([
                'success' => true,
                'data' => $calculation
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Verifica disponibilidade do SumUp
     */
    public function checkAvailability(Request $request)
    {
        $data = $request->validate([
            'amount' => 'nullable|numeric|min:0.01',
            'product_type' => 'nullable|string',
            'user_type' => 'nullable|string',
        ]);

        $available = $this->sumupService->isEnabled();

        if ($available && !empty($data['user_type'])) {
            $available = $this->sumupService->isAllowedForUser($data['user_type']);
        }

        if ($available && !empty($data['product_type'])) {
            $available = $this->sumupService->isAllowedForProduct($data['product_type']);
        }

        if ($available && !empty($data['amount'])) {
            $available = $this->sumupService->isAmountAllowed($data['amount']);
        }

        return response()->json([
            'available' => $available,
            'enabled_methods' => $available ? $this->sumupService->getEnabledPaymentMethods() : [],
            'fallback_to_mercadopago' => $this->sumupService->shouldFallbackToMercadoPago()
        ]);
    }
}
