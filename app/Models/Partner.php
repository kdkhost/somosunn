<?php

namespace App\Models;

use App\Support\UploadStorage;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class Partner extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'slug',
        'logo',
        'website_url',
        'description',
        'active',
        'order',
    ];

    protected $casts = [
        'active' => 'boolean',
        'order' => 'integer',
    ];

    protected static array $tableExistsCache = [];

    // ── Relações ────────────────────────────────────────────────────────────────
    /** Usuário membro responsável pelo parceiro */
    public function owner()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function coupons()
    {
        return $this->hasMany(PartnerCoupon::class)->orderBy('active', 'desc')->orderBy('expires_at');
    }

    public function activeCoupons()
    {
        return $this->hasMany(PartnerCoupon::class)
            ->where('active', true)
            ->where(function ($q) {
                $q->whereNull('expires_at')->orWhere('expires_at', '>=', now()->toDateString());
            });
    }

    // ── Scopes ──────────────────────────────────────────────────────────────────
    public function scopeActive($query)
    {
        return $query->where('active', true)->orderBy('order')->orderBy('name');
    }

    // ── Accessors ───────────────────────────────────────────────────────────────
    public function getLogoUrlAttribute(): string
    {
        return (string) UploadStorage::url($this->logo, '');
    }

    public static function tableExists(): bool
    {
        return static::schemaTableExists((new static())->getTable());
    }

    public static function couponsTableExists(): bool
    {
        return static::schemaTableExists((new PartnerCoupon())->getTable());
    }

    public function resolveRouteBinding($value, $field = null)
    {
        if (!static::tableExists()) {
            return null;
        }

        return parent::resolveRouteBinding($value, $field);
    }

    // ── Boot ─────────────────────────────────────────────────────────────────────
    protected static function boot()
    {
        parent::boot();
        static::creating(function ($partner) {
            if (empty($partner->slug)) {
                $partner->slug = Str::slug($partner->name);
            }
        });
        static::updating(function ($partner) {
            if ($partner->isDirty('name') && !$partner->isDirty('slug')) {
                $partner->slug = Str::slug($partner->name);
            }
        });
    }

    protected static function schemaTableExists(string $table): bool
    {
        if (array_key_exists($table, static::$tableExistsCache)) {
            return static::$tableExistsCache[$table];
        }

        try {
            return static::$tableExistsCache[$table] = Schema::hasTable($table);
        } catch (\Throwable $e) {
            return static::$tableExistsCache[$table] = false;
        }
    }
}
