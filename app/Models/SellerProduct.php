<?php

namespace App\Models;

use App\Support\UploadStorage;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

class SellerProduct extends Model
{
    use HasFactory;

    public const SALES_CHANNELS = [
        'store_only' => 'Venda somente na loja virtual',
        'points_only' => 'Troca de pontos',
        'store_and_points' => 'Ambos os locais',
        'external_only' => 'Somente venda no site externo',
    ];

    protected static ?bool $tableAvailable = null;

    protected $fillable = [
        'seller_store_id',
        'user_id',
        'slug',
        'sku',
        'type',
        'title',
        'excerpt',
        'description',
        'cover_path',
        'price',
        'sale_price',
        'sale_price_ends_at',
        'stock',
        'weight_grams',
        'height_cm',
        'width_cm',
        'length_cm',
        'status',
        'sales_channel',
        'is_featured',
        'digital_delivery_type',
        'digital_file_path',
        'digital_file_name',
        'digital_url',
        'external_checkout_url',
        'points_reference_value',
        'digital_instructions',
        'metadata',
        'published_at',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'sale_price' => 'decimal:2',
        'points_reference_value' => 'decimal:2',
        'sale_price_ends_at' => 'datetime',
        'published_at' => 'datetime',
        'is_featured' => 'boolean',
        'metadata' => 'array',
    ];

    protected $appends = [
        'cover_url',
        'effective_price',
    ];

    public function store()
    {
        return $this->belongsTo(SellerStore::class, 'seller_store_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function media()
    {
        return $this->hasMany(SellerProductMedia::class)->orderBy('sort_order')->orderBy('id');
    }

    public function redeemableItem(): HasOne
    {
        return $this->hasOne(RedeemableItem::class, 'seller_product_id');
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', 'published');
    }

    public function getCoverUrlAttribute(): ?string
    {
        if (!blank($this->cover_path)) {
            return UploadStorage::url($this->cover_path);
        }

        $media = $this->relationLoaded('media')
            ? $this->media->firstWhere('media_type', 'image')
            : $this->media()->where('media_type', 'image')->orderBy('sort_order')->first();

        return $media?->file_url;
    }

    public function getEffectivePriceAttribute(): float
    {
        $salePrice = $this->sale_price;
        $saleEndsAt = $this->sale_price_ends_at;

        if ($salePrice !== null) {
            if ($saleEndsAt === null || $saleEndsAt->isFuture()) {
                return round((float) $salePrice, 2);
            }
        }

        return round((float) ($this->price ?? 0), 2);
    }

    public function isPhysical(): bool
    {
        return $this->type === 'physical';
    }

    public function isDigital(): bool
    {
        return $this->type === 'digital';
    }

    public function isPublished(): bool
    {
        return $this->status === 'published';
    }

    public function supportsInternalCheckout(): bool
    {
        $channel = (string) ($this->sales_channel ?: 'store_only');

        return in_array($channel, ['store_only', 'store_and_points'], true);
    }

    public function supportsPointsRedemption(): bool
    {
        $channel = (string) ($this->sales_channel ?: 'store_only');

        return in_array($channel, ['points_only', 'store_and_points'], true);
    }

    public function supportsExternalCheckout(): bool
    {
        return (string) ($this->sales_channel ?: 'store_only') === 'external_only' && filled($this->external_checkout_url);
    }

    public function salesChannelLabel(): string
    {
        return self::SALES_CHANNELS[(string) ($this->sales_channel ?: 'store_only')] ?? self::SALES_CHANNELS['store_only'];
    }

    public function pointsReferenceValue(): float
    {
        $referenceValue = $this->points_reference_value;
        if ($referenceValue !== null && (float) $referenceValue > 0) {
            return round((float) $referenceValue, 2);
        }

        return round((float) ($this->effective_price > 0 ? $this->effective_price : ($this->price ?? 0.01)), 2);
    }

    public static function tableAvailable(): bool
    {
        if (static::$tableAvailable !== null) {
            return static::$tableAvailable;
        }

        try {
            static::$tableAvailable = Schema::hasTable((new static())->getTable());
        } catch (\Throwable) {
            static::$tableAvailable = false;
        }

        return static::$tableAvailable;
    }
}
