<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EventMedia extends Model
{
    protected $fillable = [
        'event_id',
        'user_id',
        'file_path',
        'type', // 'image' ou 'video'
        'watermarked'
    ];

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
