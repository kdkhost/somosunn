<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Event;
use App\Models\EventRegistration;
use App\Models\GatewayAccount;
use App\Models\Mentorship;
use App\Models\Order;
use App\Models\Plan;
use App\Services\CouponService;
use App\Services\InvoiceService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class PaymentWebhookController extends Controller
{
    public function mercadopago(Request $request, $seller_id = 'platform')
    {
        $type = $request->input('type') ?? $request->input('topic');
        if ($type !== 'payment') {
            return response('OK', 200);
        }

        try {
            $paymentId = $request->input('data.id') ?? $request->input('id');
            if (!$paymentId) {
                Log::warning('MP Webhook: missing payment id', ['payload' => $request->all()]);
                return response('OK', 200);
            }

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

            if (!$token) {
                Log::warning('MP Webhook: missing token for seller', ['seller_id' => $sellerId]);
                return response('OK', 200);
            }

            $response = Http::withToken($token)->get('https://api.mercadopago.com/v1/payments/' . $paymentId);
            if (!$response->successful()) {
                Log::warning('MP Webhook: failed to fetch payment', ['payment_id' => $paymentId, 'status' => $response->status()]);
                return response('OK', 200);
            }

            $data = $response->json();
            $orderId = $data['external_reference'] ?? null;
            $status = (string) ($data['status'] ?? '');

            if (!$orderId || $status !== 'approved') {
                return response('OK', 200);
            }

            $order = Order::find($orderId);
            if (!$order) {
                return response('OK', 200);
            }

            $wasPaid = (string) $order->status === 'paid';

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
            $this->fulfillDigitalItemsForOrder($order);
            app(InvoiceService::class)->issueAndQueueForOrder($order);
        } catch (\Throwable $e) {
            Log::error('MP Webhook Error: ' . $e->getMessage(), ['seller_id' => $seller_id]);
        }

        return response('OK', 200);
    }

    public function pagSeguro(Request $request)
    {
        Log::info('PagSeguro webhook', $request->all());

        $referenceId = $request->input('reference_id');
        $charges = $request->input('charges');

        if (!$referenceId || !$charges || !is_array($charges)) {
            return response('OK', 200);
        }

        $order = Order::find($referenceId);
        if (!$order) {
            return response('OK', 200);
        }

        foreach ($charges as $charge) {
            if (($charge['status'] ?? '') !== 'PAID') {
                continue;
            }

            if ((string) $order->status !== 'paid') {
                $order->update([
                    'status' => 'paid',
                    'transaction_id' => $charge['id'] ?? null,
                    'metadata' => array_merge($order->metadata ?? [], ['webhook_data' => $request->all()]),
                ]);
                Log::info("Order #{$referenceId} marked as PAID via PS Webhook");
            }

            app(CouponService::class)->markOrderRedemptionAsUsed((int) $order->id);
            $this->confirmEventRegistrationsForOrder($order);
            $this->activatePlanForOrder($order);
            $this->fulfillDigitalItemsForOrder($order);
            app(InvoiceService::class)->issueAndQueueForOrder($order);
            break;
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

    private function fulfillDigitalItemsForOrder(Order $order): void
    {
        $order->loadMissing('items');

        foreach ($order->items as $item) {
            if ($item->item_type === 'course') {
                $course = Course::find($item->item_id);
                if (!$course) {
                    continue;
                }

                Enrollment::firstOrCreate([
                    'user_id' => $order->user_id,
                    'enrollable_id' => $course->id,
                    'enrollable_type' => Course::class,
                ], [
                    'status' => 'active',
                    'started_at' => now(),
                    'progress' => [],
                ]);
            }

            if ($item->item_type === 'mentorship') {
                $mentorship = Mentorship::find($item->item_id);
                if (!$mentorship) {
                    continue;
                }

                Enrollment::firstOrCreate([
                    'user_id' => $order->user_id,
                    'enrollable_id' => $mentorship->id,
                    'enrollable_type' => Mentorship::class,
                ], [
                    'status' => 'active',
                    'started_at' => now(),
                    'progress' => [],
                ]);
            }
        }
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
}
