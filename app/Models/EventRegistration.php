<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EventRegistration extends Model
{
    use HasFactory;

    public const STATUS_PENDING = 'pending';
    public const STATUS_PAID = 'paid';
    public const STATUS_CONFIRMED = 'confirmed';
    public const STATUS_CANCELLED = 'cancelled';

    public const COUNTED_STATUSES = [
        self::STATUS_PAID,
        self::STATUS_CONFIRMED,
    ];

    protected $fillable = [
        'event_id',
        'user_id',
        'order_id',
        'status',
        'price',
        'quantity',
        'ticket_code',
        'check_in_at',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'quantity' => 'integer',
        'check_in_at' => 'datetime',
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
}
