<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SumUpTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'checkout_id',
        'transaction_id',
        'status',
        'payment_type',
        'amount',
        'currency',
        'webhook_token',
        'webhook_url',
        'raw_response',
    ];

    protected $casts = [
        'raw_response' => 'array',
        'amount'       => 'decimal:2',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function webhookLogs(): HasMany
    {
        return $this->hasMany(SumUpWebhookLog::class, 'order_id', 'order_id');
    }

    public function isPaid(): bool
    {
        return $this->status === 'PAID';
    }

    public function isPending(): bool
    {
        return $this->status === 'PENDING';
    }

    public function isFailed(): bool
    {
        return $this->status === 'FAILED';
    }
}
