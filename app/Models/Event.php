<?php

namespace App\Models;

use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Rules\WhatsAppGroupLinkRule;
use App\Support\UploadStorage;
use App\Models\EventExhibitorRegistration;
use App\Services\EventExhibitorService;

class Event extends Model
{
    use HasFactory;

    public const SCANNER_DEFAULT_RADIUS_METERS = 50;
    public const SCANNER_EXACT_TOLERANCE_METERS = 5;
    public const SCANNER_RESTRICTION_DISABLED = 'disabled';
    public const SCANNER_RESTRICTION_EXACT = 'exact';
    public const SCANNER_RESTRICTION_RADIUS = 'radius';

    protected $fillable = [
        'type',
        'slug',
        'user_id',
        'title',
        'speaker',
        'description',
        'image',
        'gallery_cover_image',
        'gallery_cover_media_id',
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
        'scanner_restriction_mode',
        'scanner_radius_meters',
        'scanner_early_minutes',
        'scanner_late_minutes',
        'exhibitor_sales_enabled',
        'exhibitor_total_slots',
        'exhibitor_description',
        'exhibitor_internal_notes',
        'exhibitor_area_image',
        'exhibitor_includes_ticket',
        'exhibitor_batch_1_price',
        'exhibitor_batch_1_deadline',
        'exhibitor_batch_1_slots',
        'exhibitor_batch_2_price',
        'exhibitor_batch_2_deadline',
        'exhibitor_batch_2_slots',
        'exhibitor_batch_3_price',
        'exhibitor_batch_3_deadline',
        'exhibitor_batch_3_slots',
        'exhibitor_show_publicly',
        'whatsapp_group_link',
    ];

    protected $casts = [
        'is_ticket_enabled' => 'boolean',
        'exhibitor_sales_enabled' => 'boolean',
        'exhibitor_includes_ticket' => 'boolean',
        'exhibitor_show_publicly' => 'boolean',
        'exhibitor_total_slots' => 'integer',
        'exhibitor_batch_1_slots' => 'integer',
        'exhibitor_batch_2_slots' => 'integer',
        'exhibitor_batch_3_slots' => 'integer',
        'start_at' => 'datetime',
        'end_at' => 'datetime',
        'batch_1_deadline' => 'datetime',
        'batch_2_deadline' => 'datetime',
        'batch_3_deadline' => 'datetime',
        'exhibitor_batch_1_deadline' => 'datetime',
        'exhibitor_batch_2_deadline' => 'datetime',
        'exhibitor_batch_3_deadline' => 'datetime',
        'all_day' => 'boolean',
        'published' => 'boolean',
        'is_certificate_enabled' => 'boolean',
        'certificate_settings' => 'array',
        'price' => 'decimal:2',
        'flash_sale_price' => 'decimal:2',
        'exhibitor_batch_1_price' => 'decimal:2',
        'exhibitor_batch_2_price' => 'decimal:2',
        'exhibitor_batch_3_price' => 'decimal:2',
        'flash_sale_ends_at' => 'datetime',
        'scanner_radius_meters' => 'integer',
    ];

    protected $appends = ['start', 'end', 'image_url', 'thumbnail_url', 'gallery_cover_url', 'exhibitor_area_image_url', 'is_ready_to_publish'];

    public function getImageUrlAttribute(): ?string
    {
        return UploadStorage::url($this->image);
    }

    /**
     * Indica se o evento tem todos os campos obrigatórios preenchidos para publicação.
     * Usado nos atalhos rápidos do calendário: evento criado sem imagem fica como rascunho
     * até que a capa seja adicionada.
     */
    public function getIsReadyToPublishAttribute(): bool
    {
        return !empty($this->title)
            && !empty($this->image)
            && !empty($this->start_at);
    }

    public function getThumbnailUrlAttribute(): ?string
    {
        return $this->image_url;
    }

    public function getGalleryCoverUrlAttribute(): ?string
    {
        if (!blank($this->gallery_cover_image) && UploadStorage::exists($this->gallery_cover_image)) {
            return UploadStorage::url($this->gallery_cover_image, $this->image_url);
        }

        $coverMedia = $this->relationLoaded('galleryCoverMedia')
            ? $this->galleryCoverMedia
            : ($this->gallery_cover_media_id ? $this->galleryCoverMedia()->first() : null);

        if ($coverMedia?->file_path && UploadStorage::exists($coverMedia->file_path)) {
            return UploadStorage::url($coverMedia->file_path, $this->image_url);
        }

        $fallbackMedia = $this->relationLoaded('media')
            ? $this->media
                ->where('type', 'image')
                ->first(fn (EventMedia $media) => $media->hasAccessibleFile())
            : $this->media()
                ->where('type', 'image')
                ->oldest('created_at')
                ->get()
                ->first(fn (EventMedia $media) => $media->hasAccessibleFile());

        if ($fallbackMedia?->file_path && UploadStorage::exists($fallbackMedia->file_path)) {
            return UploadStorage::url($fallbackMedia->file_path, $this->image_url);
        }

        return $this->image_url;
    }

    public function getExhibitorAreaImageUrlAttribute(): ?string
    {
        return UploadStorage::url($this->exhibitor_area_image);
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
        $now = now();
        for ($number = 1; $number <= 3; $number++) {
            $price = round((float) ($this->getAttribute("batch_{$number}_price") ?? 0), 2);
            if ($price <= 0) {
                continue;
            }

            $deadline = $this->getAttribute("batch_{$number}_deadline");
            if ($deadline && $now->gt($deadline)) {
                continue;
            }

            return $price;
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

        $now = now();
        if ($this->batch_1_price && (!$this->batch_1_deadline || $now->lte($this->batch_1_deadline))) {
            return '1° Lote';
        }
        if ($this->batch_2_price && (!$this->batch_2_deadline || $now->lte($this->batch_2_deadline))) {
            return '2° Lote';
        }
        if ($this->batch_3_price) {
            return '3° Lote';
        }

        return 'Entrada';
    }

    public function hasPaidValueConfigured(): bool
    {
        foreach (['price', 'batch_1_price', 'batch_2_price', 'batch_3_price', 'flash_sale_price'] as $field) {
            if (round((float) ($this->{$field} ?? 0), 2) > 0) {
                return true;
            }
        }

        return false;
    }

    public function isActuallyFreeForPublic(): bool
    {
        return !$this->hasPaidValueConfigured();
    }

    public function scopeActuallyFreeForPublic(Builder $query): Builder
    {
        foreach (['price', 'batch_1_price', 'batch_2_price', 'batch_3_price', 'flash_sale_price'] as $field) {
            $query->where(function (Builder $query) use ($field) {
                $query->whereNull($field)->orWhere($field, '<=', 0);
            });
        }

        return $query;
    }

    public function scopePaidForPublic(Builder $query): Builder
    {
        return $query->where(function (Builder $query) {
            foreach (['price', 'batch_1_price', 'batch_2_price', 'batch_3_price', 'flash_sale_price'] as $field) {
                $query->orWhere($field, '>', 0);
            }
        });
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

        $start = $this->start_at instanceof CarbonInterface
            ? Carbon::instance($this->start_at)
            : Carbon::parse($this->start_at);

        // Aplica tolerância antecipada configurada pelo admin
        $earlyMinutes = (int) ($this->scanner_early_minutes ?? 0);
        if ($earlyMinutes > 0) {
            $start = $start->copy()->subMinutes($earlyMinutes);
        }

        return $start;
    }

    public function scannerDeadlineAt(): ?Carbon
    {
        $deadline = null;

        if ($this->end_at) {
            $deadline = $this->end_at instanceof CarbonInterface
                ? Carbon::instance($this->end_at)
                : Carbon::parse($this->end_at);
        } else {
            $start = $this->start_at instanceof CarbonInterface
                ? Carbon::instance($this->start_at)
                : ($this->start_at ? Carbon::parse($this->start_at) : null);

            if (!$start) {
                return null;
            }

            $deadline = $start->copy()->endOfDay();
        }

        // Aplica tolerância adicional após o fim do evento
        $lateMinutes = (int) ($this->scanner_late_minutes ?? 0);
        if ($lateMinutes > 0) {
            $deadline = $deadline->copy()->addMinutes($lateMinutes);
        }

        return $deadline;
    }

    public function hasScannerLocationConstraint(): bool
    {
        return $this->scannerLocationRestrictionEnabled() && $this->hasScannerCoordinates();
    }

    public function scannerLocationRadiusMeters(): int
    {
        if ($this->scannerRestrictionMode() === self::SCANNER_RESTRICTION_EXACT) {
            return self::SCANNER_EXACT_TOLERANCE_METERS;
        }

        $radius = (int) ($this->scanner_radius_meters ?: self::SCANNER_DEFAULT_RADIUS_METERS);

        return $radius > 0 ? $radius : self::SCANNER_DEFAULT_RADIUS_METERS;
    }

    public function scannerLocationMessage(): string
    {
        if (!$this->scannerLocationRestrictionEnabled()) {
            return 'Leitura liberada sem restricao de localizacao.';
        }

        if ($this->scannerLocationSetupIncomplete()) {
            return 'Cerca digital ativa, mas faltam as coordenadas exatas do evento para validar o ingresso.';
        }

        if ($this->scannerRestrictionMode() === self::SCANNER_RESTRICTION_EXACT) {
            return 'Leitura restrita a localizacao exata do evento, com tolerancia tecnica de ate '
                . self::SCANNER_EXACT_TOLERANCE_METERS
                . 'm.';
        }

        return 'Leitura restrita a ate '
            . $this->scannerFormattedRadius()
            . ' do ponto configurado para o evento.';
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

    public function scannerRestrictionMode(): string
    {
        $mode = strtolower(trim((string) $this->scanner_restriction_mode));

        if (in_array($mode, [
            self::SCANNER_RESTRICTION_DISABLED,
            self::SCANNER_RESTRICTION_EXACT,
            self::SCANNER_RESTRICTION_RADIUS,
        ], true)) {
            return $mode;
        }

        return $this->hasScannerCoordinates()
            ? self::SCANNER_RESTRICTION_RADIUS
            : self::SCANNER_RESTRICTION_DISABLED;
    }

    public function scannerLocationRestrictionEnabled(): bool
    {
        return $this->scannerRestrictionMode() !== self::SCANNER_RESTRICTION_DISABLED;
    }

    public function scannerLocationSetupIncomplete(): bool
    {
        return $this->scannerLocationRestrictionEnabled() && !$this->hasScannerCoordinates();
    }

    public function hasScannerCoordinates(): bool
    {
        return $this->latitude !== null && $this->longitude !== null;
    }

    public function scannerFormattedRadius(): string
    {
        $meters = $this->scannerLocationRadiusMeters();

        if ($meters >= 1000) {
            $kilometers = $meters / 1000;
            $formatted = number_format($kilometers, $kilometers === (float) (int) $kilometers ? 0 : 1, ',', '.');

            return $formatted . ' km';
        }

        return $meters . ' m';
    }

    public function scannerFormRadiusValue(): string
    {
        $meters = $this->scannerRestrictionMode() === self::SCANNER_RESTRICTION_RADIUS
            ? (int) ($this->scanner_radius_meters ?: self::SCANNER_DEFAULT_RADIUS_METERS)
            : self::SCANNER_DEFAULT_RADIUS_METERS;

        if ($meters >= 1000 && $meters % 1000 === 0) {
            return (string) ($meters / 1000);
        }

        return $meters >= 1000
            ? rtrim(rtrim(number_format($meters / 1000, 2, '.', ''), '0'), '.')
            : (string) $meters;
    }

    public function scannerFormRadiusUnit(): string
    {
        $meters = (int) ($this->scanner_radius_meters ?: self::SCANNER_DEFAULT_RADIUS_METERS);

        return $meters >= 1000 && $meters % 1000 === 0 ? 'km' : 'm';
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

        $earlyMinutes = (int) ($this->scanner_early_minutes ?? 0);
        $extraInfo = $earlyMinutes > 0
            ? ' (abre ' . $this->formatMinutesToHuman($earlyMinutes) . ' antes)'
            : '';

        if ($comparison->lt($startsAt)) {
            return 'A validacao do QR Code abre em ' . $startsAt->format('d/m/Y H:i') . $extraInfo . '.';
        }

        if ($comparison->gt($deadlineAt)) {
            if ($this->end_at) {
                return 'QR Code expirado. A validacao encerrou em ' . $deadlineAt->format('d/m/Y H:i') . '.';
            }

            return 'QR Code expirado. A validacao encerrou em ' . $deadlineAt->format('d/m/Y H:i') . '.';
        }

        if ($this->end_at) {
            return 'Validacao disponivel ate ' . $deadlineAt->format('d/m/Y H:i') . '.';
        }

        return 'Validacao disponivel ate ' . $deadlineAt->format('d/m/Y H:i') . '.';
    }

    private function formatMinutesToHuman(int $minutes): string
    {
        if ($minutes < 60) {
            return $minutes . ' min';
        }

        $hours = intdiv($minutes, 60);
        $mins = $minutes % 60;

        if ($mins === 0) {
            return $hours . 'h';
        }

        return $hours . 'h' . $mins . 'min';
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

    public function coupons()
    {
        return $this->hasMany(EventCoupon::class);
    }

    public function hasWhatsappGroup(): bool
    {
        return WhatsAppGroupLinkRule::passes($this->whatsapp_group_link);
    }

    public function exhibitorRegistrations()
    {
        return $this->hasMany(EventExhibitorRegistration::class);
    }

    public function media()
    {
        return $this->hasMany(EventMedia::class);
    }

    public function galleryCoverMedia()
    {
        return $this->belongsTo(EventMedia::class, 'gallery_cover_media_id');
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

    public function paidOrConfirmedExhibitorRegistrations()
    {
        return $this->exhibitorRegistrations()
            ->whereIn('status', [
                EventExhibitorRegistration::STATUS_PAID,
                EventExhibitorRegistration::STATUS_CONFIRMED,
            ]);
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

    public function getConfirmedExhibitorSlotsAttribute(): int
    {
        return app(EventExhibitorService::class)->countedSlots($this);
    }

    public function getRemainingExhibitorSlotsAttribute(): int
    {
        return app(EventExhibitorService::class)->remainingSlots($this);
    }

    public function hasCapacityFor(int $quantity): bool
    {
        if (!$this->capacity) {
            return true;
        }

        return ((int) $this->confirmed_seats + $quantity) <= (int) $this->capacity;
    }

    public function hasExhibitorSlotsFor(int $quantity): bool
    {
        return app(EventExhibitorService::class)->hasSlotsFor($this, $quantity);
    }

    public function isExhibitorSalesActive(): bool
    {
        return app(EventExhibitorService::class)->isSalesActive($this);
    }

    public function currentExhibitorPriceFor(?User $user = null): ?float
    {
        $batch = app(EventExhibitorService::class)->currentBatch($this);

        return $batch ? (float) $batch['price'] : null;
    }

    public function currentExhibitorBatchLabelFor(?User $user = null): ?string
    {
        $batch = app(EventExhibitorService::class)->currentBatch($this);

        return $batch ? (string) $batch['label'] : null;
    }

    public function exhibitorSalesStatus(): array
    {
        return app(EventExhibitorService::class)->status($this);
    }

    public function canSellExhibitorArea(?int $quantity = 1): bool
    {
        return $this->isExhibitorSalesActive() && $this->hasExhibitorSlotsFor(max(1, (int) $quantity));
    }

    public function certificates()
    {
        return $this->hasMany(Certificate::class);
    }



    public function resolveRouteBinding($value, $field = null)
    {
        if ($field) {
            return $this->where($field, $value)->firstOrFail();
        }

        return $this->where('slug', $value)
            ->orWhere('id', $value)
            ->firstOrFail();
    }

    public function isAlbum(): bool
    {
        return $this->type === 'album';
    }

    public function isEvent(): bool
    {
        return $this->type === 'event' || is_null($this->type);
    }
}
