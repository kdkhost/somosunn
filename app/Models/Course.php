<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Illuminate\Support\Str;

class Course extends Model
{
    use HasFactory;

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($course) {
            if (empty($course->slug)) {
                $base = Str::slug((string) ($course->title ?? ''));
                if ($base === '') {
                    $base = 'curso';
                }
                $course->slug = $base . '-' . uniqid();
            }
        });

        static::saving(function ($course) {
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
    ];

    protected $casts = [
        'is_certificate_enabled' => 'boolean',
        'is_featured' => 'boolean',
        'certificate_settings' => 'array',
        'price' => 'decimal:2',
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
}
