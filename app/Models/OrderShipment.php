<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderShipment extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'status',
        'service_code',
        'service_name',
        'shipping_amount',
        'delivery_days',
        'tracking_code',
        'recipient_name',
        'recipient_email',
        'recipient_phone',
        'postal_code',
        'address_line',
        'number',
        'complement',
        'neighborhood',
        'city',
        'state',
        'quote_payload',
        'shipped_at',
        'delivered_at',
    ];

    protected $casts = [
        'shipping_amount' => 'decimal:2',
        'delivery_days' => 'integer',
        'quote_payload' => 'array',
        'shipped_at' => 'datetime',
        'delivered_at' => 'datetime',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
