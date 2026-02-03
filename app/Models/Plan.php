<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Plan extends Model
{
    use HasFactory;

    protected $fillable = [
        'name','price','period','image','highlight','coupons_enabled','benefits','permissions','is_active'
    ];

    protected $casts = [
        'highlight' => 'boolean',
        'coupons_enabled' => 'boolean',
        'is_active' => 'boolean',
        'benefits' => 'array',
        'permissions' => 'array',
        'price' => 'decimal:2'
    ];
}