<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Lesson extends Model
{
    use HasFactory;

    protected $fillable = [
        'course_id',
        'title',
        'order',
        'video_url',
        'is_free_preview',
        'free_preview_mode',
        'free_preview_seconds',
        'content',
        'duration', // Seconds
    ];

    protected $casts = [
        'is_free_preview' => 'boolean',
        'free_preview_seconds' => 'integer',
        'duration' => 'integer',
    ];

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function attachments()
    {
        return $this->hasMany(LessonAttachment::class);
    }

    public function progress()
    {
        return $this->hasOne(LessonProgress::class);
    }

    public function bookmarks()
    {
        return $this->hasMany(LessonBookmark::class);
    }
}
