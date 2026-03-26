<?php

namespace App\Models;

use App\Support\UploadStorage;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

class SellerStore extends Model
{
    use HasFactory;

    protected static ?bool $tableAvailable = null;

    protected $fillable = [
        'user_id',
        'slug',
        'brand_name',
        'tagline',
        'logo_path',
        'banner_path',
        'primary_color',
        'accent_color',
        'bio',
        'support_email',
        'support_phone',
        'whatsapp',
        'website_url',
        'instagram_url',
        'facebook_url',
        'youtube_url',
        'is_published',
        'is_blocked',
        'blocked_reason',
        'published_at',
        'slug_locked_at',
        'settings',
    ];

    protected $casts = [
        'is_published' => 'boolean',
        'is_blocked' => 'boolean',
        'published_at' => 'datetime',
        'slug_locked_at' => 'datetime',
        'settings' => 'array',
    ];

    protected $appends = [
        'logo_url',
        'banner_url',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function products()
    {
        return $this->hasMany(SellerProduct::class)->orderByDesc('is_featured')->orderByDesc('id');
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function getLogoUrlAttribute(): ?string
    {
        return UploadStorage::url($this->logo_path);
    }

    public function getBannerUrlAttribute(): ?string
    {
        return UploadStorage::url($this->banner_path);
    }

    public function isSlugLocked(): bool
    {
        return $this->slug_locked_at !== null;
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
