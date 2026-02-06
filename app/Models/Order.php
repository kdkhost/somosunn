<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'seller_id',
        'status',
        'total_amount',
        'fee_amount',
        'platform_fee_amount',
        'currency',
        'gateway',
        'gateway_account_id',
        'metadata',
        'transaction_id',
        'refunded_at'
    ];

    protected $casts = [
        'metadata' => 'array',
        'total_amount' => 'decimal:2',
        'fee_amount' => 'decimal:2',
        'platform_fee_amount' => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class); // Buyer
    }

    public function seller()
    {
        return $this->belongsTo(User::class, 'seller_id');
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function invoice()
    {
        return $this->hasOne(Invoice::class);
    }
    
    public function gatewayAccount()
    {
        return $this->belongsTo(GatewayAccount::class);
    }
}
