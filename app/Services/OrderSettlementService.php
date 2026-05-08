<?php

namespace App\Services;

use App\Jobs\SendMarketplaceOrderPaidEmailsJob;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Event;
use App\Models\EventRegistration;
use App\Models\Mentorship;
use App\Models\Order;
use App\Models\Plan;
use App\Services\Marketplace\SellerProductFulfillmentService;
use App\Support\EmailQueueSettings;
use App\Notifications\AppNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class OrderSettlementService
{
    /**
     * @param array<string,mixed> $options
     */
    public function settleAsPaid(Order $order, array $options = []): Order
    {
        $isManualApproval = (bool) ($options['manual_approval'] ?? false);
        $approverId = isset($options['approver_id']) ? (int) $options['approver_id'] : null;
        $transactionId = trim((string) ($options['transaction_id'] ?? ''));
        $paymentMethod = trim((string) ($options['payment_method'] ?? ($isManualApproval ? 'manual_approval' : '')));
        $sendNotifications = (bool) ($options['send_notifications'] ?? true);
        $queueInvoiceEmail = (bool) ($options['queue_invoice_email'] ?? true);
        $rawGatewayData = $options['gateway_data'] ?? null;

        $wasPaid = (string) $order->status === 'paid';
        $now = now();

        $metadata = is_array($order->metadata) ? $order->metadata : [];
        if ($rawGatewayData !== null) {
            $metadata['webhook_data'] = $rawGatewayData;
        }

        if ($isManualApproval) {
            $metadata['manual_approval'] = [
                'approved' => true,
                'approved_at' => $now->toIso8601String(),
                'approved_by' => $approverId,
            ];
        }

        $order->status = 'paid';
        if (!$order->paid_at) {
            $order->paid_at = $now;
        }

        if ($transactionId !== '' && (!$order->transaction_id || $isManualApproval)) {
            $order->transaction_id = $transactionId;
        }

        if ($paymentMethod !== '') {
            $order->payment_method = $paymentMethod;
        }

        if ($isManualApproval) {
            $order->is_manual_approval = true;
            $order->manual_approved_by = $approverId;
            $order->manual_approved_at = $now;
        }

        $order->metadata = $metadata;
        $order->save();

        app(CouponService::class)->markOrderRedemptionAsUsed((int) $order->id);
        $this->confirmEventRegistrationsForOrder($order);
        $this->activatePlanForOrder($order);
        $this->fulfillDigitalItemsForOrder($order);
        app(SellerProductFulfillmentService::class)->fulfillPaidOrder($order);

        // Limpar carrinho do comprador após pagamento aprovado
        $this->clearBuyerCart($order);

        $invoice = app(InvoiceService::class)->issueAndQueueForOrder($order, $queueInvoiceEmail);
        if ($invoice && $isManualApproval) {
            $invoiceMeta = is_array($invoice->metadata) ? $invoice->metadata : [];
            $invoiceMeta['manual_approval'] = [
                'approved' => true,
                'approved_at' => $now->toIso8601String(),
                'approved_by' => $approverId,
            ];

            $invoice->status = 'paid';
            if (!$invoice->paid_at) {
                $invoice->paid_at = $now;
            }
            $invoice->metadata = $invoiceMeta;
            $invoice->save();
        }

        if ($sendNotifications && (!$wasPaid || !data_get($order->metadata, 'emails.marketplace_paid_sent_at'))) {
            EmailQueueSettings::dispatch(new SendMarketplaceOrderPaidEmailsJob((int) $order->id));
            $this->notifyUsers($order);
        }

        return $order->fresh(['user', 'seller', 'items', 'invoice']);
    }

    private function notifyUsers(Order $order): void
    {
        $total = number_format((float) ($order->total_amount ?? 0), 2, ',', '.');

        if ($order->seller && !empty($order->seller->email)) {
            $order->seller->notify(new AppNotification([
                'message' => 'Parabens! Voce realizou uma nova venda no valor de R$ ' . $total . '.',
                'type' => 'SaleConfirmed',
                'action_url' => route('panel.marketplace.sales'),
                'action_label' => 'Ver vendas',
            ]));
        }

        if ($order->user) {
            $order->user->notify(new AppNotification([
                'message' => 'Seu pagamento foi confirmado! O acesso aos seus itens foi liberado.',
                'type' => 'PaymentConfirmed',
                'action_url' => route('panel.dashboard'),
                'action_label' => 'Acessar agora',
            ]));
        }
    }

    private function confirmEventRegistrationsForOrder(Order $order): void
    {
        $registrations = EventRegistration::where('order_id', $order->id)->get();
        if ($registrations->isEmpty()) {
            return;
        }

        DB::transaction(function () use ($registrations, $order) {
            $needsManualRefund = false;

            foreach ($registrations as $registration) {
                if (in_array($registration->status, EventRegistration::COUNTED_STATUSES, true)) {
                    continue;
                }
                if ($registration->status === EventRegistration::STATUS_CANCELLED) {
                    continue;
                }

                $event = Event::whereKey($registration->event_id)->lockForUpdate()->first();
                if (!$event) {
                    continue;
                }

                if ($event->capacity && !$event->hasCapacityFor((int) $registration->quantity)) {
                    $registration->update(['status' => EventRegistration::STATUS_CANCELLED]);
                    $needsManualRefund = true;
                    continue;
                }

                $registration->update(['status' => EventRegistration::STATUS_PAID]);
            }

            if ($needsManualRefund) {
                $orderMeta = is_array($order->metadata) ? $order->metadata : [];
                $orderMeta['event_overbooked'] = true;
                $orderMeta['needs_manual_refund'] = true;
                $order->metadata = $orderMeta;
                $order->save();
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
        $periodNormalized = Str::lower(Str::ascii($period));

        if ($periodNormalized === 'vitalicio') {
            return null;
        }

        if (ctype_digit($period)) {
            return now()->addDays((int) $period);
        }

        return match ($periodNormalized) {
            'mensal' => now()->addMonth(),
            'trimestral' => now()->addMonths(3),
            'semestral' => now()->addMonths(6),
            'anual' => now()->addYear(),
            default => now()->addMonth(),
        };
    }

    /**
     * Remove os itens do carrinho do comprador após pagamento aprovado.
     * Só remove itens que estão no pedido (não limpa carrinho inteiro caso
     * haja outros itens de outro vendedor/sessão).
     */
    private function clearBuyerCart(Order $order): void
    {
        if (!\Schema::hasTable('seller_product_cart_items')) {
            return;
        }

        $userId = (int) $order->user_id;
        if (!$userId) {
            return;
        }

        // Pegar IDs dos produtos do pedido (só seller_product)
        $productIds = $order->items()
            ->where('item_type', 'seller_product')
            ->pluck('item_id')
            ->filter()
            ->values();

        if ($productIds->isEmpty()) {
            return;
        }

        \DB::table('seller_product_cart_items')
            ->where('user_id', $userId)
            ->whereIn('product_id', $productIds->toArray())
            ->delete();
    }
}
