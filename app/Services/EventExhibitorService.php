<?php

namespace App\Services;

use App\Models\Event;
use App\Models\EventCoupon;
use App\Models\EventExhibitorRegistration;
use App\Models\EventRegistration;
use App\Models\Order;
use App\Models\Setting;
use App\Support\MarketplaceFee;
use Carbon\CarbonInterface;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use RuntimeException;

class EventExhibitorService
{
    public const ORDER_ITEM_TYPE = 'event_exhibitor_area';
    public const ORDER_CONTEXT = 'event_exhibitor';
    public const ORDER_SALE_TYPE = 'event_exhibitor_area';

    public function reserveMinutes(): int
    {
        return max(5, min(180, (int) (Setting::get('event_exhibitor_reserve_minutes', 30) ?: 30)));
    }

    public function currentBatch(Event $event, ?CarbonInterface $reference = null): ?array
    {
        $now = $this->asCarbon($reference);

        for ($number = 1; $number <= 3; $number++) {
            $price = $event->getAttribute("exhibitor_batch_{$number}_price");
            if ($price === null || (float) $price < 0.01) {
                continue;
            }

            $deadline = $this->dateAttribute($event, "exhibitor_batch_{$number}_deadline");
            if ($deadline && $deadline->lt($now)) {
                continue;
            }

            $label = $number . 'o Lote';
            $limit = (int) ($event->getAttribute("exhibitor_batch_{$number}_slots") ?? 0);
            if ($limit > 0 && $this->countedSlots($event, $now, $label) >= $limit) {
                continue;
            }

            return [
                'number' => $number,
                'label' => $label,
                'price' => round((float) $price, 2),
                'deadline' => $deadline,
                'slots' => $limit > 0 ? $limit : null,
            ];
        }

        return null;
    }

    public function countedSlots(Event $event, ?CarbonInterface $reference = null, ?string $batchLabel = null): int
    {
        $now = $this->asCarbon($reference);

        $query = EventExhibitorRegistration::query()
            ->with('order:id,status')
            ->where('event_id', (int) $event->id)
            ->whereIn('status', EventExhibitorRegistration::COUNTED_STATUSES);

        if ($batchLabel !== null) {
            $query->where('batch_label', $batchLabel);
        }

        return $query->get()
            ->filter(fn (EventExhibitorRegistration $registration) => $this->registrationCounts($registration, $now))
            ->sum(fn (EventExhibitorRegistration $registration) => max(1, (int) $registration->quantity));
    }

    public function remainingSlots(Event $event, ?CarbonInterface $reference = null): int
    {
        $total = (int) ($event->exhibitor_total_slots ?? 0);
        if ($total <= 0) {
            return 0;
        }

        return max(0, $total - $this->countedSlots($event, $reference));
    }

    public function hasSlotsFor(Event $event, int $quantity, ?CarbonInterface $reference = null): bool
    {
        $quantity = max(1, $quantity);
        $batch = $this->currentBatch($event, $reference);
        if (!$batch) {
            return false;
        }

        if ($this->remainingSlots($event, $reference) < $quantity) {
            return false;
        }

        $batchLimit = (int) ($batch['slots'] ?? 0);
        if ($batchLimit > 0) {
            $availableInBatch = max(0, $batchLimit - $this->countedSlots($event, $reference, $batch['label']));
            return $availableInBatch >= $quantity;
        }

        return true;
    }

    public function isSalesActive(Event $event, ?CarbonInterface $reference = null): bool
    {
        return $this->status($event, $reference)['key'] === 'ativo';
    }

    public function status(Event $event, ?CarbonInterface $reference = null): array
    {
        $now = $this->asCarbon($reference);

        if (!(bool) ($event->exhibitor_sales_enabled ?? false)) {
            return $this->statusPayload('inativo', 'Inativo', 'secondary');
        }

        if ((int) ($event->exhibitor_total_slots ?? 0) <= 0 || !$this->hasAnyConfiguredBatch($event)) {
            return $this->statusPayload('sem_configuracao', 'Sem configuracao', 'warning');
        }

        if (!$event->published || $event->isClosedForPublic($now)) {
            return $this->statusPayload('encerrado_por_data', 'Encerrado por data', 'dark');
        }

        if ($this->remainingSlots($event, $now) <= 0) {
            return $this->statusPayload('esgotado', 'Esgotado', 'danger');
        }

        $batch = $this->currentBatch($event, $now);
        if (!$batch) {
            return $this->allConfiguredDeadlinesExpired($event, $now)
                ? $this->statusPayload('encerrado_por_data', 'Encerrado por data', 'dark')
                : $this->statusPayload('esgotado', 'Esgotado', 'danger');
        }

        return $this->statusPayload('ativo', 'Ativo', 'success');
    }

    /**
     * @return array{order:Order,registration:EventExhibitorRegistration,batch:array}
     */
    public function createReservation(
        Event $event,
        \App\Models\User $user,
        array $payload,
        ?string $gatewayProvider,
        ?array $couponData = null
    ): array
    {
        $quantity = max(1, min(20, (int) ($payload['quantity'] ?? 1)));

        return DB::transaction(function () use ($event, $user, $payload, $gatewayProvider, $quantity, $couponData) {
            $lockedEvent = Event::query()->whereKey($event->id)->lockForUpdate()->firstOrFail();
            $this->expireInvalidReservations($lockedEvent);

            if (!$this->isSalesActive($lockedEvent)) {
                throw new RuntimeException('A venda de areas para expositores nao esta disponivel neste evento.');
            }

            $batch = $this->currentBatch($lockedEvent);
            if (!$batch || !$this->hasSlotsFor($lockedEvent, $quantity)) {
                throw new RuntimeException('Nao ha areas de expositor disponiveis para a quantidade solicitada.');
            }

            $unitPrice = round((float) $batch['price'], 2);
            $totalPrice = round($unitPrice * $quantity, 2);
            $discountAmount = round(min($totalPrice, (float) data_get($couponData, 'discount_amount', 0)), 2);
            $payableTotal = round(max(0, $totalPrice - $discountAmount), 2);
            $netUnitPrice = $quantity > 0 ? round($payableTotal / $quantity, 2) : $payableTotal;
            $sellerId = (int) ($lockedEvent->user_id ?? 0);
            $platformFeePercent = MarketplaceFee::deductionPercent($sellerId > 0 ? $sellerId : null);
            $platformFeeAmount = MarketplaceFee::deductionAmount($payableTotal, $sellerId > 0 ? $sellerId : null);
            $reserveExpiresAt = now()->addMinutes($this->reserveMinutes());
            $coupon = data_get($couponData, 'coupon');

            if ($coupon instanceof EventCoupon) {
                $coupon = EventCoupon::query()
                    ->whereKey($coupon->id)
                    ->lockForUpdate()
                    ->firstOrFail();

                app(EventCouponService::class)->assertCouponAvailableForContext(
                    $lockedEvent,
                    $coupon,
                    EventCoupon::APPLIES_EXHIBITOR,
                    (int) $user->id,
                    $quantity
                );
            }

            $couponPayload = $coupon ? [
                'id' => (int) $coupon->id,
                'code' => (string) $coupon->code,
                'type' => (string) $coupon->type,
                'applies_to' => (string) ($coupon->applies_to ?: EventCoupon::APPLIES_ATTENDEE),
                'discount_value' => (float) $coupon->discount_value,
                'discount_amount' => $discountAmount,
                'uses' => $quantity,
            ] : null;

            $order = Order::create([
                'user_id' => (int) $user->id,
                'seller_id' => $sellerId > 0 ? $sellerId : null,
                'status' => 'pending',
                'total_amount' => $payableTotal,
                'fee_amount' => 0,
                'platform_fee_amount' => $platformFeeAmount,
                'currency' => 'BRL',
                'gateway' => $payableTotal <= 0 ? 'free' : $gatewayProvider,
                'gateway_account_id' => null,
                'metadata' => [
                    'context' => self::ORDER_CONTEXT,
                    'sale_type' => self::ORDER_SALE_TYPE,
                    'public_token' => Str::random(40),
                    'event_id' => (int) $lockedEvent->id,
                    'platform_fee_percent' => $platformFeePercent,
                    'reserve_expires_at' => $reserveExpiresAt->toIso8601String(),
                    'is_free_checkout' => $payableTotal <= 0,
                    'original_total_amount' => $totalPrice,
                    'discount_amount' => $discountAmount,
                    'event_coupon' => $couponPayload,
                ],
            ]);

            $registration = EventExhibitorRegistration::create([
                'event_id' => (int) $lockedEvent->id,
                'user_id' => (int) $user->id,
                'order_id' => (int) $order->id,
                'name' => (string) ($payload['name'] ?? $user->name),
                'email' => (string) ($payload['email'] ?? $user->email),
                'phone' => (string) ($payload['phone'] ?? $user->phone ?? ''),
                'document' => (string) ($payload['document'] ?? $user->doc ?? ''),
                'company_name' => (string) ($payload['company_name'] ?? ''),
                'company_document' => (string) ($payload['company_document'] ?? ''),
                'brand_name' => (string) ($payload['brand_name'] ?? ''),
                'description' => (string) ($payload['description'] ?? ''),
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
                'total_price' => $totalPrice,
                'batch_label' => (string) $batch['label'],
                'status' => EventExhibitorRegistration::STATUS_RESERVED,
                'payment_status' => EventExhibitorRegistration::PAYMENT_PENDING,
                'metadata' => [
                    'reserve_expires_at' => $reserveExpiresAt->toIso8601String(),
                    'includes_ticket' => (bool) $lockedEvent->exhibitor_includes_ticket,
                    'show_publicly' => (bool) ($lockedEvent->exhibitor_show_publicly ?? true),
                    'ip' => request()?->ip(),
                    'original_total_amount' => $totalPrice,
                    'payable_total_amount' => $payableTotal,
                    'discount_amount' => $discountAmount,
                    'event_coupon' => $couponPayload,
                ],
            ]);

            $reference = $this->gatewayReference($lockedEvent, $registration, $order);
            $order->items()->create([
                'item_type' => self::ORDER_ITEM_TYPE,
                'item_id' => (int) $lockedEvent->id,
                'title' => 'Area para expositor - ' . $lockedEvent->title,
                'price' => $netUnitPrice,
                'quantity' => $quantity,
                'data' => [
                    'event_id' => (int) $lockedEvent->id,
                    'registration_id' => (int) $registration->id,
                    'batch_label' => (string) $batch['label'],
                    'unit_price' => $netUnitPrice,
                    'total_price' => $payableTotal,
                    'original_unit_price' => $unitPrice,
                    'original_total_price' => $totalPrice,
                    'discount_amount' => $discountAmount,
                    'gateway_reference' => $reference,
                    'includes_ticket' => (bool) $lockedEvent->exhibitor_includes_ticket,
                    'event_coupon' => $couponPayload,
                ],
            ]);

            $order->metadata = array_merge($order->metadata ?? [], [
                'gateway_reference' => $reference,
                'event_exhibitor_registration_id' => (int) $registration->id,
                'exhibitor_batch_label' => (string) $batch['label'],
            ]);
            $order->save();

            if ($coupon) {
                app(EventCouponService::class)->consumeLocked($coupon, $quantity);
            }

            return [
                'order' => $order->fresh(['items', 'user']),
                'registration' => $registration->fresh(['event', 'order']),
                'batch' => $batch,
            ];
        });
    }

    public function confirmPaidOrder(Order $order): void
    {
        $registrations = EventExhibitorRegistration::query()
            ->where('order_id', (int) $order->id)
            ->get();

        if ($registrations->isEmpty()) {
            return;
        }

        DB::transaction(function () use ($registrations, $order) {
            $needsManualRefund = false;

            foreach ($registrations as $registration) {
                if (in_array((string) $registration->status, [
                    EventExhibitorRegistration::STATUS_PAID,
                    EventExhibitorRegistration::STATUS_CONFIRMED,
                ], true)) {
                    continue;
                }

                if (in_array((string) $registration->status, [
                    EventExhibitorRegistration::STATUS_CANCELLED,
                    EventExhibitorRegistration::STATUS_REFUNDED,
                ], true)) {
                    continue;
                }

                $event = Event::query()->whereKey($registration->event_id)->lockForUpdate()->first();
                if (!$event) {
                    continue;
                }

                $this->expireInvalidReservations($event);
                $remainingWithoutCurrent = $this->remainingSlotsIgnoringRegistration($event, $registration);
                if ($remainingWithoutCurrent < (int) $registration->quantity) {
                    $registration->update([
                        'status' => EventExhibitorRegistration::STATUS_CANCELLED,
                        'payment_status' => EventExhibitorRegistration::PAYMENT_CANCELLED,
                        'cancelled_at' => now(),
                        'metadata' => array_merge($registration->metadata ?? [], [
                            'cancelled_reason' => 'overselling_guard',
                            'cancelled_at' => now()->toIso8601String(),
                        ]),
                    ]);
                    $needsManualRefund = true;
                    continue;
                }

                $registration->update([
                    'status' => EventExhibitorRegistration::STATUS_PAID,
                    'payment_status' => EventExhibitorRegistration::PAYMENT_PAID,
                    'paid_at' => $registration->paid_at ?: now(),
                ]);

                if ($this->shouldIssueIncludedTicket($event, $registration)) {
                    $this->issueIncludedTicket($event, $registration, $order);
                }
            }

            if ($needsManualRefund) {
                $order->metadata = array_merge($order->metadata ?? [], [
                    'event_exhibitor_overbooked' => true,
                    'needs_manual_refund' => true,
                ]);
                $order->save();
            }
        });
    }

    public function releaseOrder(Order $order, string $reason = 'released'): void
    {
        EventExhibitorRegistration::query()
            ->where('order_id', (int) $order->id)
            ->whereIn('status', [
                EventExhibitorRegistration::STATUS_PENDING,
                EventExhibitorRegistration::STATUS_RESERVED,
            ])
            ->get()
            ->each(function (EventExhibitorRegistration $registration) use ($reason) {
                $status = $reason === 'expired'
                    ? EventExhibitorRegistration::STATUS_EXPIRED
                    : EventExhibitorRegistration::STATUS_CANCELLED;
                $paymentStatus = $reason === 'expired'
                    ? EventExhibitorRegistration::PAYMENT_EXPIRED
                    : EventExhibitorRegistration::PAYMENT_CANCELLED;

                $registration->update([
                    'status' => $status,
                    'payment_status' => $paymentStatus,
                    'cancelled_at' => now(),
                    'metadata' => array_merge($registration->metadata ?? [], [
                        'release_reason' => $reason,
                        'released_at' => now()->toIso8601String(),
                    ]),
                ]);
            });
    }

    public function markOrderRefunded(Order $order, bool $fullRefund): void
    {
        EventExhibitorRegistration::query()
            ->where('order_id', (int) $order->id)
            ->get()
            ->each(function (EventExhibitorRegistration $registration) use ($fullRefund) {
                $metadata = array_merge($registration->metadata ?? [], [
                    'refund_last_at' => now()->toIso8601String(),
                    'refund_full' => $fullRefund,
                ]);

                $registration->update([
                    'status' => $fullRefund ? EventExhibitorRegistration::STATUS_REFUNDED : $registration->status,
                    'payment_status' => EventExhibitorRegistration::PAYMENT_REFUNDED,
                    'metadata' => $metadata,
                ]);
            });
    }

    public function expireInvalidReservations(?Event $event = null): int
    {
        $query = EventExhibitorRegistration::query()
            ->with('order:id,status')
            ->where('status', EventExhibitorRegistration::STATUS_RESERVED);

        if ($event) {
            $query->where('event_id', (int) $event->id);
        }

        $count = 0;
        foreach ($query->get() as $registration) {
            $expiresAt = $registration->reserve_expires_at;
            $orderStatus = (string) ($registration->order?->status ?? '');
            $expiredByTime = $expiresAt && $expiresAt->lt(now());
            $expiredByOrder = in_array($orderStatus, ['cancelled', 'failed', 'refunded'], true);

            if (!$expiredByTime && !$expiredByOrder) {
                continue;
            }

            $registration->update([
                'status' => EventExhibitorRegistration::STATUS_EXPIRED,
                'payment_status' => EventExhibitorRegistration::PAYMENT_EXPIRED,
                'cancelled_at' => now(),
                'metadata' => array_merge($registration->metadata ?? [], [
                    'expired_at' => now()->toIso8601String(),
                    'expired_reason' => $expiredByOrder ? 'order_' . $orderStatus : 'reserve_timeout',
                ]),
            ]);
            $count++;
        }

        return $count;
    }

    public function gatewayReference(Event $event, EventExhibitorRegistration $registration, Order $order): string
    {
        return 'EXHIBITOR-' . (int) $event->id . '-' . (int) $registration->id . '-' . (int) $order->id;
    }

    public function resolveOrderFromReference(?string $reference): ?Order
    {
        $reference = trim((string) $reference);
        if ($reference === '') {
            return null;
        }

        if (ctype_digit($reference)) {
            return Order::find((int) $reference);
        }

        if (preg_match('/^EXHIBITOR-(\d+)-(\d+)-(\d+)$/', $reference, $matches)) {
            return Order::find((int) $matches[3]);
        }

        return Order::query()
            ->where('metadata->gateway_reference', $reference)
            ->first();
    }

    private function registrationCounts(EventExhibitorRegistration $registration, Carbon $now): bool
    {
        if (in_array((string) $registration->status, [
            EventExhibitorRegistration::STATUS_PAID,
            EventExhibitorRegistration::STATUS_CONFIRMED,
        ], true)) {
            return true;
        }

        if ((string) $registration->status !== EventExhibitorRegistration::STATUS_RESERVED) {
            return false;
        }

        $expiresAt = $registration->reserve_expires_at;
        if (!$expiresAt || $expiresAt->lt($now)) {
            return false;
        }

        return (string) ($registration->order?->status ?? '') === 'pending';
    }

    private function shouldIssueIncludedTicket(Event $event, EventExhibitorRegistration $registration): bool
    {
        if ((bool) $event->exhibitor_includes_ticket) {
            return true;
        }

        $couponScope = (string) data_get($registration->metadata, 'event_coupon.applies_to', '');

        return in_array($couponScope, [EventCoupon::APPLIES_EXHIBITOR, EventCoupon::APPLIES_BOTH], true);
    }

    private function issueIncludedTicket(Event $event, EventExhibitorRegistration $registration, Order $order): void
    {
        $payload = [
            'event_id' => (int) $event->id,
            'user_id' => (int) $registration->user_id,
            'order_id' => (int) $order->id,
            'coupon_id' => (int) data_get($registration->metadata, 'event_coupon.id') ?: null,
            'status' => EventRegistration::STATUS_PAID,
            'payment_status' => EventRegistration::PAYMENT_PAID,
            'price' => 0,
            'quantity' => 1,
            'ticket_code' => $event->is_ticket_enabled ? Str::uuid()->toString() : null,
        ];

        $existing = EventRegistration::query()
            ->where('event_id', (int) $event->id)
            ->where('user_id', (int) $registration->user_id)
            ->where('order_id', (int) $order->id)
            ->lockForUpdate()
            ->first();

        if ($existing) {
            if ($event->is_ticket_enabled && !$existing->ticket_code) {
                $payload['ticket_code'] = Str::uuid()->toString();
            } else {
                unset($payload['ticket_code']);
            }

            $existing->update($payload);
            return;
        }

        EventRegistration::create($payload);
    }

    private function remainingSlotsIgnoringRegistration(Event $event, EventExhibitorRegistration $ignored): int
    {
        $total = (int) ($event->exhibitor_total_slots ?? 0);
        if ($total <= 0) {
            return 0;
        }

        $counted = EventExhibitorRegistration::query()
            ->with('order:id,status')
            ->where('event_id', (int) $event->id)
            ->where('id', '!=', (int) $ignored->id)
            ->whereIn('status', EventExhibitorRegistration::COUNTED_STATUSES)
            ->get()
            ->filter(fn (EventExhibitorRegistration $registration) => $this->registrationCounts($registration, now()))
            ->sum(fn (EventExhibitorRegistration $registration) => max(1, (int) $registration->quantity));

        return max(0, $total - $counted);
    }

    private function hasAnyConfiguredBatch(Event $event): bool
    {
        for ($number = 1; $number <= 3; $number++) {
            if ((float) ($event->getAttribute("exhibitor_batch_{$number}_price") ?? 0) > 0) {
                return true;
            }
        }

        return false;
    }

    private function allConfiguredDeadlinesExpired(Event $event, Carbon $now): bool
    {
        $hasConfigured = false;

        for ($number = 1; $number <= 3; $number++) {
            if ((float) ($event->getAttribute("exhibitor_batch_{$number}_price") ?? 0) <= 0) {
                continue;
            }

            $hasConfigured = true;
            $deadline = $this->dateAttribute($event, "exhibitor_batch_{$number}_deadline");
            if (!$deadline || $deadline->gte($now)) {
                return false;
            }
        }

        return $hasConfigured;
    }

    private function statusPayload(string $key, string $label, string $badge): array
    {
        return compact('key', 'label', 'badge');
    }

    private function dateAttribute(Event $event, string $attribute): ?Carbon
    {
        $value = $event->getAttribute($attribute);

        if (!$value) {
            return null;
        }

        return $value instanceof CarbonInterface ? Carbon::instance($value) : Carbon::parse($value);
    }

    private function asCarbon(?CarbonInterface $reference): Carbon
    {
        return $reference ? Carbon::instance($reference) : now();
    }
}
