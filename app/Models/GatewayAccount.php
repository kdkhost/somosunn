<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GatewayAccount extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'provider', // mercadopago, pagseguro
        'public_key',
        'access_token',
        'client_id',
        'client_secret',
        'webhook_secret',
        'pix_key',
        'enabled',
        'extra',
    ];

    protected $casts = [
        'enabled' => 'boolean',
        'extra' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
