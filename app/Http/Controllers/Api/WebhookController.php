<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use App\Models\Order;
use App\Models\Payment;
use App\Models\User;
use App\Models\Enrollment;
use App\Models\Subscription;
use App\Models\Invoice;
use Carbon\Carbon;

class WebhookController extends Controller
{
    /**
     * Handle MercadoPago Webhook
     */
    public function mercadopago(Request $request)
    {
        if ($request->isMethod('GET')) {
            return response()->json(['status' => 'online', 'message' => 'MercadoPago Webhook is active and waiting for POST requests.'], 200);
        }

        Log::info('MercadoPago Webhook received: ' . json_encode($request->all()));

        $type = $request->input('type');
        $id = $request->input('data.id');

        if ($type !== 'payment' || !$id) {
            return response()->json(['status' => 'ignored'], 200);
        }

        try {
            // Em ambiente de teste local, simula o SDK ignorando request externa
            if (app()->environment('testing')) {
                $payment = (object) [
                    'id' => $id,
                    'external_reference' => $request->input('external_reference', null),
                    'status' => $request->input('status', 'approved'),
                    'transaction_amount' => 100.00,
                    'metadata' => (object) []
                ];
            } else {
                // Init SDK
                \App\Services\MercadoPagoService::init();
                $payment = \MercadoPago\Payment::find_by_id($id);
            }

            if (!$payment) {
                Log::error("MercadoPago Webhook: Payment $id not found.");
                return response()->json(['status' => 'not_found'], 404);
            }

            $orderId = $payment->external_reference;
            $status = $payment->status; // approved, pending, rejected, etc.

            $order = Order::find($orderId);
            if (!$order) {
                // Try finding by metadata if external_reference is missing
                if (isset($payment->metadata->order_id)) {
                    $order = Order::find($payment->metadata->order_id);
                }
            }

            if (!$order) {
                Log::error("MercadoPago Webhook: Order not found for payment $id (ref: $orderId).");
                return response()->json(['status' => 'order_not_found'], 404);
            }

            // Update Order Status
            $this->processPaymentStatus($order, $status, 'mercadopago', $id, $payment->transaction_amount);

        } catch (\Throwable $e) {
            Log::error('MercadoPago Webhook Error: ' . $e->getMessage());
            return response()->json(['status' => 'error'], 500);
        }

        return response()->json(['status' => 'success']);
    }

    /**
     * Handle PagSeguro Webhook
     */
    public function pagseguro(Request $request)
    {
        if ($request->isMethod('GET')) {
            return response()->json(['status' => 'online', 'message' => 'PagSeguro Webhook is active and waiting for POST notifications.'], 200);
        }

        Log::info('PagSeguro Webhook received: ' . json_encode($request->all()));

        $notificationCode = $request->input('notificationCode');
        $notificationType = $request->input('notificationType');

        if ($notificationType !== 'transaction' || !$notificationCode) {
            return response()->json(['status' => 'ignored'], 200);
        }

        try {
            $env = \App\Models\Setting::get('pagseguro_env', config('payments.pagseguro.env', 'sandbox'));
            $sandbox = ($env === 'sandbox');

            $prefix = $sandbox ? 'pagseguro_sandbox_' : 'pagseguro_prod_';

            $token = \App\Models\Setting::get($prefix . 'token');
            if (empty($token)) {
                $token = \App\Models\Setting::get('pagseguro_token');
            }
            if (empty($token)) {
                $token = config('payments.pagseguro.access_token', config('payments.pagseguro.token'));
            }

            $email = \App\Models\Setting::get('pagseguro_email', config('payments.pagseguro.email'));

            $url = $sandbox
                ? "https://ws.sandbox.pagseguro.uol.com.br/v3/transactions/notifications/{$notificationCode}"
                : "https://ws.pagseguro.uol.com.br/v3/transactions/notifications/{$notificationCode}";

            $response = Http::get($url, [
                'email' => $email,
                'token' => $token
            ]);

            if ($response->failed()) {
                Log::error('PagSeguro Webhook: Failed to fetch transaction. ' . $response->body());
                return response()->json(['status' => 'error'], 500);
            }

            $xml = simplexml_load_string($response->body());
            $json = json_encode($xml);
            $data = json_decode($json, true);

            $reference = $data['reference'] ?? null;
            $status = (int) ($data['status'] ?? 0); // 3 = paid
            $code = $data['code'] ?? null;
            $grossAmount = $data['grossAmount'] ?? 0;

            if (!$reference) {
                Log::error('PagSeguro Webhook: Reference not found in transaction.');
                return response()->json(['status' => 'ignored'], 200);
            }

            $order = Order::find($reference);
            if (!$order) {
                Log::error("PagSeguro Webhook: Order $reference not found.");
                return response()->json(['status' => 'order_not_found'], 404);
            }

            $statusMap = [
                1 => 'pending', // Aguardando pagamento
                2 => 'pending', // Em análise
                3 => 'approved', // Paga
                4 => 'approved', // Disponível
                5 => 'dispute', // Em disputa
                6 => 'refunded', // Devolvida
                7 => 'cancelled', // Cancelada
            ];

            $orderStatus = $statusMap[$status] ?? 'pending';

            $this->processPaymentStatus($order, $orderStatus, 'pagseguro', $code, $grossAmount);

        } catch (\Throwable $e) {
            Log::error('PagSeguro Webhook Error: ' . $e->getMessage());
            return response()->json(['status' => 'error'], 500);
        }

        return response()->json(['status' => 'success']);
    }

    /**
     * Process unified payment status
     */
    private function processPaymentStatus(Order $order, string $status, string $gateway, string $transactionId, $amount)
    {
        Log::info("Processing Order #{$order->id} - Status: {$status}");

        // Map gateway status to internal status
        // MercadoPago: approved, pending, in_process, rejected, refunded, cancelled
        // Internal: paid, pending, failed, refunded, cancelled

        $internalStatus = 'pending';
        if ($status === 'approved' || $status === 'paid') {
            $internalStatus = 'paid';
        } elseif ($status === 'rejected' || $status === 'cancelled') {
            $internalStatus = 'failed';
        } elseif ($status === 'refunded') {
            $internalStatus = 'refunded';
        }

        // Avoid reprocessing if already paid
        if ($order->status === 'paid' && $internalStatus === 'paid') {
            return;
        }

        $order->status = $internalStatus;
        $order->transaction_id = $transactionId;
        if ($internalStatus === 'paid' && !$order->paid_at) {
            $order->paid_at = now();
        }
        $order->save();

        // Register/Update Payment Record
        Payment::create([
            'user_id' => $order->user_id,
            'gateway' => $gateway,
            'gateway_id' => $transactionId,
            'status' => $internalStatus,
            'amount' => $amount,
            'currency' => $order->currency ?? 'BRL',
            'payable_id' => $order->id,
            'payable_type' => Order::class,
            'description' => "Order #{$order->id} update via Webhook"
        ]);

        if ($internalStatus === 'paid') {
            $this->fulfillOrder($order);
        }
    }

    /**
     * Fulfill the order (Enrollments, Subscriptions, etc.)
     */
    private function fulfillOrder(Order $order)
    {
        Log::info("Fulfilling Order #{$order->id}");

        $order->load('items');

        foreach ($order->items as $item) {
            if ($item->item_type === 'course') {
                Enrollment::firstOrCreate([
                    'user_id' => $order->user_id,
                    'enrollable_id' => $item->item_id,
                    'enrollable_type' => \App\Models\Course::class
                ], [
                    'status' => 'active',
                    'started_at' => now(),
                    'progress' => []
                ]);
                Log::info("Enrolled User {$order->user_id} in Course {$item->item_id}");
            } elseif ($item->item_type === 'mentorship') {
                Enrollment::firstOrCreate([
                    'user_id' => $order->user_id,
                    'enrollable_id' => $item->item_id,
                    'enrollable_type' => \App\Models\Mentorship::class
                ], [
                    'status' => 'active',
                    'started_at' => now(),
                    'progress' => []
                ]);
                Log::info("Enrolled User {$order->user_id} in Mentorship {$item->item_id}");
            } elseif ($item->item_type === 'plan' || $item->item_type === 'subscription') {
                $user = User::find($order->user_id);
                if ($user) {
                    $user->plan_id = $item->item_id;

                    $plan = \App\Models\Plan::find($item->item_id);
                    $period = $this->resolveOrderPlanPeriod($order, $item);
                    $months = $this->getPlanDurationInMonths($plan, $period);

                    $user->plan_expires_at = now()->addMonths($months);
                    $user->save();

                    Subscription::create([
                        'user_id' => $user->id,
                        'plan_id' => $item->item_id,
                        'started_at' => now(),
                        'ends_at' => now()->addMonths($months),
                        'status' => 'active',
                        // 'gateway' fields removed as they are not in fillable
                    ]);

                    Log::info("Subscribed User {$order->user_id} to Plan {$item->item_id}");
                }
            }
        }

        // Generate Invoice
        if (!$order->invoice) {
            Invoice::create([
                'user_id' => $order->user_id,
                'order_id' => $order->id,
                'status' => 'paid',
                'total_amount' => $order->total_amount,
                'issued_at' => now(),
                'paid_at' => now(),
                'number' => 'FAT-' . date('Y') . '-' . str_pad($order->id, 6, '0', STR_PAD_LEFT)
            ]);
        }
    }

    private function getPlanDurationInMonths($plan, ?string $period = null)
    {
        if (!$plan)
            return 1;

        $period = strtolower(trim((string) ($period ?: $plan->period ?: '')));

        if (str_contains($period, 'month') || str_contains($period, 'mensal'))
            return 1;
        if (str_contains($period, 'quarter') || str_contains($period, 'trimestral'))
            return 3;
        if (str_contains($period, 'semester') || str_contains($period, 'semestral'))
            return 6;
        if (str_contains($period, 'year') || str_contains($period, 'anual'))
            return 12;

        return 1;
    }

    private function resolveOrderPlanPeriod(Order $order, $item): string
    {
        $itemPeriod = trim((string) data_get($item, 'data.period', ''));
        if ($itemPeriod !== '') {
            return \App\Models\Plan::sanitizePeriod($itemPeriod);
        }

        $metadataPeriod = trim((string) data_get($order->metadata, 'period', ''));
        if ($metadataPeriod !== '') {
            return \App\Models\Plan::sanitizePeriod($metadataPeriod);
        }

        return \App\Models\Plan::sanitizePeriod((string) ($order->period ?? ''));
    }
}
