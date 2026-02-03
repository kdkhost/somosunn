<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Course extends Model
{
    use HasFactory;

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