<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Mentorship extends Model
{
    use HasFactory;

    protected $fillable = ['title','mentor_id','description','price','slots','schedule'];

    protected $casts = [
        'schedule' => 'array',
    ];

    public function mentor()
    {
        return $this->belongsTo(User::class, 'mentor_id');
    }
}