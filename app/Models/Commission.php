<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Commission extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'seller_id',
        'total_amount',
        'platform_fee_amount',
        'seller_amount',
        'currency',
        'gateway',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
        'total_amount' => 'decimal:2',
        'platform_fee_amount' => 'decimal:2',
        'seller_amount' => 'decimal:2',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function seller()
    {
        return $this->belongsTo(User::class, 'seller_id');
    }
}
