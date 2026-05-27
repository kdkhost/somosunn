<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Support\UploadStorage;

class EventMedia extends Model
{
    use HasFactory;

    protected $fillable = [
        'event_id',
        'user_id',
        'file_path',
        'type', // 'image' ou 'video'
        'watermarked'
    ];

    protected $appends = ['url'];

    public function getUrlAttribute(): ?string
    {
        return UploadStorage::url($this->file_path);
    }

    public function hasAccessibleFile(): bool
    {
        return UploadStorage::exists($this->file_path);
    }

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
