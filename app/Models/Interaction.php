<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Interaction extends Model
{
    use HasFactory;

    protected $fillable = ['user_from_id', 'user_to_id', 'level', 'message', 'meta'];

    protected $casts = [
        'meta' => 'array',
    ];

    public function userFrom()
    {
        return $this->belongsTo(User::class, 'user_from_id');
    }

    public function userTo()
    {
        return $this->belongsTo(User::class, 'user_to_id');
    }

    public function satisfaction()
    {
        return $this->hasOne(Satisfaction::class);
    }
}
