<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Conversation extends Model
{
    use HasFactory;

    protected $fillable = ['type', 'title', 'created_by'];

    public function users()
    {
        return $this->belongsToMany(User::class)->withPivot('role', 'joined_at');
    }

    public function messages()
    {
        return $this->hasMany(Message::class);
    }

    public function scopePrivateBetween($query, int $firstUserId, int $secondUserId)
    {
        return $query
            ->whereHas('users', fn($query) => $query->where('users.id', $firstUserId))
            ->whereHas('users', fn($query) => $query->where('users.id', $secondUserId))
            ->withCount('users')
            ->having('users_count', 2);
    }
}
