<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Redemption extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'redeemable_item_id',
        'provider_type',
        'provider_user_id',
        'provider_name',
        'item_type',
        'points_spent',
        'reference_value',
        'status',
        'admin_notes',
        'fulfillment_instructions',
        'delivery_notes',
        'tracking_code',
        'tracking_url',
        'estimated_delivery_at',
        'shipped_at',
        'completed_at',
        'cancelled_at',
    ];

    protected $casts = [
        'reference_value' => 'decimal:2',
        'estimated_delivery_at' => 'datetime',
        'shipped_at' => 'datetime',
        'completed_at' => 'datetime',
        'cancelled_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function item()
    {
        return $this->belongsTo(RedeemableItem::class, 'redeemable_item_id');
    }

    public function providerUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'provider_user_id');
    }

    public function canBeManagedBy(User $user): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        return $this->provider_type === 'seller'
            && (int) $this->provider_user_id === (int) $user->id
            && $user->canSellOnMarketplace();
    }

    public function getProviderLabelAttribute(): string
    {
        return trim((string) ($this->provider_name ?: config('app.name', 'SOMOS UNN')));
    }

    public function getItemTypeLabelAttribute(): string
    {
        $type = strtolower((string) ($this->item_type ?: ($this->item->item_type ?? 'service')));

        return RedeemableItem::ITEM_TYPES[$type] ?? RedeemableItem::ITEM_TYPES['service'];
    }
}
