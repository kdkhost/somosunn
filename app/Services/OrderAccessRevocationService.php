<?php

namespace App\Services;

use App\Models\Course;
use App\Models\Enrollment;
use App\Models\EventExhibitorRegistration;
use App\Models\EventRegistration;
use App\Models\Mentorship;
use App\Models\Order;
use App\Models\SellerProduct;
use App\Models\Subscription;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class OrderAccessRevocationService
{
    public function revoke(Order $order, string $reason = 'order_cancelled'): void
    {
        $order->loadMissing(['items', 'user']);

        DB::transaction(function () use ($order, $reason) {
            $now = now();
            $revokedItems = [];

            $this->revokeEventRegistrations($order, $reason, $revokedItems);
            $this->revokeEventExhibitorRegistrations($order, $reason, $revokedItems);
            $this->revokeEnrollments($order, $reason, $revokedItems);
            $this->revokePlans($order, $reason, $revokedItems);
            $this->markOrderItemsRevoked($order, $reason, $revokedItems);
            $this->restoreSellerProductStock($order, $reason, $revokedItems);

            $metadata = is_array($order->metadata) ? $order->metadata : [];
            $previousRevocation = is_array($metadata['access_revocation'] ?? null)
                ? $metadata['access_revocation']
                : [];

            $metadata['access_revocation'] = array_merge($previousRevocation, [
                'status' => 'revoked',
                'reason' => $reason,
                'revoked_at' => $now->toIso8601String(),
                'revoked_by' => auth()->id(),
                'items' => $revokedItems,
            ]);

            $order->forceFill(['metadata' => $metadata])->save();
        });
    }

    private function revokeEventRegistrations(Order $order, string $reason, array &$revokedItems): void
    {
        if (
            !Schema::hasTable('event_registrations')
            || !Schema::hasColumn('event_registrations', 'event_id')
            || !Schema::hasColumn('event_registrations', 'user_id')
            || !Schema::hasColumn('event_registrations', 'status')
        ) {
            return;
        }

        $payload = $this->cancelledEventRegistrationPayload();
        $updated = 0;

        if (Schema::hasColumn('event_registrations', 'order_id')) {
            $updated += EventRegistration::query()
                ->where('order_id', (int) $order->id)
                ->where('status', '!=', EventRegistration::STATUS_CANCELLED)
                ->update($payload);
        }

        $updated += $this->revokeLegacyEventRegistrationsWithoutOrder($order, $payload);

        if ($updated > 0) {
            $revokedItems[] = [
                'type' => 'event',
                'status' => 'cancelled',
                'reason' => $reason,
                'count' => $updated,
            ];
        }
    }

    private function cancelledEventRegistrationPayload(): array
    {
        $payload = ['status' => EventRegistration::STATUS_CANCELLED];
        if (Schema::hasColumn('event_registrations', 'payment_status')) {
            $payload['payment_status'] = EventRegistration::PAYMENT_CANCELLED;
        }

        return $payload;
    }

    private function revokeLegacyEventRegistrationsWithoutOrder(Order $order, array $payload): int
    {
        $eventQuantities = [];
        foreach ($order->items as $item) {
            if (!in_array((string) $item->item_type, ['event', 'event_registration'], true)) {
                continue;
            }

            $eventId = (int) $item->item_id;
            if ($eventId <= 0) {
                continue;
            }

            $eventQuantities[$eventId] = ($eventQuantities[$eventId] ?? 0) + max(1, (int) ($item->quantity ?? 1));
        }

        if ($eventQuantities === [] || !(int) $order->user_id) {
            return 0;
        }

        $updated = 0;
        foreach ($eventQuantities as $eventId => $quantityToRevoke) {
            $query = EventRegistration::query()
                ->where('event_id', $eventId)
                ->where('user_id', (int) $order->user_id)
                ->where('status', '!=', EventRegistration::STATUS_CANCELLED);

            if (Schema::hasColumn('event_registrations', 'order_id')) {
                $query->whereNull('order_id');
            }

            $legacyCount = (clone $query)->count();
            if ($legacyCount <= 0) {
                continue;
            }

            $protectedQuantity = $this->paidEventQuantityExcludingOrder($order, $eventId);
            $limit = min((int) $quantityToRevoke, max(0, $legacyCount - $protectedQuantity));
            if ($limit <= 0) {
                continue;
            }

            $registrationIds = (clone $query)
                ->latest('id')
                ->limit($limit)
                ->pluck('id');

            if ($registrationIds->isEmpty()) {
                continue;
            }

            $updated += EventRegistration::query()
                ->whereIn('id', $registrationIds)
                ->update($payload);
        }

        return $updated;
    }

    private function revokeEventExhibitorRegistrations(Order $order, string $reason, array &$revokedItems): void
    {
        if (
            !Schema::hasTable('event_exhibitor_registrations')
            || !Schema::hasColumn('event_exhibitor_registrations', 'order_id')
            || !Schema::hasColumn('event_exhibitor_registrations', 'status')
        ) {
            return;
        }

        $status = (string) $order->status === 'refunded'
            ? EventExhibitorRegistration::STATUS_REFUNDED
            : EventExhibitorRegistration::STATUS_CANCELLED;
        $paymentStatus = (string) $order->status === 'refunded'
            ? EventExhibitorRegistration::PAYMENT_REFUNDED
            : EventExhibitorRegistration::PAYMENT_CANCELLED;

        $registrations = EventExhibitorRegistration::query()
            ->where('order_id', (int) $order->id)
            ->whereNotIn('status', [
                EventExhibitorRegistration::STATUS_CANCELLED,
                EventExhibitorRegistration::STATUS_REFUNDED,
            ])
            ->get();

        foreach ($registrations as $registration) {
            $metadata = is_array($registration->metadata) ? $registration->metadata : [];
            $metadata['access_revocation'] = [
                'reason' => $reason,
                'revoked_at' => now()->toIso8601String(),
                'revoked_by' => auth()->id(),
            ];

            $payload = [
                'status' => $status,
            ];

            if (Schema::hasColumn('event_exhibitor_registrations', 'payment_status')) {
                $payload['payment_status'] = $paymentStatus;
            }

            if (Schema::hasColumn('event_exhibitor_registrations', 'cancelled_at')) {
                $payload['cancelled_at'] = now();
            }

            if (Schema::hasColumn('event_exhibitor_registrations', 'metadata')) {
                $payload['metadata'] = $metadata;
            }

            $registration->update($payload);
        }

        if ($registrations->isNotEmpty()) {
            $revokedItems[] = [
                'type' => 'event_exhibitor_area',
                'status' => $status,
                'reason' => $reason,
                'count' => $registrations->count(),
            ];
        }
    }

    private function revokeEnrollments(Order $order, string $reason, array &$revokedItems): void
    {
        if (
            !Schema::hasTable('enrollments')
            || !Schema::hasColumn('enrollments', 'user_id')
            || !Schema::hasColumn('enrollments', 'enrollable_type')
            || !Schema::hasColumn('enrollments', 'enrollable_id')
            || !Schema::hasColumn('enrollments', 'status')
        ) {
            return;
        }

        foreach ($order->items as $item) {
            $itemType = (string) $item->item_type;
            $modelClass = match ($itemType) {
                'course' => Course::class,
                'mentorship' => Mentorship::class,
                default => null,
            };

            if ($modelClass === null || $this->hasAnotherPaidOrderForItem($order, $itemType, (int) $item->item_id)) {
                continue;
            }

            $payload = ['status' => 'cancelled'];
            if (Schema::hasColumn('enrollments', 'completed_at')) {
                $payload['completed_at'] = null;
            }

            $updated = Enrollment::query()
                ->where('user_id', (int) $order->user_id)
                ->where('enrollable_type', $modelClass)
                ->where('enrollable_id', (int) $item->item_id)
                ->where('status', '!=', 'cancelled')
                ->update($payload);

            if ($updated > 0) {
                $revokedItems[] = [
                    'type' => $itemType,
                    'item_id' => (int) $item->item_id,
                    'status' => 'cancelled',
                    'reason' => $reason,
                ];
            }
        }
    }

    private function revokePlans(Order $order, string $reason, array &$revokedItems): void
    {
        $user = $order->user;
        if (!$user) {
            return;
        }

        foreach ($order->items as $item) {
            $itemType = (string) $item->item_type;
            if (!in_array($itemType, ['plan', 'subscription'], true)) {
                continue;
            }

            $planId = (int) $item->item_id;
            if ($planId <= 0 || $this->hasAnotherPaidOrderForItem($order, $itemType, $planId)) {
                continue;
            }

            if (
                Schema::hasTable('subscriptions')
                && Schema::hasColumn('subscriptions', 'user_id')
                && Schema::hasColumn('subscriptions', 'plan_id')
                && Schema::hasColumn('subscriptions', 'status')
            ) {
                $payload = ['status' => 'cancelled'];
                if (Schema::hasColumn('subscriptions', 'ends_at')) {
                    $payload['ends_at'] = now();
                }

                Subscription::query()
                    ->where('user_id', (int) $order->user_id)
                    ->where('plan_id', $planId)
                    ->where('status', 'active')
                    ->update($payload);
            }

            if (Schema::hasColumn('users', 'plan_id') && (int) $user->plan_id === $planId) {
                $payload = ['plan_id' => null];
                if (Schema::hasColumn('users', 'plan_expires_at')) {
                    $payload['plan_expires_at'] = null;
                }

                $user->forceFill($payload)->save();
            }

            $revokedItems[] = [
                'type' => $itemType,
                'item_id' => $planId,
                'status' => 'cancelled',
                'reason' => $reason,
            ];
        }
    }

    private function markOrderItemsRevoked(Order $order, string $reason, array &$revokedItems): void
    {
        foreach ($order->items as $item) {
            $data = is_array($item->data) ? $item->data : [];
            if (data_get($data, 'access.status') === 'revoked') {
                continue;
            }

            $data['access'] = [
                'status' => 'revoked',
                'reason' => $reason,
                'revoked_at' => now()->toIso8601String(),
                'revoked_by' => auth()->id(),
            ];

            $item->forceFill(['data' => $data])->save();

            $revokedItems[] = [
                'type' => (string) $item->item_type,
                'item_id' => (int) $item->item_id,
                'order_item_id' => (int) $item->id,
                'status' => 'revoked',
                'reason' => $reason,
            ];
        }
    }

    private function restoreSellerProductStock(Order $order, string $reason, array &$revokedItems): void
    {
        if (!SellerProduct::tableAvailable() || !Schema::hasColumn('seller_products', 'stock')) {
            return;
        }

        $metadata = is_array($order->metadata) ? $order->metadata : [];
        if (!data_get($metadata, 'seller_products.fulfilled_at') || data_get($metadata, 'access_revocation.stock_restored_at')) {
            return;
        }

        $restored = [];
        foreach ($order->items->where('item_type', 'seller_product') as $item) {
            $product = SellerProduct::query()->lockForUpdate()->find((int) $item->item_id);
            if (!$product || !$product->isPhysical() || $product->stock === null) {
                continue;
            }

            $quantity = max(1, (int) ($item->quantity ?? 1));
            $product->stock = (int) $product->stock + $quantity;
            $product->save();

            $restored[] = [
                'product_id' => (int) $product->id,
                'quantity' => $quantity,
            ];
        }

        if ($restored === []) {
            return;
        }

        $metadata['access_revocation']['stock_restored_at'] = now()->toIso8601String();
        $metadata['access_revocation']['stock_restore_reason'] = $reason;
        $metadata['access_revocation']['stock_restored_items'] = $restored;
        $order->forceFill(['metadata' => $metadata])->save();

        $revokedItems[] = [
            'type' => 'seller_product_stock',
            'status' => 'restored',
            'reason' => $reason,
            'items' => $restored,
        ];
    }

    private function paidEventQuantityExcludingOrder(Order $order, int $eventId): int
    {
        return (int) Order::query()
            ->where('user_id', (int) $order->user_id)
            ->where('id', '!=', (int) $order->id)
            ->where('status', 'paid')
            ->whereHas('items', function ($query) use ($eventId) {
                $query->whereIn('item_type', ['event', 'event_registration'])
                    ->where('item_id', $eventId);
            })
            ->with(['items' => function ($query) use ($eventId) {
                $query->whereIn('item_type', ['event', 'event_registration'])
                    ->where('item_id', $eventId);
            }])
            ->get()
            ->sum(function (Order $paidOrder) {
                return $paidOrder->items->sum(fn ($item) => max(1, (int) ($item->quantity ?? 1)));
            });
    }

    private function hasAnotherPaidOrderForItem(Order $order, string $itemType, int $itemId): bool
    {
        $itemTypes = in_array($itemType, ['plan', 'subscription'], true)
            ? ['plan', 'subscription']
            : [$itemType];

        return Order::query()
            ->where('user_id', (int) $order->user_id)
            ->where('id', '!=', (int) $order->id)
            ->where('status', 'paid')
            ->whereHas('items', function ($query) use ($itemTypes, $itemId) {
                $query->whereIn('item_type', $itemTypes)
                    ->where('item_id', $itemId);
            })
            ->exists();
    }
}
