<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VisitorLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'ip_hash',
        'ip',
        'country',
        'region',
        'city',
        'latitude',
        'longitude',
        'timezone',
        'method',
        'path',
        'url',
        'referrer',
        'user_agent',
    ];

    protected $casts = [
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

