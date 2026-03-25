<?php

namespace App\Models;

use App\Support\UploadStorage;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SellerProduct extends Model
{
    use HasFactory;

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
        'is_featured',
        'digital_delivery_type',
        'digital_file_path',
        'digital_file_name',
        'digital_url',
        'digital_instructions',
        'metadata',
        'published_at',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'sale_price' => 'decimal:2',
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
}
