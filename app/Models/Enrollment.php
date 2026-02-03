<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Enrollment extends Model
{
    use HasFactory;

    protected $fillable = ['user_id','enrollable_id','enrollable_type','status','progress','started_at','completed_at'];

    protected $casts = [
        'progress' => 'array',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function enrollable()
    {
        return $this->morphTo();
    }
}