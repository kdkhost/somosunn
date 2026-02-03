<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'content', 'visibility', 'is_pinned'];

    protected $with = ['user', 'media', 'reactions', 'comments'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function media()
    {
        return $this->hasMany(PostMedia::class);
    }

    public function reactions()
    {
        return $this->hasMany(PostReaction::class);
    }

    public function comments()
    {
        return $this->hasMany(PostComment::class)->whereNull('parent_id')->orderBy('created_at', 'asc');
    }
}
