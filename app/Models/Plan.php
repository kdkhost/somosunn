<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Plan extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'price',
        'period',
        'billing_cycle',
        'prorata',
        'description',
        'image',
        'is_featured',
        'highlight_legacy',
        'highlight',
        'coupons_enabled',
        'benefits',
        'permissions',
        'is_active'
    ];

    protected $casts = [
        'is_featured' => 'boolean',
        'highlight_legacy' => 'boolean',
        'highlight' => 'boolean',
        'coupons_enabled' => 'boolean',
        'is_active' => 'boolean',
        'prorata' => 'boolean',
        'benefits' => 'array',
        'permissions' => 'array',
        'price' => 'decimal:2'
    ];

    public function hasFeature($feature)
    {
        $features = $this->permissions ?? [];
        if (!is_array($features)) {
            return false;
        }
        return in_array($feature, $features) || in_array('*', $features);
    }
}
