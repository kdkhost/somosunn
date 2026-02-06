<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Event;
use App\Models\EventRegistration;
use App\Models\GatewayAccount;
use App\Models\Order;
use App\Models\Plan;
use App\Services\CouponService;
use App\Services\InvoiceService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class PaymentWebhookController extends Controller
{
    public function mercadoPago(Request $request, $seller_id)
    {
        // Minimal validation of "topic" or type
        $type = $request->input('type') ?? $request->input('topic');
        
        if ($type === 'payment') {
            try {
                // Fetch payment info from API to verify status
                $paymentId = $request->input('data.id') ?? $request->input('id');
                if (!$paymentId) {
                    Log::warning('MP Webhook: missing payment id', ['payload' => $request->all()]);
                    return response('OK', 200);
                }
                
                // We're blindly trusting the webhook ID presence, but ideally we query the API using the seller's token.
                // Since we have multiple sellers, it's tricky without knowing which seller.
                // However, internal logic: if we have the order ID in external_reference, use it.
                // BUT MP webhook only sends ID. We need to query API.
                // Simplification for now: Assume valid if signature check passes (TODO) or trust data.
                
                // Better approach: We can't query API without Token. 
                // We can try to find Order by transaction_id if we saved it during checkout creation? 
                // No, checkout creation yields Preference ID (MP). 
                // MP sends 'external_reference' in the payment object.
                // So we MUST query the payment.
                // Limitation: We don't know which seller token to use unless we iterate or use a global token (not the case here).
                
                // ALTERNATIVE: Use the `seller_id` passed in the URL (we added this in service).
                $sellerId = (string) $seller_id;

                $token = null;
                if ($sellerId === 'platform') {
                    $token = config('payments.mercadopago.access_token');
                } elseif (ctype_digit($sellerId)) {
                    $sellerAccount = GatewayAccount::where('user_id', (int) $sellerId)
                        ->where('provider', 'mercadopago')
                        ->where('enabled', true)
                        ->first();

                    $token = $sellerAccount?->access_token;
                }

                if (!$token) {
                    $token = config('payments.mercadopago.access_token');
                }
                
                if ($token) {
                     $response = Http::withToken($token)->get('https://api.mercadopago.com/v1/payments/' . $paymentId);
                     
                     if ($response->successful()) {
                         $data = $response->json();
                         $orderId = $data['external_reference'] ?? null;
                         $status = $data['status'] ?? '';
                         
                         if ($orderId && $status === 'approved') {
                             $order = Order::find($orderId);
                             if ($order) {
                                 $wasPaid = $order->status === 'paid';

                                 if (!$wasPaid) {
                                     $order->update([
                                         'status' => 'paid',
                                         'transaction_id' => (string) $paymentId,
                                         'metadata' => array_merge($order->metadata ?? [], ['webhook_data' => $data]),
                                     ]);
                                     Log::info("Order #{$orderId} marked as PAID via MP Webhook");
                                 } else {
                                     $order->update([
                                         'transaction_id' => $order->transaction_id ?: (string) $paymentId,
                                         'metadata' => array_merge($order->metadata ?? [], ['webhook_data' => $data]),
                                     ]);
                                 }

                                 app(CouponService::class)->markOrderRedemptionAsUsed((int) $order->id);
                                 $this->confirmEventRegistrationsForOrder($order);
                                 $this->activatePlanForOrder($order);
                                 app(InvoiceService::class)->issueAndQueueForOrder($order);
                             }
                         }
                     }
                }
            } catch (\Exception $e) {
                Log::error('MP Webhook Error: ' . $e->getMessage(), ['seller_id' => $seller_id]);
            }
        }

        return response('OK', 200);
    }

    private function confirmEventRegistrationsForOrder(Order $order): void
    {
        $registrations = EventRegistration::where('order_id', $order->id)->get();
        if ($registrations->isEmpty()) {
            return;
        }

        DB::transaction(function () use ($registrations, $order) {
            $needsManualRefund = false;

            foreach ($registrations as $reg) {
                if (in_array($reg->status, EventRegistration::COUNTED_STATUSES, true)) {
                    continue;
                }
                if ($reg->status === EventRegistration::STATUS_CANCELLED) {
                    continue;
                }

                $event = Event::whereKey($reg->event_id)->lockForUpdate()->first();
                if (!$event) {
                    continue;
                }

                if ($event->capacity && !$event->hasCapacityFor((int) $reg->quantity)) {
                    $reg->update(['status' => EventRegistration::STATUS_CANCELLED]);
                    $needsManualRefund = true;
                    continue;
                }

                $reg->update(['status' => EventRegistration::STATUS_PAID]);
            }

            if ($needsManualRefund) {
                $order->update([
                    'metadata' => array_merge($order->metadata ?? [], [
                        'event_overbooked' => true,
                        'needs_manual_refund' => true,
                    ]),
                ]);
            }
        });
    }

    private function activatePlanForOrder(Order $order): void
    {
        $item = $order->items()->where('item_type', 'plan')->first();
        if (!$item) {
            return;
        }

        $plan = Plan::find($item->item_id);
        if (!$plan) {
            return;
        }

        $user = $order->user;
        if (!$user) {
            return;
        }

        $user->update([
            'plan_id' => $plan->id,
            'plan_expires_at' => $this->planExpiresAt($plan),
        ]);
    }

    private function planExpiresAt(Plan $plan): ?\Carbon\Carbon
    {
        $period = trim((string) ($plan->period ?? ''));
        $periodLower = Str::lower($period);

        if ($periodLower === 'vitalício' || $periodLower === 'vitalicio') {
            return null;
        }

        if (ctype_digit($period)) {
            return now()->addDays((int) $period);
        }

        return match ($periodLower) {
            'mensal' => now()->addMonth(),
            'trimestral' => now()->addMonths(3),
            'semestral' => now()->addMonths(6),
            'anual' => now()->addYear(),
            default => now()->addMonth(),
        };
    }

    public function pagSeguro(Request $request)
    {
        // PagSeguro V4 sends 'reference_id' in the body directly often.
        \Log::info('PagSeguro webhook', $request->all());
        
        $referenceId = $request->input('reference_id');
        $charges = $request->input('charges');
        
        if ($referenceId && $charges) {
            $order = \App\Models\Order::find($referenceId);
            if ($order) {
                // Check if any charge is PAID
                foreach ($charges as $charge) {
                    if (($charge['status'] ?? '') === 'PAID') {
                         if ($order->status !== 'paid') {
                             $order->update([
                                 'status' => 'paid',
                                 'transaction_id' => $charge['id'] ?? null, // Save Charge ID for refund
                                 'metadata' => array_merge($order->metadata ?? [], ['webhook_data' => $request->all()])
                             ]);
                             \Log::info("Order #{$referenceId} marked as PAID via PS Webhook");
                         }
                         app(CouponService::class)->markOrderRedemptionAsUsed((int) $order->id);
                         app(InvoiceService::class)->issueAndQueueForOrder($order);
                         break;
                    }
                }
            }
        }
        
        return response('OK', 200);
    }
}
