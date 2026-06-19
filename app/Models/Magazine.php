<?php

namespace App\Models;

use App\Support\UploadStorage;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Magazine extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'title',
        'slug',
        'category',
        'edition',
        'published_at',
        'short_description',
        'full_description',
        'thumbnail',
        'pdf_file',
        'pages_count',
        'file_size_kb',
        'is_featured',
        'allow_download',
        'enable_sound',
        'status',
        'visibility',
        'views_count',
    ];

    protected $casts = [
        'published_at'   => 'date',
        'is_featured'    => 'boolean',
        'allow_download' => 'boolean',
        'enable_sound'   => 'boolean',
        'pages_count'    => 'integer',
        'file_size_kb'   => 'integer',
        'views_count'    => 'integer',
    ];

    protected static function booted(): void
    {
        static::creating(function (Magazine $magazine) {
            if (empty($magazine->slug)) {
                $magazine->slug = static::makeUniqueSlug($magazine->title);
            }
        });

        static::updating(function (Magazine $magazine) {
            if ($magazine->isDirty('title') && empty($magazine->getOriginal('slug'))) {
                $magazine->slug = static::makeUniqueSlug($magazine->title, $magazine->id);
            }
        });
    }

    protected static function makeUniqueSlug(string $title, ?int $ignoreId = null): string
    {
        $base = Str::slug($title);
        if ($base === '') {
            $base = 'revista';
        }

        for ($i = 1; $i <= 1000; $i++) {
            $slug = $i === 1 ? $base : $base . '-' . $i;
            $exists = static::query()
                ->where('slug', $slug)
                ->when($ignoreId, fn($q) => $q->where('id', '!=', $ignoreId))
                ->exists();

            if (!$exists) {
                return $slug;
            }
        }

        throw new \RuntimeException('Não foi possível gerar um slug único para a revista.');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function getThumbnailUrlAttribute(): ?string
    {
        if (empty($this->thumbnail)) {
            return null;
        }
        return UploadStorage::url($this->thumbnail);
    }

    public function getPdfUrlAttribute(): ?string
    {
        if (empty($this->pdf_file)) {
            return null;
        }
        return UploadStorage::url($this->pdf_file);
    }

    public function isPublished(): bool
    {
        return $this->status === 'published';
    }

    public function isOwnedBy($userId): bool
    {
        return (int) $this->user_id === (int) $userId;
    }

    /**
     * Scope: revistas visíveis ao usuário com base na visibilidade e no interesse "Notícias".
     */
    public function scopeVisibleTo($query, ?User $user)
    {
        $query->where('status', 'published');

        if (!$user) {
            return $query->where('visibility', 'public');
        }

        if ($user->isAdmin()) {
            return $query;
        }

        return $query->where(function ($q) use ($user) {
            $q->where('visibility', 'public')
              ->orWhere('visibility', 'members');

            if (static::userHasNewsInterest($user)) {
                $q->orWhere('visibility', 'interest');
            }
        });
    }

    /**
     * Verifica se o usuário marcou "Notícias" como interesse no perfil.
     */
    public static function userHasNewsInterest(?User $user): bool
    {
        if (!$user) {
            return false;
        }
        $raw = (string) ($user->interests ?? '');
        if ($raw === '') {
            return false;
        }
        $list = array_map(fn($v) => trim(mb_strtolower($v)), explode(',', $raw));
        $needles = ['notícias', 'noticias', 'news'];
        foreach ($needles as $needle) {
            if (in_array($needle, $list, true)) {
                return true;
            }
        }
        return false;
    }
}
