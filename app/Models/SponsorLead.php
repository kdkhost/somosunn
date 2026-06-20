<?php

namespace App\Models;

use App\Models\Concerns\ChecksTableAvailability;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SponsorLead extends Model
{
    use HasFactory;
    use ChecksTableAvailability;

    protected $fillable = [
        'sponsor_id',
        'user_id',
        'event_id',
        'source',
        'consent',
    ];

    protected $casts = [
        'consent' => 'boolean',
    ];

    public function sponsor()
    {
        return $this->belongsTo(Sponsor::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function event()
    {
        return $this->belongsTo(Event::class);
    }
}
