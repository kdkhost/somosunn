<?php

namespace App\Models;

use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Mentorship extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'mentor_id',
        'description',
        'image',
        'price',
        'flash_sale_price',
        'flash_sale_ends_at',
        'slots',
        'schedule',
        'type',
        'video_platform',
        'video_link',
        'is_certificate_enabled',
        'certificate_bg',
        'instructor_signature',
        'certificate_settings',
        'demo_link',
        'is_somos_unicas',
    ];

    const TYPE_ONLINE = 'online';
    const TYPE_PRESENCIAL = 'presencial';

    const PLATFORM_ZOOM = 'zoom';
    const PLATFORM_MEET = 'google_meet';
    const PLATFORM_TEAMS = 'teams';
    const PLATFORM_OTHER = 'other';

    protected $casts = [
        'schedule' => 'array',
        'is_certificate_enabled' => 'boolean',
        'certificate_settings' => 'array',
        'price' => 'decimal:2',
        'flash_sale_price' => 'decimal:2',
        'flash_sale_ends_at' => 'datetime',
    ];

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

        return (float) ($this->price ?? 0);
    }

    public function latestScheduleAt(): ?Carbon
    {
        $moments = $this->extractScheduleMoments($this->schedule);

        if (empty($moments)) {
            return null;
        }

        usort($moments, static fn(Carbon $left, Carbon $right) => $left->getTimestamp() <=> $right->getTimestamp());

        return end($moments) ?: null;
    }

    public function isClosedForPublic(?CarbonInterface $reference = null): bool
    {
        $latestScheduleAt = $this->latestScheduleAt();

        if (!$latestScheduleAt) {
            return false;
        }

        $comparison = $reference
            ? Carbon::instance($reference)
            : now();

        return $latestScheduleAt->lt($comparison);
    }

    public function hasPublicAction(): bool
    {
        return !$this->isClosedForPublic();
    }

    public function mentor()
    {
        return $this->belongsTo(User::class, 'mentor_id');
    }

    public function isOwnedBy($userId): bool
    {
        return $this->mentor_id === $userId;
    }

    public function reviews()
    {
        return $this->morphMany(ItemReview::class, 'reviewable');
    }

    public function certificates()
    {
        return $this->hasMany(Certificate::class);
    }

    private function extractScheduleMoments(mixed $payload, ?string $timezone = null): array
    {
        if (!is_array($payload)) {
            return [];
        }

        $timezone = $this->resolveScheduleTimezone($payload, $timezone);
        $moments = [];

        $dateTimeKeys = ['end_at', 'ends_at', 'end_datetime', 'end', 'start_at', 'starts_at', 'start_datetime', 'datetime', 'start'];
        foreach ($dateTimeKeys as $key) {
            if (!array_key_exists($key, $payload)) {
                continue;
            }

            $parsed = $this->parseScheduleMoment($payload[$key], $timezone);
            if ($parsed) {
                $moments[] = $parsed;
            }
        }

        if (array_key_exists('date', $payload) && is_scalar($payload['date'])) {
            $time = $payload['end_time'] ?? $payload['time'] ?? $payload['hour'] ?? '23:59:59';
            $parsed = $this->parseScheduleMoment(trim((string) $payload['date']) . ' ' . trim((string) $time), $timezone);
            if ($parsed) {
                $moments[] = $parsed;
            }
        }

        foreach ($payload as $value) {
            if (is_array($value)) {
                $moments = array_merge($moments, $this->extractScheduleMoments($value, $timezone));
            }
        }

        return $moments;
    }

    private function parseScheduleMoment(mixed $value, ?string $timezone = null): ?Carbon
    {
        if ($value instanceof Carbon) {
            return $value->copy();
        }

        if ($value instanceof CarbonInterface) {
            return Carbon::instance($value);
        }

        if (!is_scalar($value)) {
            return null;
        }

        $raw = trim((string) $value);
        if ($raw === '') {
            return null;
        }

        try {
            return Carbon::parse($raw, $timezone ?: config('app.timezone'));
        } catch (\Throwable $e) {
            return null;
        }
    }

    private function resolveScheduleTimezone(array $payload, ?string $fallback = null): ?string
    {
        foreach (['timezone', 'tz'] as $key) {
            $candidate = trim((string) ($payload[$key] ?? ''));
            if ($candidate !== '') {
                return $candidate;
            }
        }

        return $fallback;
    }
}
