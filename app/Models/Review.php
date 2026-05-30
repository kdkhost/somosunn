<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'order_id',
        'order_item_id',
        'item_type',
        'item_id',
        'rating',
        'comment',
        'is_verified',
        'is_approved',
        'feedback_requested_at',
        'submitted_at',
    ];

    protected $casts = [
        'rating' => 'integer',
        'is_verified' => 'boolean',
        'is_approved' => 'boolean',
        'feedback_requested_at' => 'datetime',
        'submitted_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function orderItem()
    {
        return $this->belongsTo(OrderItem::class);
    }

    public function getItem()
    {
        if (!$this->item_type || !$this->item_id) {
            return null;
        }

        switch ($this->item_type) {
            case 'event':
                return Event::find($this->item_id);
            case 'course':
                return Course::find($this->item_id);
            case 'mentorship':
                return Mentorship::find($this->item_id);
            case 'marketplace':
                return SellerProduct::find($this->item_id);
            default:
                return null;
        }
    }
}
