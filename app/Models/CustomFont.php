<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomFont extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'type',
        'file_path',
        'google_font_url',
        'font_family',
        'is_active',
        'uploaded_by',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    // Get the usable CSS font-family value
    public function getFontFamilyAttribute($value)
    {
        return $value;
    }

    // Get the full URL or path for rendering
    public function getFontSource()
    {
        if ($this->type === 'google_link') {
            return $this->google_font_url;
        }
        return asset($this->file_path);
    }
}
