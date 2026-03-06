<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReferralLinkVisit extends Model
{
    use HasFactory;

    protected $fillable = [
        'referrer_user_id',
        'referral_code',
        'session_id',
        'visitor_token',
        'registered_user_id',
        'landing_page_path',
        'landing_page_url',
        'first_page_path',
        'first_page_url',
        'last_page_path',
        'last_page_url',
        'referrer_url',
        'utm_source',
        'utm_medium',
        'utm_campaign',
        'utm_content',
        'utm_term',
        'ip_hash',
        'user_agent',
        'country',
        'region',
        'city',
        'clicks_count',
        'pageviews_count',
        'checkout_started_count',
        'purchases_count',
        'first_visited_at',
        'last_visited_at',
        'registered_at',
        'first_checkout_started_at',
        'first_purchase_at',
        'last_purchase_at',
        'first_order_id',
        'first_paid_order_id',
        'first_plan_id',
        'total_revenue_amount',
    ];

    protected $casts = [
        'clicks_count' => 'integer',
        'pageviews_count' => 'integer',
        'checkout_started_count' => 'integer',
        'purchases_count' => 'integer',
        'first_visited_at' => 'datetime',
        'last_visited_at' => 'datetime',
        'registered_at' => 'datetime',
        'first_checkout_started_at' => 'datetime',
        'first_purchase_at' => 'datetime',
        'last_purchase_at' => 'datetime',
        'first_order_id' => 'integer',
        'first_paid_order_id' => 'integer',
        'first_plan_id' => 'integer',
        'total_revenue_amount' => 'decimal:2',
    ];

    public function referrer()
    {
        return $this->belongsTo(User::class, 'referrer_user_id');
    }

    public function registeredUser()
    {
        return $this->belongsTo(User::class, 'registered_user_id');
    }

    public function events()
    {
        return $this->hasMany(ReferralLinkEvent::class);
    }
}
