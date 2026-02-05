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
}
