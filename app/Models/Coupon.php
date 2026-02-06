<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Coupon extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'description',
        'discount_type', // percent|fixed
        'discount_value',
        'is_active',
        'applies_to', // all|event|course|mentorship
        'applies_to_id',
        'min_amount',
        'max_uses',
        'max_uses_per_user',
        'starts_at',
        'ends_at',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'discount_value' => 'decimal:2',
        'min_amount' => 'decimal:2',
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
    ];

    public function redemptions()
    {
        return $this->hasMany(CouponRedemption::class);
    }

    public function normalizeCode(): void
    {
        $this->code = strtoupper(trim((string) $this->code));
        $this->code = preg_replace('/\s+/', '', $this->code);
    }
}

