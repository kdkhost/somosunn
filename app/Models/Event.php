<?php

namespace App\Models;

use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Support\UploadStorage;

class Event extends Model
{
    use HasFactory;

    public const SCANNER_LOCATION_RADIUS_METERS = 50;

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
        'visibility',
        'is_ticket_enabled',
    ];

    protected $casts = [
        'is_ticket_enabled' => 'boolean',
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

    protected $appends = ['start', 'end', 'image_url', 'thumbnail_url'];

    public function getImageUrlAttribute(): ?string
    {
        return UploadStorage::url($this->image);
    }

    public function getThumbnailUrlAttribute(): ?string
    {
        return $this->image_url;
    }

    public function getStartAttribute()
    {
        return $this->start_at instanceof \DateTime
            ? $this->start_at->toIso8601String()
            : Carbon::parse($this->start_at)->toIso8601String();
    }

    public function getEndAttribute()
    {
        if (!$this->end_at) {
            return null;
        }

        return $this->end_at instanceof \DateTime
            ? $this->end_at->toIso8601String()
            : Carbon::parse($this->end_at)->toIso8601String();
    }

    public function getCurrentPriceAttribute()
    {
        return $this->currentPriceFor(auth()->user());
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
        return $this->effectivePriceFor(auth()->user());
    }

    public function getCurrentBatchLabelAttribute()
    {
        return $this->currentBatchLabelFor(auth()->user());
    }

    public function currentPriceFor(?User $user = null): float
    {
        if ($this->hasFirstLotPriority($user) && $this->batch_1_price !== null) {
            return round((float) $this->batch_1_price, 2);
        }

        $now = now();

        if ($this->batch_1_price && (!$this->batch_1_deadline || $now->lte($this->batch_1_deadline))) {
            return round((float) $this->batch_1_price, 2);
        }

        if ($this->batch_2_price && (!$this->batch_2_deadline || $now->lte($this->batch_2_deadline))) {
            return round((float) $this->batch_2_price, 2);
        }

        if ($this->batch_3_price) {
            return round((float) $this->batch_3_price, 2);
        }

        return round((float) ($this->price ?? 0), 2);
    }

    public function effectivePriceFor(?User $user = null): float
    {
        $currentPrice = $this->currentPriceFor($user);

        if ($this->isFlashSaleActive()) {
            return round(min($currentPrice, (float) $this->flash_sale_price), 2);
        }

        return $currentPrice;
    }

    public function currentBatchLabelFor(?User $user = null): string
    {
        if ($this->hasFirstLotPriority($user) && $this->batch_1_price !== null) {
            return '1o Lote';
        }

        $now = now();
        if ($this->batch_1_price && (!$this->batch_1_deadline || $now->lte($this->batch_1_deadline))) {
            return '1o Lote';
        }
        if ($this->batch_2_price && (!$this->batch_2_deadline || $now->lte($this->batch_2_deadline))) {
            return '2o Lote';
        }
        if ($this->batch_3_price) {
            return '3o Lote';
        }

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

    public function scannerStartsAt(): ?Carbon
    {
        if (!$this->start_at) {
            return null;
        }

        return $this->start_at instanceof CarbonInterface
            ? Carbon::instance($this->start_at)
            : Carbon::parse($this->start_at);
    }

    public function scannerDeadlineAt(): ?Carbon
    {
        if ($this->end_at) {
            return $this->end_at instanceof CarbonInterface
                ? Carbon::instance($this->end_at)
                : Carbon::parse($this->end_at);
        }

        $start = $this->scannerStartsAt();
        if (!$start) {
            return null;
        }

        return $start->copy()->endOfDay();
    }

    public function hasScannerLocationConstraint(): bool
    {
        return $this->latitude !== null && $this->longitude !== null;
    }

    public function scannerLocationRadiusMeters(): int
    {
        return self::SCANNER_LOCATION_RADIUS_METERS;
    }

    public function scannerLocationMessage(): string
    {
        if (!$this->hasScannerLocationConstraint()) {
            return 'Este evento nao exige validacao por localizacao.';
        }

        return 'Validacao por localizacao ativa: o scanner deve estar em ate '
            . $this->scannerLocationRadiusMeters()
            . 'm do ponto configurado para o evento.';
    }

    public function distanceToScannerLocationMeters(?float $latitude, ?float $longitude): ?float
    {
        if (!$this->hasScannerLocationConstraint() || $latitude === null || $longitude === null) {
            return null;
        }

        $earthRadiusMeters = 6371000;
        $dLat = deg2rad((float) $this->latitude - $latitude);
        $dLon = deg2rad((float) $this->longitude - $longitude);
        $originLat = deg2rad($latitude);
        $eventLat = deg2rad((float) $this->latitude);

        $a = sin($dLat / 2) * sin($dLat / 2)
            + cos($originLat) * cos($eventLat) * sin($dLon / 2) * sin($dLon / 2);
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadiusMeters * $c;
    }

    public function isWithinScannerLocationRadius(?float $latitude, ?float $longitude): bool
    {
        $distance = $this->distanceToScannerLocationMeters($latitude, $longitude);

        return $distance !== null && $distance <= $this->scannerLocationRadiusMeters();
    }

    public function isScannerOpen(?CarbonInterface $reference = null): bool
    {
        $startsAt = $this->scannerStartsAt();
        $deadlineAt = $this->scannerDeadlineAt();

        if (!$startsAt || !$deadlineAt) {
            return false;
        }

        $comparison = $reference
            ? Carbon::instance($reference)
            : now();

        return $comparison->betweenIncluded($startsAt, $deadlineAt);
    }

    public function isScannerExpired(?CarbonInterface $reference = null): bool
    {
        $deadlineAt = $this->scannerDeadlineAt();

        if (!$deadlineAt) {
            return false;
        }

        $comparison = $reference
            ? Carbon::instance($reference)
            : now();

        return $comparison->gt($deadlineAt);
    }

    public function scannerStatusMessage(?CarbonInterface $reference = null): string
    {
        $startsAt = $this->scannerStartsAt();
        $deadlineAt = $this->scannerDeadlineAt();
        $comparison = $reference
            ? Carbon::instance($reference)
            : now();

        if (!$startsAt || !$deadlineAt) {
            return 'Este evento ainda nao possui uma janela valida de check-in configurada.';
        }

        if ($comparison->lt($startsAt)) {
            return 'A validacao do QR Code abre em ' . $startsAt->format('d/m/Y H:i') . '.';
        }

        if ($comparison->gt($deadlineAt)) {
            if ($this->end_at) {
                return 'QR Code expirado. A validacao encerrou em ' . $deadlineAt->format('d/m/Y H:i') . '.';
            }

            return 'QR Code expirado. A validacao encerrou as 23:59 do dia ' . $startsAt->format('d/m/Y') . '.';
        }

        if ($this->end_at) {
            return 'Validacao disponivel ate ' . $deadlineAt->format('d/m/Y H:i') . '.';
        }

        return 'Validacao disponivel ate 23:59 do dia ' . $startsAt->format('d/m/Y') . '.';
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

    public function media()
    {
        return $this->hasMany(EventMedia::class);
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

    private function hasFirstLotPriority(?User $user): bool
    {
        if (!$user) {
            return false;
        }

        if (method_exists($user, 'isAdmin') && $user->isAdmin()) {
            return true;
        }

        return method_exists($user, 'canAccessFeature') && $user->canAccessFeature('events.first_lot');
    }
}
