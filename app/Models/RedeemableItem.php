<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RedeemableItem extends Model
{
    use HasFactory;

    public const ITEM_TYPES = [
        'physical' => 'Produto fisico',
        'digital' => 'Produto digital',
        'service' => 'Servico',
    ];

    protected $fillable = [
        'name',
        'description',
        'image',
        'points_cost',
        'stock',
        'is_active',
        'provider_type',
        'provider_user_id',
        'provider_name',
        'item_type',
        'fulfillment_instructions',
        'reference_value',
        'delivery_lead_days',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'points_cost' => 'integer',
        'stock' => 'integer',
        'reference_value' => 'decimal:2',
        'delivery_lead_days' => 'integer',
    ];

    public function redemptions()
    {
        return $this->hasMany(Redemption::class);
    }

    public function providerUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'provider_user_id');
    }

    public function getProviderLabelAttribute(): string
    {
        return trim((string) ($this->provider_name ?: config('app.name', 'SOMOS UNN')));
    }

    public function getItemTypeLabelAttribute(): string
    {
        $type = strtolower((string) ($this->item_type ?: 'service'));

        return self::ITEM_TYPES[$type] ?? self::ITEM_TYPES['service'];
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
}
