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
        'paid_at',
        'cancelled_at',
        'total_amount',
        'fee_amount',
        'platform_fee_amount',
        'currency',
        'gateway',
        'payment_method',
        'is_manual_approval',
        'manual_approved_by',
        'manual_approved_at',
        'gateway_account_id',
        'metadata',
        'transaction_id',
        'refunded_at'
    ];

    protected $casts = [
        'metadata' => 'array',
        'paid_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'manual_approved_at' => 'datetime',
        'is_manual_approval' => 'boolean',
        'total_amount' => 'decimal:2',
        'fee_amount' => 'decimal:2',
        'platform_fee_amount' => 'decimal:2',
        'refunded_at' => 'datetime',
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

    public function manualApprover()
    {
        return $this->belongsTo(User::class, 'manual_approved_by');
    }

    public function scopeFinancialPaid($query)
    {
        return $query
            ->where('status', 'paid')
            ->where(function ($q) {
                $q->whereNull('is_manual_approval')->orWhere('is_manual_approval', false);
            });
    }

    public function scopeManualApproved($query)
    {
        return $query
            ->where('status', 'paid')
            ->where('is_manual_approval', true);
    }
}
