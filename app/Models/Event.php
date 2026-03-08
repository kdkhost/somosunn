<?php

namespace App\Models;

use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'speaker',
        'description',
        'image',
        'start_at',
        'end_at',
        'location',
        'address',
        'latitude',
        'longitude',
        'price',
        'flash_sale_price',
        'flash_sale_ends_at',
        'capacity',
        'published',
        'color',
        'all_day',
        'batch_1_price',
        'batch_1_deadline',
        'batch_2_price',
        'batch_2_deadline',
        'batch_3_price',
        'batch_3_deadline',
        'is_certificate_enabled',
        'certificate_bg',
        'instructor_signature',
        'certificate_settings',
        'is_somos_unicas',
        'visibility'
    ];

    protected $casts = [
        'start_at' => 'datetime',
        'end_at' => 'datetime',
        'batch_1_deadline' => 'datetime',
        'batch_2_deadline' => 'datetime',
        'batch_3_deadline' => 'datetime',
        'all_day' => 'boolean',
        'published' => 'boolean',
        'is_certificate_enabled' => 'boolean',
        'certificate_settings' => 'array',
        'price' => 'decimal:2',
        'flash_sale_price' => 'decimal:2',
        'flash_sale_ends_at' => 'datetime',
    ];

    protected $appends = ['start', 'end'];

    public function getStartAttribute()
    {
        return $this->start_at instanceof \DateTime ? $this->start_at->toIso8601String() : \Carbon\Carbon::parse($this->start_at)->toIso8601String();
    }

    public function getEndAttribute()
    {
        if (!$this->end_at)
            return null;
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

    public function isFlashSaleActive(): bool
    {
        $price = $this->flash_sale_price;
        $endsAt = $this->flash_sale_ends_at;

        if ($price === null || $endsAt === null) {
            return false;
        }

        try {
            return (float) $price >= 0 && $endsAt->isFuture();
        } catch (\Throwable $e) {
            return false;
        }
    }

    public function getEffectivePriceAttribute(): float
    {
        if ($this->isFlashSaleActive()) {
            return (float) $this->flash_sale_price;
        }

        return (float) ($this->current_price ?? 0);
    }

    public function getCurrentBatchLabelAttribute()
    {
        $now = now();
        if ($this->batch_1_price && (!$this->batch_1_deadline || $now->lte($this->batch_1_deadline)))
            return '1º Lote';
        if ($this->batch_2_price && (!$this->batch_2_deadline || $now->lte($this->batch_2_deadline)))
            return '2º Lote';
        if ($this->batch_3_price)
            return '3º Lote';
        return 'Entrada';
    }

    public function publicDeadlineAt(): ?Carbon
    {
        $reference = $this->end_at ?: $this->start_at;

        if (!$reference) {
            return null;
        }

        $deadline = $reference instanceof CarbonInterface
            ? Carbon::instance($reference)
            : Carbon::parse($reference);

        if ($this->all_day && !$this->end_at) {
            return $deadline->endOfDay();
        }

        return $deadline;
    }

    public function isClosedForPublic(?CarbonInterface $reference = null): bool
    {
        $deadline = $this->publicDeadlineAt();

        if (!$deadline) {
            return false;
        }

        $comparison = $reference
            ? Carbon::instance($reference)
            : now();

        return $deadline->lt($comparison);
    }

    public function hasPublicAction(): bool
    {
        return !$this->isClosedForPublic();
    }

    public function scopePublicUpcoming(Builder $query): Builder
    {
        $now = now();
        $today = $now->copy()->startOfDay();

        return $query->where(function (Builder $scope) use ($now, $today) {
            $scope->where(function (Builder $builder) use ($now) {
                $builder->whereNotNull('end_at')
                    ->where('end_at', '>=', $now);
            })->orWhere(function (Builder $builder) use ($now, $today) {
                $builder->whereNull('end_at')
                    ->where(function (Builder $dateScope) use ($now, $today) {
                        $dateScope->where(function (Builder $allDayScope) use ($today) {
                            $allDayScope->where('all_day', true)
                                ->where('start_at', '>=', $today);
                        })->orWhere(function (Builder $timedScope) use ($now) {
                            $timedScope->where(function (Builder $allDayFlagScope) {
                                $allDayFlagScope->whereNull('all_day')
                                    ->orWhere('all_day', false);
                            })->where('start_at', '>=', $now);
                        });
                    });
            });
        });
    }

    public function scopePublicPast(Builder $query): Builder
    {
        $now = now();
        $today = $now->copy()->startOfDay();

        return $query->where(function (Builder $scope) use ($now, $today) {
            $scope->where(function (Builder $builder) use ($now) {
                $builder->whereNotNull('end_at')
                    ->where('end_at', '<', $now);
            })->orWhere(function (Builder $builder) use ($now, $today) {
                $builder->whereNull('end_at')
                    ->where(function (Builder $dateScope) use ($now, $today) {
                        $dateScope->where(function (Builder $allDayScope) use ($today) {
                            $allDayScope->where('all_day', true)
                                ->where('start_at', '<', $today);
                        })->orWhere(function (Builder $timedScope) use ($now) {
                            $timedScope->where(function (Builder $allDayFlagScope) {
                                $allDayFlagScope->whereNull('all_day')
                                    ->orWhere('all_day', false);
                            })->where('start_at', '<', $now);
                        });
                    });
            });
        });
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

    public function certificates()
    {
        return $this->hasMany(Certificate::class);
    }
}
