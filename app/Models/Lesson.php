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
        'video_storage_disk',
        'video_storage_path',
        'video_hls_manifest_path',
        'video_hls_key_path',
        'video_transcode_status',
        'video_transcode_error',
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

    public function possuiVideoInterno(): bool
    {
        return trim((string) ($this->video_storage_path ?? '')) !== '';
    }

    public function hlsPronto(): bool
    {
        return $this->video_transcode_status === 'ready'
            && trim((string) ($this->video_hls_manifest_path ?? '')) !== '';
    }

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
