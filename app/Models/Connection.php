<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Connection extends Model
{
    use HasFactory;

    protected $fillable = [
        'requester_id',
        'requested_id',
        'status',
        'responded_at',
        'hide_profile'
    ];

    /**
     * Relationship: The user who initiated the connection.
     */
    public function requester()
    {
        return $this->belongsTo(User::class, 'requester_id');
    }

    /**
     * Relationship: The user who received the connection.
     */
    public function requested()
    {
        return $this->belongsTo(User::class, 'requested_id');
    }

    /**
     * Scope: Accepted connections.
     */
    public function scopeAccepted($query)
    {
        return $query->where('status', 'accepted');
    }

    /**
     * Scope: Pending connections.
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope: Blocked connections.
     */
    public function scopeBlocked($query)
    {
        return $query->where('status', 'blocked');
    }

    public static function getPendingBetween($userA, $userB)
    {
        return self::where('status', 'pending')
            ->where(function ($q) use ($userA, $userB) {
                $q->where(function ($sq) use ($userA, $userB) {
                    $sq->where('requester_id', $userA)->where('requested_id', $userB);
                })->orWhere(function ($sq) use ($userA, $userB) {
                    $sq->where('requester_id', $userB)->where('requested_id', $userA);
                });
            })->first();
    }

    public static function isAcceptedBetween($userA, $userB)
    {
        return self::where('status', 'accepted')
            ->where(function ($q) use ($userA, $userB) {
                $q->where(function ($sq) use ($userA, $userB) {
                    $sq->where('requester_id', $userA)->where('requested_id', $userB);
                })->orWhere(function ($sq) use ($userA, $userB) {
                    $sq->where('requester_id', $userB)->where('requested_id', $userA);
                });
            })->exists();
    }
}
