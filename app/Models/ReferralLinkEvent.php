<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReferralLinkEvent extends Model
{
    use HasFactory;

    protected $fillable = [
        'referral_link_visit_id',
        'referrer_user_id',
        'actor_user_id',
        'registered_user_id',
        'event_type',
        'channel',
        'page_path',
        'page_url',
        'order_id',
        'plan_id',
        'amount',
        'metadata',
        'occurred_at',
    ];

    protected $casts = [
        'referral_link_visit_id' => 'integer',
        'referrer_user_id' => 'integer',
        'actor_user_id' => 'integer',
        'registered_user_id' => 'integer',
        'order_id' => 'integer',
        'plan_id' => 'integer',
        'amount' => 'decimal:2',
        'metadata' => 'array',
        'occurred_at' => 'datetime',
    ];

    public function visit()
    {
        return $this->belongsTo(ReferralLinkVisit::class, 'referral_link_visit_id');
    }

    public function referrer()
    {
        return $this->belongsTo(User::class, 'referrer_user_id');
    }

    public function actor()
    {
        return $this->belongsTo(User::class, 'actor_user_id');
    }

    public function registeredUser()
    {
        return $this->belongsTo(User::class, 'registered_user_id');
    }
}
