<?php

namespace App\Services;

use App\Models\Event;
use App\Models\EventCoupon;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\Log;
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
        if ($code === '' || $event->isClosedForPublic()) {
            return null;
        }

        $coupon = EventCoupon::query()
            ->where('event_id', (int) $event->id)
            ->where('code', $code)
            ->first();

        if (!$coupon || !$this->isCurrentlyAvailable($event, $coupon)) {
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
        $result = $this->validateCouponLocked($event, $code, $subtotal, $uses);

        if ($result['discount_amount'] < max(0, $subtotal) - 0.009) {
            throw ValidationException::withMessages([
                'coupon_code' => 'Este cupom nao libera gratuidade integral para o evento.',
            ]);
        }

        return $result;
    }

    /**
     * @return array{coupon: EventCoupon, discount_amount: float}
     */
    public function validateCouponLocked(Event $event, string $code, float $subtotal, int $uses = 1): array
    {
        if ($event->isClosedForPublic()) {
            throw ValidationException::withMessages([
                'coupon_code' => 'As vendas deste evento ja foram encerradas.',
            ]);
        }

        $code = $this->normalizeCode($code);
        if ($code === '') {
            throw ValidationException::withMessages(['coupon_code' => 'Informe um cupom valido.']);
        }

        $coupon = EventCoupon::query()
            ->where('event_id', (int) $event->id)
            ->where('code', $code)
            ->lockForUpdate()
            ->first();

        if (!$coupon) {
            throw ValidationException::withMessages(['coupon_code' => 'Cupom invalido para este evento.']);
        }

        if (!$coupon->active) {
            throw ValidationException::withMessages(['coupon_code' => 'Cupom inativo.']);
        }

        if ($coupon->starts_at && now()->lt($coupon->starts_at)) {
            throw ValidationException::withMessages(['coupon_code' => 'Este cupom ainda nao esta ativo.']);
        }

        $effectiveExpiresAt = $this->effectiveExpiresAt($event, $coupon);
        if ($effectiveExpiresAt && now()->gt($effectiveExpiresAt)) {
            throw ValidationException::withMessages(['coupon_code' => 'Este cupom expirou.']);
        }

        $uses = max(1, (int) $uses);
        if ($coupon->max_uses !== null && ((int) $coupon->used_count + $uses) > (int) $coupon->max_uses) {
            throw ValidationException::withMessages(['coupon_code' => 'Cupom esgotado.']);
        }

        return [
            'coupon' => $coupon,
            'discount_amount' => round($this->discountAmount($coupon, $subtotal), 2),
        ];
    }

    public function canUse(EventCoupon $coupon, int $uses = 1): bool
    {
        if (!$coupon->active) {
            return false;
        }

        $uses = max(1, (int) $uses);
        $now = now();

        if ($coupon->starts_at && $now->lt($coupon->starts_at)) {
            return false;
        }

        if ($coupon->expires_at && $now->gt($coupon->expires_at)) {
            return false;
        }

        if ($coupon->max_uses !== null && ((int) $coupon->used_count + $uses) > (int) $coupon->max_uses) {
            return false;
        }

        return true;
    }

    public function consumeLocked(EventCoupon $coupon, int $uses = 1): void
    {
        $uses = max(1, (int) $uses);

        if (!$this->canUse($coupon, $uses)) {
            throw ValidationException::withMessages(['coupon_code' => 'Cupom esgotado ou indisponivel.']);
        }

        $coupon->increment('used_count', $uses);

        Log::info('Uso de cupom de evento registrado', [
            'event_id' => $coupon->event_id,
            'coupon_id' => $coupon->id,
            'uses' => $uses,
            'used_count' => (int) $coupon->fresh()->used_count,
        ]);
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

    public function effectiveExpiresAt(Event $event, EventCoupon $coupon): ?CarbonInterface
    {
        $eventDeadline = $event->publicDeadlineAt();
        $couponDeadline = $coupon->expires_at;

        if ($eventDeadline && $couponDeadline) {
            return $eventDeadline->lte($couponDeadline) ? $eventDeadline : $couponDeadline;
        }

        return $couponDeadline ?: $eventDeadline;
    }

    private function isCurrentlyAvailable(Event $event, EventCoupon $coupon): bool
    {
        if (!$coupon->active) {
            return false;
        }

        $now = now();
        if ($coupon->starts_at && $now->lt($coupon->starts_at)) {
            return false;
        }

        $effectiveExpiresAt = $this->effectiveExpiresAt($event, $coupon);
        if ($effectiveExpiresAt && $now->gt($effectiveExpiresAt)) {
            return false;
        }

        if ($coupon->max_uses !== null && (int) $coupon->used_count >= (int) $coupon->max_uses) {
            return false;
        }

        return true;
    }
}
