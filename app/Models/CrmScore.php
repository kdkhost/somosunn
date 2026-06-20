<?php

namespace App\Models;

use App\Models\Concerns\ChecksTableAvailability;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CrmScore extends Model
{
    use HasFactory;
    use ChecksTableAvailability;

    protected $fillable = [
        'user_id',
        'score',
        'category',
        'last_activity',
    ];

    protected $casts = [
        'score' => 'integer',
        'last_activity' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
