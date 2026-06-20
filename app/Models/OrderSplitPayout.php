<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderSplitPayout extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_split_id',
        'provider',
        'status',
        'amount',
        'pix_key',
        'external_id',
        'attempts',
        'last_error',
        'notes',
        'last_attempt_at',
        'processed_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'attempts' => 'integer',
        'last_attempt_at' => 'datetime',
        'processed_at' => 'datetime',
    ];

    public function split()
    {
        return $this->belongsTo(OrderSplit::class, 'order_split_id');
    }
}
