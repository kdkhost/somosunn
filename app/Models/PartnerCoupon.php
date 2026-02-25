<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PartnerCoupon extends Model
{
    use HasFactory;

    protected $fillable = [
        'partner_id',
        'code',
        'title',
        'description',
        'discount_type',
        'discount_value',
        'min_purchase',
        'expires_at',
        'active',
    ];

    protected $casts = [
        'active' => 'boolean',
        'discount_value' => 'decimal:2',
        'min_purchase' => 'decimal:2',
        'expires_at' => 'date',
    ];

    // ── Relações ────────────────────────────────────────────────────────────────
    public function partner()
    {
        return $this->belongsTo(Partner::class);
    }

    // ── Scopes ──────────────────────────────────────────────────────────────────
    public function scopeActive($query)
    {
        return $query->where('active', true)
            ->where(function ($q) {
                $q->whereNull('expires_at')->orWhere('expires_at', '>=', now()->toDateString());
            });
    }

    // ── Accessors ───────────────────────────────────────────────────────────────
    public function getFormattedDiscountAttribute(): string
    {
        if ($this->discount_type === 'percent') {
            return number_format($this->discount_value, 0) . '%';
        }
        return 'R$ ' . number_format($this->discount_value, 2, ',', '.');
    }

    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }
}
