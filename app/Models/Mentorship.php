<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Mentorship extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'mentor_id',
        'description',
        'price',
        'slots',
        'schedule',
        'type',
        'video_platform',
        'video_link',
        'demo_link'
    ];

    const TYPE_ONLINE = 'online';
    const TYPE_PRESENCIAL = 'presencial';

    const PLATFORM_ZOOM = 'zoom';
    const PLATFORM_MEET = 'google_meet';
    const PLATFORM_TEAMS = 'teams';
    const PLATFORM_OTHER = 'other';

    protected $casts = [
        'schedule' => 'array',
    ];

    public function mentor()
    {
        return $this->belongsTo(User::class, 'mentor_id');
    }

    public function reviews()
    {
        return $this->morphMany(ItemReview::class, 'reviewable');
    }
}
