<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Support\UploadStorage;

class MentorshipMedia extends Model
{
    protected $table = 'mentorship_media';

    protected $fillable = [
        'mentorship_id',
        'user_id',
        'file_path',
        'type',
        'watermarked',
    ];

    protected $casts = [
        'watermarked' => 'boolean',
    ];

    public function mentorship()
    {
        return $this->belongsTo(Mentorship::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getUrlAttribute(): string
    {
        return UploadStorage::url($this->file_path);
    }
}
