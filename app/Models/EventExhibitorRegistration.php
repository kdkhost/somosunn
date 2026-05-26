<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EventExhibitorRegistration extends Model
{
    use HasFactory;
    use SoftDeletes;

    public const STATUS_PENDING = 'pending';
    public const STATUS_RESERVED = 'reserved';
    public const STATUS_PAID = 'paid';
    public const STATUS_CONFIRMED = 'confirmed';
    public const STATUS_CANCELLED = 'cancelled';
    public const STATUS_REFUNDED = 'refunded';
    public const STATUS_EXPIRED = 'expired';

    public const PAYMENT_PENDING = 'pending';
    public const PAYMENT_PAID = 'paid';
    public const PAYMENT_FAILED = 'failed';
    public const PAYMENT_CANCELLED = 'cancelled';
    public const PAYMENT_REFUNDED = 'refunded';
    public const PAYMENT_EXPIRED = 'expired';

    public const COUNTED_STATUSES = [
        self::STATUS_PAID,
        self::STATUS_CONFIRMED,
        self::STATUS_RESERVED,
    ];

    protected $fillable = [
        'event_id',
        'user_id',
        'order_id',
        'name',
        'email',
        'phone',
        'document',
        'company_name',
        'company_document',
        'brand_name',
        'description',
        'quantity',
        'unit_price',
        'total_price',
        'batch_label',
        'status',
        'payment_status',
        'paid_at',
        'cancelled_at',
        'metadata',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'unit_price' => 'decimal:2',
        'total_price' => 'decimal:2',
        'paid_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'metadata' => 'array',
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

    public function getReserveExpiresAtAttribute(): ?\Illuminate\Support\Carbon
    {
        $value = data_get($this->metadata, 'reserve_expires_at');

        return $value ? \Illuminate\Support\Carbon::parse($value) : null;
    }
}
