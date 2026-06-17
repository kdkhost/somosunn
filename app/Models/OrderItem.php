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

    public function getGrossUnitPriceAttribute(): float
    {
        foreach (['original_unit_price', 'gross_unit_price', 'regular_unit_price'] as $key) {
            $value = data_get($this->data, $key);

            if (is_numeric($value) && (float) $value > 0) {
                return round((float) $value, 2);
            }
        }

        return round((float) $this->price, 2);
    }

    public function getLineGrossAmountAttribute(): float
    {
        return round($this->gross_unit_price * max(1, (int) $this->quantity), 2);
    }
}
