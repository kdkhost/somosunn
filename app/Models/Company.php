<?php

namespace App\Models;

use App\Models\Concerns\ChecksTableAvailability;
use App\Support\UploadStorage;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    use HasFactory;
    use ChecksTableAvailability;

    protected $fillable = [
        'name',
        'slug',
        'cnpj',
        'email',
        'phone',
        'whatsapp',
        'website',
        'instagram',
        'linkedin',
        'youtube',
        'logo',
        'banner',
        'description',
        'city',
        'state',
        'verified',
        'active',
    ];

    protected $casts = [
        'verified' => 'boolean',
        'active' => 'boolean',
    ];

    protected $appends = [
        'logo_url',
        'banner_url',
    ];

    public function users()
    {
        return $this->belongsToMany(User::class, 'company_users')
            ->using(CompanyUser::class)
            ->withPivot('role')
            ->withTimestamps();
    }

    public function memberships()
    {
        return $this->hasMany(CompanyUser::class);
    }

    public function sponsors()
    {
        return $this->hasMany(Sponsor::class);
    }

    public function activeSponsor()
    {
        return $this->hasOne(Sponsor::class)
            ->where('status', Sponsor::STATUS_ACTIVE)
            ->latestOfMany();
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function getLogoUrlAttribute(): ?string
    {
        return UploadStorage::url($this->logo);
    }

    public function getBannerUrlAttribute(): ?string
    {
        return UploadStorage::url($this->banner);
    }
}
