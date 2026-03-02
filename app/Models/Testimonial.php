<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Testimonial extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'author_name',
        'author_title',
        'rating',
        'content',
        'status',
        'is_featured',
        'is_active',
        'source',
        'external_id',
        'avatar_url',
        'moderated_by',
        'moderated_at',
        'moderation_notes',
    ];

    protected $casts = [
        'rating'       => 'integer',
        'is_featured'  => 'boolean',
        'is_active'    => 'boolean',
        'moderated_at' => 'datetime',
    ];

    // ─── Relacionamentos ─────────────────────────────────────────────────────

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function moderator()
    {
        return $this->belongsTo(User::class, 'moderated_by');
    }

    // ─── Scopes ──────────────────────────────────────────────────────────────

    /** Depoimentos visíveis no site público: aprovados e ativos. */
    public function scopeForSite($query)
    {
        return $query->where('status', 'approved')->where('is_active', true);
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    // ─── Acessores ───────────────────────────────────────────────────────────

    /**
     * URL do avatar resolvida:
     *   1. Foto do member (se vinculado a um usuário)
     *   2. avatar_url (reviews do Google)
     *   3. null → template renderiza inicial do nome
     */
    public function getResolvedAvatarAttribute(): ?string
    {
        if ($this->user_id && $this->relationLoaded('user') && $this->user) {
            try {
                return $this->user->profile_photo_url ?: null;
            } catch (\Throwable) {
                // ignora: model sem profile_photo_url
            }
        }

        return $this->avatar_url ?: null;
    }

    /** Nome de exibição: author_name > nome do user vinculado > 'Anônimo'. */
    public function getDisplayNameAttribute(): string
    {
        if (!empty($this->author_name)) {
            return $this->author_name;
        }

        if ($this->user_id && $this->relationLoaded('user') && $this->user) {
            return $this->user->name ?? 'Anônimo';
        }

        return 'Anônimo';
    }
}

