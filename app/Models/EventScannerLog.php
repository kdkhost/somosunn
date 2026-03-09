<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EventScannerLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'event_id',
        'event_registration_id',
        'scanner_user_id',
        'ticket_code',
        'scanner_context',
        'outcome',
        'status_code',
        'message',
        'distance_meters',
        'latitude',
        'longitude',
        'ip_address',
        'user_agent',
        'attempted_at',
    ];

    protected $casts = [
        'distance_meters' => 'decimal:2',
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
        'attempted_at' => 'datetime',
    ];

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    public function registration()
    {
        return $this->belongsTo(EventRegistration::class, 'event_registration_id');
    }

    public function scannerUser()
    {
        return $this->belongsTo(User::class, 'scanner_user_id');
    }
}
