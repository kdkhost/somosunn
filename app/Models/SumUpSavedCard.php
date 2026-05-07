<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SumUpSavedCard extends Model
{
    use HasFactory;

    protected $table = 'sumup_saved_cards';

    protected $fillable = [
        'user_id',
        'token',
        'last_four',
        'brand',
        'expires_at',
        'is_default',
    ];

    protected $casts = [
        'is_default' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getDisplayNameAttribute(): string
    {
        return strtoupper($this->brand) . ' •••• ' . $this->last_four . ' (' . $this->expires_at . ')';
    }
}
