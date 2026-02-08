<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ItemReview extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'reviewable_type',
        'reviewable_id',
        'rating',
        'comment',
        'status',
        'moderated_by',
        'moderated_at',
        'moderation_notes',
    ];

    protected $casts = [
        'rating' => 'integer',
        'moderated_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function moderator()
    {
        return $this->belongsTo(User::class, 'moderated_by');
    }

    public function reviewable()
    {
        return $this->morphTo();
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }
}

