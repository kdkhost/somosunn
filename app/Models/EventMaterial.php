<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EventMaterial extends Model
{
    protected $fillable = [
        'event_id',
        'file_path',
        'file_name',
        'file_type',
        'file_size',
        'created_by',
    ];

    protected $casts = [
        'file_size' => 'integer',
        'created_by' => 'integer',
    ];

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    public function uploader()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
