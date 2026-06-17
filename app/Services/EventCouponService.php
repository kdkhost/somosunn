<?php

namespace App\Services;

use App\Models\Event;
use App\Models\EventCoupon;
use Illuminate\Validation\ValidationException;

class EventCouponService
{
    public function normalizeCode(?string $code): string
    {
        return EventCoupon::normalizeCodeValue($code);
    }

    public function findPotentialFreeCoupon(Event $event, ?string $code, float $subtotal): ?EventCoupon
    {
        $code = $this->normalizeCode($code);
        if ($code === '') {
            return null;
        }

        $coupon = EventCoupon::query()
            ->where('event_id', (int) $event->id)
            ->where('code', $code)
            ->first();

        if (!$coupon) {
            return null;
        }

        return $this->discountAmount($coupon, $subtotal) >= max(0, $subtotal) - 0.009
            ? $coupon
            : null;
    }

    /**
     * @return array{coupon: EventCoupon, discount_amount: float}
     */
    public function validateFreeCouponLocked(Event $event, string $code, float $subtotal, int $uses = 1): array
    {
        $code = $this->normalizeCode($code);
        if ($code === '') {
            throw ValidationException::withMessages(['coupon_code' => 'Informe um cupom válido.']);
        }

        $coupon = EventCoupon::query()
            ->where('event_id', (int) $event->id)
            ->where('code', $code)
            ->lockForUpdate()
            ->first();

        if (!$coupon || !$coupon->active) {
            throw ValidationException::withMessages(['coupon_code' => 'Cupom inválido ou inativo para este evento.']);
        }

        $now = now();
        if ($coupon->starts_at && $now->lt($coupon->starts_at)) {
            throw ValidationException::withMessages(['coupon_code' => 'Este cupom ainda não está ativo.']);
        }

        if ($coupon->expires_at && $now->gt($coupon->expires_at)) {
            throw ValidationException::withMessages(['coupon_code' => 'Este cupom expirou.']);
        }

        $uses = max(1, (int) $uses);
        if ($coupon->max_uses !== null && ((int) $coupon->used_count + $uses) > (int) $coupon->max_uses) {
            throw ValidationException::withMessages(['coupon_code' => 'Cupom esgotado.']);
        }

        $discountAmount = $this->discountAmount($coupon, $subtotal);
        if ($discountAmount < max(0, $subtotal) - 0.009) {
            throw ValidationException::withMessages(['coupon_code' => 'Este cupom não libera gratuidade integral para o evento.']);
        }

        return [
            'coupon' => $coupon,
            'discount_amount' => round($discountAmount, 2),
        ];
    }

    public function consumeLocked(EventCoupon $coupon, int $uses = 1): void
    {
        $coupon->increment('used_count', max(1, (int) $uses));
    }

    public function discountAmount(EventCoupon $coupon, float $subtotal): float
    {
        $subtotal = max(0, (float) $subtotal);
        $type = (string) $coupon->type;
        $value = (float) $coupon->discount_value;

        if ($type === EventCoupon::TYPE_FREE) {
            return round($subtotal, 2);
        }

        if ($type === EventCoupon::TYPE_PERCENT) {
            return round(min($subtotal, $subtotal * (max(0, min(100, $value)) / 100)), 2);
        }

        return round(min($subtotal, max(0, $value)), 2);
    }
}
