<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class Course extends Model
{
    use HasFactory;

    protected static function slugColumnExists(): bool
    {
        static $exists = null;

        if ($exists !== null) {
            return $exists;
        }

        try {
            $exists = Schema::hasColumn((new static())->getTable(), 'slug');
        } catch (\Throwable $e) {
            // If schema inspection fails for any reason, keep legacy behavior.
            $exists = true;
        }

        return $exists;
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($course) {
            if (!static::slugColumnExists()) {
                return;
            }

            if (empty($course->slug)) {
                $base = Str::slug((string) ($course->title ?? ''));
                if ($base === '') {
                    $base = 'curso';
                }
                $course->slug = $base . '-' . uniqid();
            }
        });

        static::saving(function ($course) {
            if (!static::slugColumnExists()) {
                return;
            }

            if (!empty($course->slug)) {
                return;
            }

            $base = Str::slug((string) ($course->title ?? ''));
            if ($base === '') {
                $base = 'curso';
            }
            $course->slug = $base . '-' . uniqid();
        });
    }

    protected $fillable = [
        'user_id',
        'title',
        'slug',
        'price',
        'duration',
        'is_certificate_enabled',
        'thumbnail',
        'short_description',
        'full_description',
        'author_name',
        'status',
        'is_featured',
        'certificate_bg',
        'instructor_signature',
        'certificate_settings',
        'published', // Legacy
        'video_block_download',
        'video_floating_enabled',
        'video_floating_width',
        'video_floating_height',
    ];

    protected $casts = [
        'is_certificate_enabled' => 'boolean',
        'is_featured' => 'boolean',
        'certificate_settings' => 'array',
        'price' => 'decimal:2',
        'video_block_download' => 'boolean',
        'video_floating_enabled' => 'boolean',
        'video_floating_width' => 'integer',
        'video_floating_height' => 'integer',
    ];

    /**
     * Calculate total course hours from lesson durations.
     * Lesson duration is stored in seconds.
     */
    public function getTotalHoursAttribute()
    {
        $totalSeconds = $this->lessons()->sum('duration');
        // Convert seconds to hours, rounded to 1 decimal place
        return $totalSeconds > 0 ? round($totalSeconds / 3600, 1) : 0;
    }

    /**
     * Auto-generate presentation text based on course title.
     */
    public function getDefaultPresentationTextAttribute()
    {
        return "This certificate is proudly presented to recognize the successful completion of {$this->title}";
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function isOwnedBy($userId): bool
    {
        return $this->user_id === $userId;
    }

    public function lessons()
    {
        return $this->hasMany(Lesson::class)->orderBy('order');
    }

    public function enrollments()
    {
        return $this->morphMany(Enrollment::class, 'enrollable');
    }

    public function isPublished()
    {
        return $this->status === 'published';
    }

    public function certificates()
    {
        return $this->hasMany(Certificate::class);
    }

    public function reviews()
    {
        return $this->morphMany(ItemReview::class, 'reviewable');
    }

    /**
     * Retorna a média de avaliações aprovadas.
     */
    public function getAverageRatingAttribute(): float
    {
        return round((float) $this->reviews()->approved()->avg('rating'), 1);
    }

    /**
     * Retorna a quantidade de avaliações aprovadas.
     */
    public function getApprovedReviewsCountAttribute(): int
    {
        return (int) $this->reviews()->approved()->count();
    }
}
