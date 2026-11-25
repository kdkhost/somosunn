<?php

namespace App\Models;

use App\Models\Concerns\ChecksTableAvailability;
use App\Support\UploadStorage;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

class SponsorBanner extends Model
{
    use HasFactory;
    use ChecksTableAvailability;

    public const STATUS_PENDING = 'pending';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';

    public const POSITIONS = [
        'global_banner' => 'Banner Global (Páginas Públicas)',
        'community_feed' => 'Feed da Comunidade (Entre Posts)',
        'home_top' => 'Home — Topo',
        'home_middle' => 'Home — Meio',
        'home_bottom' => 'Home — Rodapé',
        'event_sidebar' => 'Eventos — Lateral',
        'event_footer' => 'Eventos — Rodapé',
        'marketplace_top' => 'Marketplace — Topo',
        'course_top' => 'Cursos — Topo',
        'member_dashboard' => 'Painel do Membro',
    ];

    protected $fillable = [
        'sponsor_id',
        'sponsor_plan_id',
        'order_id',
        'title',
        'image',
        'url',
        'position',
        'starts_at',
        'ends_at',
        'active',
        'approval_status',
        'impressions_count',
        'clicks_count',
    ];

    protected $casts = [
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'active' => 'boolean',
        'impressions_count' => 'integer',
        'clicks_count' => 'integer',
    ];

    protected $appends = [
        'image_url',
    ];

    public function sponsor()
    {
        return $this->belongsTo(Sponsor::class);
    }

    public function plan()
    {
        return $this->belongsTo(SponsorPlan::class, 'sponsor_plan_id');
    }

    public function getImageUrlAttribute(): ?string
    {
        return UploadStorage::url($this->image);
    }

    public function getCtrAttribute(): float
    {
        $impressions = (int) ($this->impressions_count ?? 0);
        if ($impressions <= 0) {
            return 0.0;
        }

        return round(((int) ($this->clicks_count ?? 0) / $impressions) * 100, 2);
    }

    public function scopeLiveForContext(Builder $query, string $context): Builder
    {
        $positions = $context === 'feed'
            ? ['community_feed', 'member_dashboard']
            : ['global_banner', 'home_bottom', 'home_middle', 'home_top', 'marketplace_top', 'event_footer'];

        $now = now();

        $query->where('active', true)
            ->whereIn('position', $positions)
            ->where(function (Builder $q) use ($now) {
                $q->whereNull('starts_at')->orWhere('starts_at', '<=', $now);
            })
            ->where(function (Builder $q) use ($now) {
                $q->whereNull('ends_at')->orWhere('ends_at', '>=', $now);
            });

        if (Schema::hasColumn('sponsor_banners', 'approval_status')) {
            $query->where('approval_status', self::STATUS_APPROVED);
        }

        return $query;
    }
}
