<?php

namespace App\Models;

use App\Models\Concerns\ChecksTableAvailability;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SponsorPlan extends Model
{
    use HasFactory;
    use ChecksTableAvailability;

    public const DURATION_PRESETS = [7, 14, 21, 30, 60, 90, 120, 180, 365];

    protected $fillable = [
        'name',
        'price',
        'duration_days',
        'description',
        'max_banners',
        'max_events',
        'max_leads',
        'priority',
        'active',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'duration_days' => 'integer',
        'max_banners' => 'integer',
        'max_events' => 'integer',
        'max_leads' => 'integer',
        'priority' => 'integer',
        'active' => 'boolean',
    ];

    public function sponsors()
    {
        return $this->hasMany(Sponsor::class);
    }

    public function getDurationLabelAttribute(): string
    {
        $days = max(1, (int) ($this->duration_days ?: 30));

        return $days === 1 ? '1 dia' : $days . ' dias';
    }
}
