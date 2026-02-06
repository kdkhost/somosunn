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
        'comparison',
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
        'comparison' => 'array',
        'price' => 'decimal:2'
    ];

    public function hasFeature($feature)
    {
        $feature = (string) $feature;

        $features = $this->permissions ?? [];
        if (!is_array($features)) {
            $features = [];
        }

        if (in_array('*', $features, true)) {
            return true;
        }

        if (in_array($feature, $features, true)) {
            return true;
        }

        // Compatibilidade: versões antigas gravavam permissões "admin-like" (ex.: courses.view)
        $legacyPrefixes = [
            'courses' => 'courses.',
            'events' => 'events.',
            'mentorships' => 'mentorships.',
        ];

        if (isset($legacyPrefixes[$feature])) {
            $prefix = $legacyPrefixes[$feature];
            foreach ($features as $value) {
                if (!is_string($value)) {
                    continue;
                }
                if (str_starts_with($value, $prefix)) {
                    return true;
                }
            }
        }

        return false;
    }
}
