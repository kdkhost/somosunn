<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EventCoupon extends Model
{
    use HasFactory;

    public const TYPE_FREE = 'free';
    public const TYPE_PERCENT = 'percent';
    public const TYPE_FIXED = 'fixed';

    protected $fillable = [
        'event_id',
        'code',
        'type',
        'discount_value',
        'max_uses',
        'used_count',
        'starts_at',
        'expires_at',
        'active',
        'created_by',
    ];

    protected $casts = [
        'discount_value' => 'decimal:2',
        'max_uses' => 'integer',
        'used_count' => 'integer',
        'starts_at' => 'datetime',
        'expires_at' => 'datetime',
        'active' => 'boolean',
    ];

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function registrations()
    {
        return $this->hasMany(EventRegistration::class, 'coupon_id');
    }

    public function normalizeCode(): void
    {
        $this->code = self::normalizeCodeValue($this->code);
    }

    public static function normalizeCodeValue(?string $code): string
    {
        $code = strtoupper(trim((string) $code));
        $code = preg_replace('/\s+/', '', $code);

        return $code ?: '';
    }

    public function remainingUses(): ?int
    {
        if ($this->max_uses === null) {
            return null;
        }

        return max(0, (int) $this->max_uses - (int) $this->used_count);
    }
}
