<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderSplit extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'receiver_type',
        'receiver_id',
        'amount',
        'percentage',
        'status',
        'pix_key',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'percentage' => 'decimal:2',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function receiver()
    {
        return $this->belongsTo(User::class, 'receiver_id');
    }

    public function payout()
    {
        return $this->hasOne(OrderSplitPayout::class);
    }
}
