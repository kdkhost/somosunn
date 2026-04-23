<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SumUpWebhookLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'event_type',
        'payload',
        'signature',
        'is_valid',
        'processed_at',
    ];

    protected $casts = [
        'payload'      => 'array',
        'is_valid'     => 'boolean',
        'processed_at' => 'datetime',
    ];
}
