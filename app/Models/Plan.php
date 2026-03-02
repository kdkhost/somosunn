<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Plan extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'price',
        'period',
        'billing_cycle',
        'prorata',
        'description',
        'image',
        'is_featured',
        'highlight_legacy',
        'highlight',
        'coupons_enabled',
        'benefits',
        'permissions',
        'comparison',
        'is_active',
        'is_free',
        'mp_plan_id',
        'is_recurring',
        'sort_order',
        'price_periods',
    ];

    protected $casts = [
        'is_featured' => 'boolean',
        'highlight_legacy' => 'boolean',
        'highlight' => 'boolean',
        'coupons_enabled' => 'boolean',
        'is_active' => 'boolean',
        'prorata' => 'boolean',
        'is_recurring' => 'boolean',
        'is_free' => 'boolean',
        'benefits' => 'array',
        'permissions' => 'array',
        'comparison' => 'array',
        'price' => 'decimal:2',
        'price_periods' => 'array',
        'sort_order' => 'integer',
    ];

    /**
     * Retorna o plano gratuito padrão da plataforma.
     * Prioridade: is_free=true → slug 'cliente' → menor preço.
     */
    public static function getFreePlan(): ?self
    {
        try {
            return static::query()->where('is_free', true)->where('is_active', true)->first()
                ?? static::query()->where('slug', 'cliente')->where('is_active', true)->first()
                ?? static::query()->where('price', 0)->where('is_active', true)->orderBy('id')->first()
                ?? static::query()->where('is_active', true)->orderBy('price')->orderBy('id')->first();
        } catch (\Throwable $e) {
            return null;
        }
    }

    /**
     * Retorna o preço para um período específico.
     * Usa price_periods se disponível, caso contrário retorna o preço base.
     *
     * @param string|null $period mensal|trimestral|semestral|anual
     */
    public function getPriceForPeriod(?string $period = null): float
    {
        $period = $period ? strtolower(trim($period)) : 'mensal';
        $periods = $this->price_periods;
        if (is_array($periods) && isset($periods[$period]) && $periods[$period] > 0) {
            return (float) $periods[$period];
        }
        return (float) $this->price;
    }

    /**
     * Retorna todos os períodos disponíveis com seus preços.
     * Sempre inclui pelo menos 'mensal' com o preço base.
     */
    public function getAvailablePeriods(): array
    {
        $base = [
            'mensal'     => (float) $this->price,
            'trimestral' => null,
            'semestral'  => null,
            'anual'      => null,
        ];

        $periods = $this->price_periods;
        if (is_array($periods)) {
            foreach ($periods as $key => $value) {
                if (array_key_exists($key, $base) && $value > 0) {
                    $base[$key] = (float) $value;
                }
            }
        }

        // Remove períodos sem preço definido (exceto mensal)
        return array_filter($base, fn($v, $k) => $k === 'mensal' || $v !== null, ARRAY_FILTER_USE_BOTH);
    }

    /**
     * Calcula prorrata para upgrade de plano:
     * dias restantes no plano atual × (novo_preço_diário − preço_diário_atual).
     */
    public static function calculateProrata(self $currentPlan, self $newPlan, string $period = 'mensal'): float
    {
        $daysInPeriod = match ($period) {
            'trimestral' => 90,
            'semestral'  => 180,
            'anual'      => 365,
            default      => 30,
        };

        /** @var \App\Models\User $user */
        $user = auth()->user();
        $expiresAt = $user?->plan_expires_at;

        $daysRemaining = $expiresAt ? max(0, (int) now()->diffInDays($expiresAt, false)) : 0;
        if ($daysRemaining <= 0) {
            return $newPlan->getPriceForPeriod($period);
        }

        $dailyCurrent = $currentPlan->getPriceForPeriod($period) / $daysInPeriod;
        $dailyNew     = $newPlan->getPriceForPeriod($period) / $daysInPeriod;
        $diff = ($dailyNew - $dailyCurrent) * $daysRemaining;

        return max(0, round($diff, 2));
    }

    public function hasFeature($feature)
    {
        $feature = (string) $feature;
        $feature = trim($feature);
        if ($feature === '') {
            return false;
        }

        $features = $this->permissions ?? [];
        if (!is_array($features)) {
            $features = [];
        }

        if (in_array('*', $features, true)) {
            return true;
        }

        $checks = array_values(array_unique(array_merge([$feature], self::aliasesForFeature($feature))));
        foreach ($checks as $check) {
            if (in_array($check, $features, true)) {
                return true;
            }
        }

        // Compatibilidade: versões antigas gravavam permissões "admin-like" (ex.: courses.view)
        $legacyPrefixes = [
            'courses' => 'courses.',
            'events' => 'events.',
            'mentorships' => 'mentorships.',
        ];

        foreach ($checks as $check) {
            if (!isset($legacyPrefixes[$check])) {
                continue;
            }

            $prefix = $legacyPrefixes[$check];
            foreach ($features as $value) {
                if (!is_string($value)) {
                    continue;
                }
                if (str_starts_with($value, $prefix)) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Resolve aliases de features para manter compatibilidade entre:
     * - Chaves em rotas/middlewares (ex.: courses_access, events_create)
     * - Chaves em planos/telas antigas (ex.: courses, events.create)
     *
     * @param string $feature
     * @return array<string>
     */
    public static function aliasesForFeature(string $feature): array
    {
        $feature = trim($feature);
        if ($feature === '') {
            return [];
        }

        $aliases = [];

        // Navbar/legado: admin_panel vs admin.panel
        if ($feature === 'admin_panel') {
            $aliases[] = 'admin.panel';
        } elseif ($feature === 'admin.panel') {
            $aliases[] = 'admin_panel';
        }

        // Access pairs (site/painel)
        $accessPairs = [
            'community' => 'community_access',
            'chat' => 'chat_access',
            'courses' => 'courses_access',
            'events' => 'events_access',
            'mentorships' => 'mentorships_access',
            'rankings' => 'ranking_access',
            'marketplace' => 'marketplace.buy',
        ];

        foreach ($accessPairs as $base => $access) {
            if ($feature === $base) {
                $aliases[] = $access;
            } elseif ($feature === $access) {
                $aliases[] = $base;
            }
        }

        // Pontuação costuma andar junto com rankings
        if ($feature === 'rankings') {
            $aliases[] = 'points_access';
        } elseif ($feature === 'points_access') {
            $aliases[] = 'rankings';
        }

        // CRUD patterns: courses_create <-> courses.create (idem events/mentorships)
        if (preg_match('/^(courses|events|mentorships)_(create|edit|delete)$/', $feature, $m)) {
            $aliases[] = $m[1] . '.' . $m[2];
        } elseif (preg_match('/^(courses|events|mentorships)\\.(create|edit|delete)$/', $feature, $m)) {
            $aliases[] = $m[1] . '_' . $m[2];
        }

        // Reviews (granular) -> editor/gestão
        if ($feature === 'courses_review') {
            $aliases[] = 'courses.edit';
            $aliases[] = 'courses_edit';
        } elseif ($feature === 'mentorships_review') {
            $aliases[] = 'mentorships.edit';
            $aliases[] = 'mentorships_edit';
        }

        // Event reserve (granular) -> acesso a eventos
        if ($feature === 'events_reserve') {
            $aliases[] = 'events';
            $aliases[] = 'events_access';
        }

        // Lessons access/granular -> acesso a cursos
        if ($feature === 'courses_lessons_access') {
            $aliases[] = 'courses';
            $aliases[] = 'courses_access';
        } elseif ($feature === 'courses_lessons_create') {
            $aliases[] = 'courses.create';
            $aliases[] = 'courses_create';
        } elseif ($feature === 'courses_lessons_edit') {
            $aliases[] = 'courses.edit';
            $aliases[] = 'courses_edit';
        } elseif ($feature === 'courses_lessons_delete') {
            $aliases[] = 'courses.delete';
            $aliases[] = 'courses_delete';
        }

        // Attachments granular -> downloads/edição de curso
        if ($feature === 'courses_lessons_attachments_download') {
            $aliases[] = 'courses.downloads';
        } elseif ($feature === 'courses.downloads') {
            $aliases[] = 'courses_lessons_attachments_download';
        }

        if (
            in_array($feature, [
                'courses_lessons_attachments_upload',
                'courses_lessons_attachments_edit',
            ], true)
        ) {
            $aliases[] = 'courses.edit';
            $aliases[] = 'courses_edit';
        } elseif ($feature === 'courses_lessons_attachments_delete') {
            $aliases[] = 'courses.delete';
            $aliases[] = 'courses_delete';
        }

        // Certificados granular -> cursos.certificates (site)
        if (str_starts_with($feature, 'certificates_')) {
            $aliases[] = 'courses.certificates';
        } elseif ($feature === 'courses.certificates') {
            $aliases[] = 'certificates_access';
            $aliases[] = 'certificates_create';
            $aliases[] = 'certificates_generate';
            $aliases[] = 'certificates_delete';
        }

        // marketplace.buy <-> marketplace (acesso genérico)
        if ($feature === 'marketplace.buy') {
            $aliases[] = 'marketplace';
        } elseif ($feature === 'marketplace') {
            $aliases[] = 'marketplace.buy';
        }

        $aliases = array_values(array_unique(array_filter($aliases, static fn($v) => is_string($v) && trim($v) !== '' && $v !== $feature)));
        return $aliases;
    }
}
