<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'item_type', // course, etc
        'item_id',
        'title',
        'price',
        'quantity',
        'data',
    ];

    protected $casts = [
        'data' => 'array',
        'price' => 'decimal:2',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
