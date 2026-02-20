<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RedeemableItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'image',
        'points_cost',
        'stock',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'points_cost' => 'integer',
        'stock' => 'integer',
    ];

    public function redemptions()
    {
        return $this->hasMany(Redemption::class);
    }
}
