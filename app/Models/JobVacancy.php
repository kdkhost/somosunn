<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JobVacancy extends Model
{
    use HasFactory;

    protected $appends = ['image_url'];

    protected $fillable = [
        'user_id',
        'title',
        'company_name',
        'location',
        'type',
        'level',
        'short_description',
        'description',
        'requirements',
        'benefits',
        'salary_range',
        'image',
        'visibility',
        'is_active',
        'is_demo',
        'expires_at',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_demo' => 'boolean',
        'expires_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function applications()
    {
        return $this->hasMany(JobApplication::class);
    }
    public function getImageUrlAttribute(): string
    {
        return \App\Support\UploadStorage::url($this->image);
    }
}
