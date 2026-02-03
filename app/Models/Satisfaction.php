<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Satisfaction extends Model
{
    use HasFactory;

    protected $fillable = ['interaction_id', 'rating', 'feedback', 'whatsapp_notified'];

    public function interaction()
    {
        return $this->belongsTo(Interaction::class);
    }
}
