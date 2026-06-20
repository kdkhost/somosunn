<?php

namespace App\Models;

use App\Models\Concerns\ChecksTableAvailability;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SponsorPlan extends Model
{
    use HasFactory;
    use ChecksTableAvailability;

    protected $fillable = [
        'name',
        'price',
        'max_banners',
        'max_events',
        'max_leads',
        'priority',
        'active',
    ];

    protected $casts = [
        'price' => 'decimal:2',
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
}
