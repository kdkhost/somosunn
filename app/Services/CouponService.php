<?php

namespace App\Services;

use App\Models\Coupon;
use App\Models\CouponRedemption;
use Illuminate\Support\Carbon;
use Illuminate\Database\QueryException;
use Illuminate\Validation\ValidationException;

class CouponService
{
    public const CONTEXT_EVENT = 'event';
    public const CONTEXT_COURSE = 'course';
    public const CONTEXT_MENTORSHIP = 'mentorship';

    public function normalizeCode(?string $code): string
    {
        $code = strtoupper(trim((string) $code));
        $code = preg_replace('/\s+/', '', $code);
        return $code ?: '';
    }

    /**
     * Validates and calculates discount.
     *
     * IMPORTANT: call inside a DB transaction for proper locking.
     *
     * @return array{coupon: Coupon, discount_amount: float}
     */
    public function validateAndCalculateLocked(string $code, string $context, int $contextId, int $userId, float $subtotal, ?int $currentOrderId = null): array
    {
        $code = $this->normalizeCode($code);
        if ($code === '') {
            throw ValidationException::withMessages(['coupon_code' => 'Informe um cupom válido.']);
        }

        $coupon = Coupon::query()->where('code', $code)->lockForUpdate()->first();
        if (!$coupon || !$coupon->is_active) {
            throw ValidationException::withMessages(['coupon_code' => 'Cupom inválido ou inativo.']);
        }

        $now = now();
        if ($coupon->starts_at && $now->lt($coupon->starts_at)) {
            throw ValidationException::withMessages(['coupon_code' => 'Este cupom ainda não está ativo.']);
        }
        if ($coupon->ends_at && $now->gt($coupon->ends_at)) {
            throw ValidationException::withMessages(['coupon_code' => 'Este cupom expirou.']);
        }

        if ($coupon->applies_to !== 'all' && $coupon->applies_to !== $context) {
            throw ValidationException::withMessages(['coupon_code' => 'Este cupom não é válido para este tipo de item.']);
        }
        if ($coupon->applies_to_id && (int) $coupon->applies_to_id !== (int) $contextId) {
            throw ValidationException::withMessages(['coupon_code' => 'Este cupom não é válido para este item.']);
        }

        if ($coupon->min_amount !== null && $subtotal < (float) $coupon->min_amount) {
            $min = number_format((float) $coupon->min_amount, 2, ',', '.');
            throw ValidationException::withMessages(['coupon_code' => 'Valor mínimo para usar este cupom: R$ ' . $min . '.']);
        }

        $activeStatuses = ['reserved', 'used'];
        $activeRedemptions = CouponRedemption::query()
            ->where('coupon_id', $coupon->id)
            ->whereIn('status', $activeStatuses)
            ->where(function ($q) use ($now) {
                $q->whereNull('reserved_until')->orWhere('reserved_until', '>=', $now);
            });

        if ($currentOrderId) {
            $activeRedemptions->where(function ($q) use ($currentOrderId) {
                $q->whereNull('order_id')->orWhere('order_id', '<>', $currentOrderId);
            });
        }

        $totalActiveUses = (int) $activeRedemptions->count();
        if ($coupon->max_uses !== null && $totalActiveUses >= (int) $coupon->max_uses) {
            throw ValidationException::withMessages(['coupon_code' => 'Cupom esgotado.']);
        }

        if ($coupon->max_uses_per_user !== null) {
            $userActiveUses = (int) CouponRedemption::query()
                ->where('coupon_id', $coupon->id)
                ->where('user_id', $userId)
                ->whereIn('status', $activeStatuses)
                ->where(function ($q) use ($now) {
                    $q->whereNull('reserved_until')->orWhere('reserved_until', '>=', $now);
                })
                ->when($currentOrderId, function ($query) use ($currentOrderId) {
                    $query->where(function ($q) use ($currentOrderId) {
                        $q->whereNull('order_id')->orWhere('order_id', '<>', $currentOrderId);
                    });
                })
                ->count();

            if ($userActiveUses >= (int) $coupon->max_uses_per_user) {
                throw ValidationException::withMessages(['coupon_code' => 'Você já atingiu o limite de uso deste cupom.']);
            }
        }

        $discountAmount = $this->calculateDiscountAmount($coupon, $subtotal);

        return [
            'coupon' => $coupon,
            'discount_amount' => $discountAmount,
        ];
    }

    public function reserveRedemption(Coupon $coupon, int $userId, int $orderId, float $discountAmount, ?Carbon $reservedUntil = null): CouponRedemption
    {
        $payload = [
            'coupon_id' => $coupon->id,
            'user_id' => $userId,
            'order_id' => $orderId,
            'status' => 'reserved',
            'discount_amount' => $discountAmount,
            'reserved_until' => $reservedUntil ?? now()->addMinutes(30),
        ];

        $existing = CouponRedemption::query()
            ->where('coupon_id', $coupon->id)
            ->where('order_id', $orderId)
            ->lockForUpdate()
            ->first();

        if ($existing) {
            $existing->fill($payload)->save();
            return $existing->fresh();
        }

        try {
            return CouponRedemption::create($payload);
        } catch (QueryException $e) {
            if (!$this->isDuplicateCouponOrderRedemption($e)) {
                throw $e;
            }

            $existing = CouponRedemption::query()
                ->where('coupon_id', $coupon->id)
                ->where('order_id', $orderId)
                ->lockForUpdate()
                ->first();

            if (!$existing) {
                throw $e;
            }

            $existing->fill($payload)->save();
            return $existing->fresh();
        }
    }

    public function markOrderRedemptionAsUsed(int $orderId): void
    {
        CouponRedemption::query()
            ->where('order_id', $orderId)
            ->where('status', 'reserved')
            ->update([
                'status' => 'used',
                'reserved_until' => null,
            ]);
    }

    /**
     * Returns price splits for MercadoPago rounding (2 decimals) while keeping totals exact.
     *
     * @return array<int, array{unit_price: float, quantity: int}>
     */
    public function splitUnitPrices(float $finalTotal, int $quantity): array
    {
        $quantity = max(1, (int) $quantity);

        $finalCents = (int) round($finalTotal * 100);
        $finalCents = max(0, $finalCents);

        $base = intdiv($finalCents, $quantity);
        $remainder = $finalCents - ($base * $quantity);

        $splits = [];
        if ($remainder > 0) {
            $splits[] = ['unit_price' => ($base + 1) / 100, 'quantity' => $remainder];
        }
        $rest = $quantity - $remainder;
        if ($rest > 0) {
            $splits[] = ['unit_price' => $base / 100, 'quantity' => $rest];
        }

        return $splits;
    }

    private function calculateDiscountAmount(Coupon $coupon, float $subtotal): float
    {
        $subtotal = max(0, (float) $subtotal);

        $type = (string) $coupon->discount_type;
        $value = (float) $coupon->discount_value;

        if ($type === 'percent') {
            $pct = max(0, min(100, $value));
            $discount = $subtotal * ($pct / 100);
        } else {
            $discount = max(0, $value);
        }

        $discount = min($discount, $subtotal);

        return round($discount, 2);
    }

    private function isDuplicateCouponOrderRedemption(QueryException $e): bool
    {
        $message = (string) $e->getMessage();
        $sqlState = is_array($e->errorInfo ?? null) && isset($e->errorInfo[0]) ? (string) $e->errorInfo[0] : '';
        $driverCode = is_array($e->errorInfo ?? null) && isset($e->errorInfo[1]) ? (string) $e->errorInfo[1] : '';

        return $sqlState === '23000'
            && ($driverCode === '1062'
                || str_contains($message, 'coupon_redemptions_coupon_id_order_id')
                || str_contains($message, 'coupon_id_order_id'));
    }
}
