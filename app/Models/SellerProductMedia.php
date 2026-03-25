<?php

namespace App\Models;

use App\Support\UploadStorage;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SellerProductMedia extends Model
{
    use HasFactory;

    protected $fillable = [
        'seller_product_id',
        'media_type',
        'file_path',
        'sort_order',
        'alt_text',
    ];

    protected $appends = [
        'file_url',
    ];

    public function product()
    {
        return $this->belongsTo(SellerProduct::class, 'seller_product_id');
    }

    public function getFileUrlAttribute(): ?string
    {
        return UploadStorage::url($this->file_path);
    }
}
