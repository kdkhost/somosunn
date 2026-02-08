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
        'content',
        'duration', // Seconds
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
