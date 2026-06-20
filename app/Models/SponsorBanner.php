<?php

namespace App\Models;

use App\Models\Concerns\ChecksTableAvailability;
use App\Support\UploadStorage;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SponsorBanner extends Model
{
    use HasFactory;
    use ChecksTableAvailability;

    protected $fillable = [
        'sponsor_id',
        'title',
        'image',
        'url',
        'position',
        'starts_at',
        'ends_at',
        'active',
    ];

    protected $casts = [
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'active' => 'boolean',
    ];

    protected $appends = [
        'image_url',
    ];

    public function sponsor()
    {
        return $this->belongsTo(Sponsor::class);
    }

    public function getImageUrlAttribute(): ?string
    {
        return UploadStorage::url($this->image);
    }
}
