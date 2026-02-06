<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Testimonial extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'author_name',
        'author_title',
        'rating',
        'content',
        'status',
        'is_featured',
        'moderated_by',
        'moderated_at',
        'moderation_notes',
    ];

    protected $casts = [
        'rating' => 'integer',
        'is_featured' => 'boolean',
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
}

