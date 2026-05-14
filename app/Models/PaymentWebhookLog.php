<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentWebhookLog extends Model
{
    protected $fillable = [
        'provider',
        'external_id',
        'request_id',
        'signature',
        'status',
        'payload',
        'ip',
    ];

    protected $casts = [
        'payload' => 'array',
    ];
}
