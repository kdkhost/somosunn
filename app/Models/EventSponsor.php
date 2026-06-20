<?php

namespace App\Models;

use App\Models\Concerns\ChecksTableAvailability;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EventSponsor extends Model
{
    use HasFactory;
    use ChecksTableAvailability;

    protected $fillable = [
        'event_id',
        'sponsor_id',
        'type',
        'amount',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    public function sponsor()
    {
        return $this->belongsTo(Sponsor::class);
    }
}
