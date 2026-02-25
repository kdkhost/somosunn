<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Partner extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'logo',
        'website_url',
        'description',
        'active',
        'order',
    ];

    protected $casts = [
        'active' => 'boolean',
        'order' => 'integer',
    ];

    // ── Relações ────────────────────────────────────────────────────────────────
    public function coupons()
    {
        return $this->hasMany(PartnerCoupon::class)->orderBy('active', 'desc')->orderBy('expires_at');
    }

    public function activeCoupons()
    {
        return $this->hasMany(PartnerCoupon::class)
            ->where('active', true)
            ->where(function ($q) {
                $q->whereNull('expires_at')->orWhere('expires_at', '>=', now()->toDateString());
            });
    }

    // ── Scopes ──────────────────────────────────────────────────────────────────
    public function scopeActive($query)
    {
        return $query->where('active', true)->orderBy('order')->orderBy('name');
    }

    // ── Accessors ───────────────────────────────────────────────────────────────
    public function getLogoUrlAttribute(): string
    {
        if (!$this->logo) {
            return '';
        }
        if (str_starts_with($this->logo, 'http')) {
            return $this->logo;
        }
        return Storage::disk('public')->url($this->logo);
    }

    // ── Boot ─────────────────────────────────────────────────────────────────────
    protected static function boot()
    {
        parent::boot();
        static::creating(function ($partner) {
            if (empty($partner->slug)) {
                $partner->slug = Str::slug($partner->name);
            }
        });
        static::updating(function ($partner) {
            if ($partner->isDirty('name') && !$partner->isDirty('slug')) {
                $partner->slug = Str::slug($partner->name);
            }
        });
    }
}
