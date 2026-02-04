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
                $course->slug = Str::slug($course->title) . '-' . uniqid();
            }
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
        'certificate_settings',
        'published', // Legacy
    ];

    protected $casts = [
        'is_certificate_enabled' => 'boolean',
        'is_featured' => 'boolean',
        'certificate_settings' => 'array',
        'price' => 'decimal:2',
    ];

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