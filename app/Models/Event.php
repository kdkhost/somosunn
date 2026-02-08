<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title','speaker','description','image','start_at','end_at','location','address','latitude','longitude',
        'price','capacity','published', 'color', 'all_day',
        'batch_1_price', 'batch_1_deadline',
        'batch_2_price', 'batch_2_deadline',
        'batch_3_price', 'batch_3_deadline'
    ];

    protected $casts = [
        'start_at' => 'datetime',
        'end_at' => 'datetime',
        'batch_1_deadline' => 'datetime',
        'batch_2_deadline' => 'datetime',
        'batch_3_deadline' => 'datetime',
        'all_day' => 'boolean',
        'published' => 'boolean'
    ];

    protected $appends = ['start', 'end'];

    public function getStartAttribute()
    {
        return $this->start_at instanceof \DateTime ? $this->start_at->toIso8601String() : \Carbon\Carbon::parse($this->start_at)->toIso8601String();
    }

    public function getEndAttribute()
    {
        if (!$this->end_at) return null;
        return $this->end_at instanceof \DateTime ? $this->end_at->toIso8601String() : \Carbon\Carbon::parse($this->end_at)->toIso8601String();
    }

    public function getCurrentPriceAttribute()
    {
        $now = now();

        // If Batch 1 is valid (no deadline OR deadline is future) AND it has a price
        if ($this->batch_1_price && (!$this->batch_1_deadline || $now->lte($this->batch_1_deadline))) {
            return $this->batch_1_price;
        }

        // If Batch 1 expired, check Batch 2
        if ($this->batch_2_price && (!$this->batch_2_deadline || $now->lte($this->batch_2_deadline))) {
            return $this->batch_2_price;
        }

        // If Batch 2 expired, check Batch 3
        if ($this->batch_3_price) {
            return $this->batch_3_price;
        }

        // Fallback to legacy price or 0
        return $this->price ?? 0;
    }

    public function getCurrentBatchLabelAttribute()
    {
        $now = now();
        if ($this->batch_1_price && (!$this->batch_1_deadline || $now->lte($this->batch_1_deadline))) return '1º Lote';
        if ($this->batch_2_price && (!$this->batch_2_deadline || $now->lte($this->batch_2_deadline))) return '2º Lote';
        if ($this->batch_3_price) return '3º Lote';
        return 'Entrada';
    }

    public function registrations()
    {
        return $this->hasMany(EventRegistration::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function isOwnedBy($userId): bool
    {
        return $this->user_id === $userId;
    }

    public function paidOrConfirmedRegistrations()
    {
        return $this->registrations()->whereIn('status', EventRegistration::COUNTED_STATUSES);
    }

    public function getConfirmedSeatsAttribute()
    {
        return (int) $this->paidOrConfirmedRegistrations()->sum('quantity');
    }

    public function getRemainingSeatsAttribute()
    {
        if (!$this->capacity) {
            return null;
        }

        return max(0, (int) $this->capacity - (int) $this->confirmed_seats);
    }

    public function hasCapacityFor(int $quantity): bool
    {
        if (!$this->capacity) {
            return true;
        }

        return ((int) $this->confirmed_seats + $quantity) <= (int) $this->capacity;
    }
}
