<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class EventRegistration extends Model
{
    use HasFactory;

    public const STATUS_PENDING = 'pending';
    public const STATUS_PAID = 'paid';
    public const STATUS_CONFIRMED = 'confirmed';
    public const STATUS_CANCELLED = 'cancelled';

    public const PAYMENT_PENDING = 'pending';
    public const PAYMENT_PAID = 'paid';
    public const PAYMENT_FREE = 'free';
    public const PAYMENT_CANCELLED = 'cancelled';

    public const COUNTED_STATUSES = [
        self::STATUS_PAID,
        self::STATUS_CONFIRMED,
    ];

    protected $fillable = [
        'event_id',
        'user_id',
        'order_id',
        'coupon_id',
        'status',
        'payment_status',
        'price',
        'quantity',
        'ticket_code',
        'check_in_at',
        'joined_group_at',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'quantity' => 'integer',
        'check_in_at' => 'datetime',
        'joined_group_at' => 'datetime',
    ];

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function coupon()
    {
        return $this->belongsTo(EventCoupon::class, 'coupon_id');
    }

    public function isTicketUsed(): bool
    {
        return $this->check_in_at !== null;
    }

    public function isTicketExpired(): bool
    {
        return !$this->isTicketUsed() && (bool) $this->event?->isScannerExpired();
    }

    public function ticketStatusState(): string
    {
        if ($this->isTicketUsed()) {
            return 'used';
        }

        if ($this->isTicketExpired()) {
            return 'expired';
        }

        return 'valid';
    }

    public function ticketStatusMessage(): string
    {
        if ($this->isTicketUsed()) {
            $checkInAt = $this->check_in_at instanceof Carbon
                ? $this->check_in_at
                : Carbon::parse($this->check_in_at);

            return 'Ja utilizado em ' . $checkInAt->format('d/m/Y H:i') . '.';
        }

        if ($this->isTicketExpired()) {
            return 'Ingresso invalido ou expirado. ' . ($this->event?->scannerStatusMessage() ?? '');
        }

        return 'Ingresso valido para leitura.';
    }
}
