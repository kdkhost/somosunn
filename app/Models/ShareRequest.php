<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShareRequest extends Model
{
    protected $fillable = [
        'post_id',
        'from_user_id',
        'to_user_id',
        'message',
        'status',
        'expires_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
    ];

    // ─── Relacionamentos ───────────────────────────────────────────────────

    public function post()
    {
        return $this->belongsTo(Post::class);
    }

    public function fromUser()
    {
        return $this->belongsTo(User::class, 'from_user_id');
    }

    public function toUser()
    {
        return $this->belongsTo(User::class, 'to_user_id');
    }

    // ─── Scopes ────────────────────────────────────────────────────────────

    public function scopePending($query)
    {
        return $query->where('status', 'pending')
            ->where(function ($q) {
                $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
            });
    }

    public function scopeExpired($query)
    {
        return $query->where('status', 'pending')
            ->whereNotNull('expires_at')
            ->where('expires_at', '<=', now());
    }

    // ─── Helpers ───────────────────────────────────────────────────────────

    public function isPending(): bool
    {
        return $this->status === 'pending'
            && ($this->expires_at === null || $this->expires_at->isFuture());
    }
}
