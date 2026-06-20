<?php

namespace App\Models;

use App\Models\Concerns\ChecksTableAvailability;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BusinessMatch extends Model
{
    use HasFactory;
    use ChecksTableAvailability;

    protected $fillable = [
        'user_id',
        'matched_user_id',
        'score',
        'status',
    ];

    protected $casts = [
        'score' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function matchedUser()
    {
        return $this->belongsTo(User::class, 'matched_user_id');
    }
}
