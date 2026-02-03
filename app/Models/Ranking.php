<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ranking extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'level', 'interactions_count', 'average_rating', 'score'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
