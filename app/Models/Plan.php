<?php

namespace App\Models;

use App\Support\UploadStorage;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Plan extends Model
{
    use HasFactory;

    public const PERIOD_KEYS = [
        'mensal',
        'trimestral',
        'semestral',
        'anual',
    ];

    public const PERIOD_LABELS = [
        'mensal' => 'Mensal',
        'trimestral' => 'Trimestral',
        'semestral' => 'Semestral',
        'anual' => 'Anual',
    ];

    public const PERIOD_MULTIPLIERS = [
        'mensal' => 1,
        'trimestral' => 3,
        'semestral' => 6,
        'anual' => 12,
    ];

    public const DEFAULT_FREE_PLAN_PERMISSIONS = [
        'community',
        'rankings',
    ];

    public const DEFAULT_FREE_PLAN_BENEFITS = [
        'Acesso a comunidade do Somos UNN',
        'Participacao nos eventos presenciais e gratuitos',
        'Visualizacao do ranking de membros',
        'Acesso aos cursos gratuitos',
    ];

    public const DEFAULT_FREE_PLAN_DESCRIPTION = 'Acesso a comunidade do Somos UNN, participacao nos eventos presenciais e gratuitos, visualizacao do ranking de membros e acesso aos cursos gratuitos.';

    public const PRO_PLAN_PERMISSIONS = [
        'community',
        'chat',
        'connections',
        'connections.unlimited',
        'courses',
        'courses.certificates',
        'courses.downloads',
        'events',
        'mentorships',
        'mentorships.group',
        'mentorships.individual',
        'benefits.club.access',
        'events.pitch.priority',
        'events.keynote.annual',
        'events.first_lot',
        'rankings',
    ];

    public const ELITE_PLAN_PERMISSIONS = [
        'community',
        'chat',
        'connections',
        'connections.unlimited',
        'courses',
        'courses.certificates',
        'courses.downloads',
        'courses.create',
        'courses.edit',
        'courses.delete',
        'events',
        'events.create',
        'events.edit',
        'events.delete',
        'events.exhibitors.manage',
        'mentorships',
        'mentorships.group',
        'mentorships.individual',
        'mentorships.create',
        'mentorships.edit',
        'mentorships.delete',
        'marketplace',
        'marketplace.buy',
        'marketplace.sales',
        'marketplace.sell',
        'benefits.club.access',
        'benefits.club.partner',
        'events.pitch.priority',
        'events.keynote.annual',
        'events.first_lot',
        'events.mentor',
        'rankings',
    ];

    public const FREE_PLAN_RESTRICTED_FEATURES = [
        'courses',
        'courses_access',
        'courses_lessons_access',
        'courses.downloads',
        'courses.certificates',
        'events',
        'events_access',
        'events.exhibitors.manage',
        'events_exhibitors_manage',
        'events.recordings',
        'events.vip',
        'events.pitch.priority',
        'events.keynote.annual',
        'events.first_lot',
        'events.mentor',
        'mentorships',
        'mentorships_access',
        'mentorships.group',
        'mentorships.individual',
        'benefits.club.access',
        'benefits.club.partner',
    ];

    public const FEATURE_LABELS = [
        'community' => 'Comunidade e perfil interno',
        'chat' => 'Chat entre membros',
        'connections' => 'Networking entre membros',
        'connections.unlimited' => 'Networking sem limite',
        'courses' => 'Consumir cursos completos',
        'courses.create' => 'Publicar cursos',
        'courses.edit' => 'Editar cursos publicados',
        'courses.delete' => 'Remover cursos publicados',
        'courses.certificates' => 'Certificados de cursos',
        'courses.downloads' => 'Downloads de materiais',
        'events' => 'Participar dos eventos',
        'events.create' => 'Criar e divulgar eventos',
        'events.edit' => 'Editar eventos publicados',
        'events.delete' => 'Remover eventos publicados',
        'events.exhibitors.manage' => 'Gerenciar areas para expositores',
        'admin.events.coupons.view' => 'Ver cupons de eventos',
        'admin.events.coupons.create' => 'Criar cupons de eventos',
        'admin.events.coupons.edit' => 'Editar cupons de eventos',
        'admin.events.coupons.delete' => 'Excluir cupons de eventos',
        'admin.events.coupons.toggle' => 'Ativar cupons de eventos',
        'admin.events.group_link.manage' => 'Gerenciar link do grupo do evento',
        'events.recordings' => 'Acesso a gravacoes de eventos',
        'events.vip' => 'Eventos VIP/exclusivos',
        'events.pitch.priority' => 'Pitch diferenciado nos eventos',
        'events.keynote.annual' => 'Apresentacao principal anual (15+5)',
        'events.first_lot' => 'Compra prioritaria com primeiro lote',
        'events.mentor' => 'Mentoria nas dinamicas dos eventos',
        'mentorships' => 'Consumir mentorias',
        'mentorships.create' => 'Publicar mentorias',
        'mentorships.edit' => 'Editar mentorias publicadas',
        'mentorships.delete' => 'Remover mentorias publicadas',
        'mentorships.group' => 'Mentorias em grupo',
        'mentorships.individual' => 'Mentorias individuais',
        'marketplace' => 'Acesso ao marketplace',
        'marketplace.sales' => 'Historico de vendas',
        'marketplace.buy' => 'Compras no marketplace',
        'marketplace.sell' => 'Vendas no marketplace',
        'benefits.club.access' => 'Consumir o clube de beneficios',
        'benefits.club.partner' => 'Perfil no clube de beneficios e cupons',
        'rankings' => 'Ranking de membros',
        'support.priority' => 'Suporte prioritario',
        'early.access' => 'Acesso antecipado a novidades',
        'admin.panel' => 'Acesso ao painel admin',
        'magazines.access' => 'Acessar revistas digitais',
        'magazines.publish' => 'Publicar revistas (Editor)',
        'sponsor.dashboard' => 'Painel do patrocinador',
        'sponsor.leads' => 'Leads do patrocinador',
        'sponsor.billing' => 'Financeiro do patrocinador',
        'sponsor.reports' => 'Relatorios do patrocinador',
        'sponsor.events' => 'Eventos patrocinados',
        'sponsor.campaigns' => 'Campanhas do patrocinador',
    ];

    public const FEATURE_GROUPS = [
        'Experiencia do membro' => [
            'community',
            'chat',
            'connections',
            'connections.unlimited',
            'rankings',
            'benefits.club.access',
        ],
        'Conteudo e mentorias' => [
            'courses',
            'courses.certificates',
            'courses.downloads',
            'mentorships',
            'mentorships.group',
            'mentorships.individual',
        ],
        'Eventos e visibilidade' => [
            'events',
            'events.recordings',
            'events.vip',
            'events.pitch.priority',
            'events.keynote.annual',
            'events.first_lot',
            'events.mentor',
        ],
        'Criacao e vendas' => [
            'marketplace',
            'marketplace.buy',
            'marketplace.sales',
            'marketplace.sell',
            'courses.create',
            'courses.edit',
            'courses.delete',
            'events.create',
            'events.edit',
            'events.delete',
            'events.exhibitors.manage',
            'admin.events.coupons.view',
            'admin.events.coupons.create',
            'admin.events.coupons.edit',
            'admin.events.coupons.delete',
            'admin.events.coupons.toggle',
            'admin.events.group_link.manage',
            'mentorships.create',
            'mentorships.edit',
            'mentorships.delete',
            'magazines.access',
            'magazines.publish',
            'benefits.club.partner',
        ],
        'Administracao' => [
            'support.priority',
            'early.access',
            'admin.panel',
        ],
        'Patrocinadores' => [
            'sponsor.dashboard',
            'sponsor.leads',
            'sponsor.billing',
            'sponsor.reports',
            'sponsor.events',
            'sponsor.campaigns',
        ],
    ];

    public const PREMIUM_COMPARISON_ROWS = [
        ['label' => 'Comunidade e perfil interno', 'permission' => 'community'],
        ['label' => 'Ranking de membros', 'permission' => 'rankings'],
        ['label' => 'Eventos de networking', 'permission' => 'events'],
        ['label' => 'Cursos completos', 'permission' => 'courses'],
        ['label' => 'Mentorias', 'permission' => 'mentorships'],
        ['label' => 'Clube de beneficios', 'permission' => 'benefits.club.access'],
        ['label' => 'Pitch diferenciado em eventos', 'permission' => 'events.pitch.priority'],
        ['label' => 'Apresentacao principal anual', 'permission' => 'events.keynote.annual'],
        ['label' => 'Primeiro lote garantido', 'permission' => 'events.first_lot'],
        ['label' => 'Publicar cursos', 'permission' => 'courses.create'],
        ['label' => 'Publicar mentorias', 'permission' => 'mentorships.create'],
        ['label' => 'Criar e divulgar eventos', 'permission' => 'events.create'],
        ['label' => 'Gerenciar areas para expositores', 'permission' => 'events.exhibitors.manage'],
        ['label' => 'Mentoria nas dinamicas dos eventos', 'permission' => 'events.mentor'],
        ['label' => 'Perfil parceiro e cupons', 'permission' => 'benefits.club.partner'],
    ];

    public const COMMERCIAL_BLUEPRINTS = [
        'free' => [
            'description' => self::DEFAULT_FREE_PLAN_DESCRIPTION,
            'benefits' => self::DEFAULT_FREE_PLAN_BENEFITS,
            'permissions' => self::DEFAULT_FREE_PLAN_PERMISSIONS,
            'period' => 'mensal',
            'period_settings' => [
                'mensal' => ['enabled' => true],
                'trimestral' => ['enabled' => false],
                'semestral' => ['enabled' => false],
                'anual' => ['enabled' => false],
            ],
        ],
        'pro' => [
            'description' => 'Plano voltado para membros que desejam consumir cursos e mentorias, participar dos eventos de networking e ganhar prioridade comercial nos encontros da comunidade.',
            'benefits' => [
                'Acesso a comunidade de membros do Somos UNN',
                'Consumo completo de cursos e mentorias',
                'Consumo do clube de beneficios',
                'Participacao nos eventos de networking',
                'Pitch diferenciado em todos os eventos do grupo',
                '1 apresentacao principal de 15 min + 5 min de perguntas por ano',
                'Compra prioritaria com valor de primeiro lote em todos os eventos',
                'Perfil na rede social interna e participacao no ranking',
            ],
            'permissions' => self::PRO_PLAN_PERMISSIONS,
            'period' => 'trimestral',
            'period_settings' => [
                'mensal' => ['enabled' => false],
                'trimestral' => ['enabled' => true],
                'semestral' => ['enabled' => true],
                'anual' => ['enabled' => true],
            ],
        ],
        'elite' => [
            'description' => 'Plano para especialistas e criadores que querem publicar cursos, mentorias e eventos, atuar nas dinamicas da comunidade e converter networking em vendas dentro da plataforma.',
            'benefits' => [
                'Publicacao de cursos e mentorias',
                'Criacao e divulgacao dos seus eventos dentro da plataforma',
                'Mentoria nas dinamicas dos eventos do grupo',
                'Perfil no clube de beneficios com geracao de cupons',
                'Consumo do clube de beneficios e eventos de networking',
                'Pitch diferenciado em todos os eventos do grupo',
                '1 apresentacao principal de 15 min + 5 min de perguntas por ano',
                'Compra prioritaria com valor de primeiro lote em todos os eventos',
                'Perfil na rede social interna e participacao no ranking',
            ],
            'permissions' => self::ELITE_PLAN_PERMISSIONS,
            'period' => 'trimestral',
            'period_settings' => [
                'mensal' => ['enabled' => false],
                'trimestral' => ['enabled' => true],
                'semestral' => ['enabled' => true],
                'anual' => ['enabled' => true],
            ],
        ],
    ];

    protected $fillable = [
        'name',
        'slug',
        'seller_id',
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
        'period_settings',
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
        'period_settings' => 'array',
        'sort_order' => 'integer',
    ];

    public function seller(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'seller_id');
    }

    public function getImageUrlAttribute(): ?string
    {
        return UploadStorage::url($this->image);
    }

    public function isFreeAccessPlan(): bool
    {
        return (bool) $this->is_free || (float) ($this->price ?? 0) <= 0;
    }

    public function marketingDescription(): string
    {
        if ($this->isFreeAccessPlan()) {
            return self::DEFAULT_FREE_PLAN_DESCRIPTION;
        }

        $description = trim((string) ($this->description ?? ''));
        if ($description !== '') {
            return $description;
        }

        $blueprint = self::blueprintForPlan((string) ($this->slug ?? ''), false);
        return trim((string) ($blueprint['description'] ?? ''));
    }

    public function resolvedBenefits(): array
    {
        if ($this->isFreeAccessPlan()) {
            return self::DEFAULT_FREE_PLAN_BENEFITS;
        }

        $benefits = $this->benefits ?? [];

        if (!is_array($benefits)) {
            $benefits = [];
        }

        $benefits = array_values(array_filter($benefits, static fn ($value) => is_string($value) && trim($value) !== ''));
        if ($benefits !== []) {
            return $benefits;
        }

        $blueprint = self::blueprintForPlan((string) ($this->slug ?? ''), false);
        $fallback = $blueprint['benefits'] ?? [];

        return is_array($fallback)
            ? array_values(array_filter($fallback, static fn ($value) => is_string($value) && trim($value) !== ''))
            : [];
    }

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

    public function resolvedPricePeriods(): array
    {
        $basePrice = round((float) ($this->price ?? 0), 2);
        $prices = [
            'mensal' => $basePrice,
            'trimestral' => null,
            'semestral' => null,
            'anual' => null,
        ];

        $stored = $this->price_periods;
        if (is_array($stored)) {
            foreach (self::PERIOD_KEYS as $period) {
                if (!array_key_exists($period, $stored)) {
                    continue;
                }

                $value = self::parseMoneyValue($stored[$period]);
                if ($value === null) {
                    continue;
                }

                $prices[$period] = round($value, 2);
            }
        }

        if ($this->isFreeAccessPlan()) {
            $prices['mensal'] = 0.0;
        }

        return $prices;
    }

    public function resolvedPeriodSettings(): array
    {
        $prices = $this->resolvedPricePeriods();
        $settings = self::normalizePeriodSettings($this->period_settings ?? [], $prices, $this->isFreeAccessPlan());

        if ($this->isFreeAccessPlan()) {
            return $settings;
        }

        foreach (self::PERIOD_KEYS as $period) {
            if (($settings[$period]['enabled'] ?? false) && !array_key_exists($period, $prices)) {
                $settings[$period]['enabled'] = false;
            }
        }

        if (!self::hasEnabledPeriod($settings)) {
            $fallback = self::firstEnabledPeriodFromPrices($prices);
            $settings[$fallback]['enabled'] = true;
        }

        return $settings;
    }

    public function firstAvailablePeriod(): string
    {
        $available = $this->getAvailablePeriods();
        $first = array_key_first($available);

        return is_string($first) ? $first : 'mensal';
    }

    public function getPriceForPeriod(?string $period = null): float
    {
        $available = $this->getAvailablePeriods();
        $period = self::sanitizePeriod($period);

        if (!array_key_exists($period, $available)) {
            $period = $this->firstAvailablePeriod();
        }

        return round((float) ($available[$period] ?? 0), 2);
    }

    public function getAvailablePeriods(): array
    {
        $prices = $this->resolvedPricePeriods();
        $settings = $this->resolvedPeriodSettings();
        $available = [];

        foreach (self::PERIOD_KEYS as $period) {
            if (!(bool) ($settings[$period]['enabled'] ?? false)) {
                continue;
            }

            $price = $prices[$period] ?? null;
            if ($price === null) {
                continue;
            }

            if (!$this->isFreeAccessPlan() && (float) $price <= 0) {
                continue;
            }

            $available[$period] = round((float) $price, 2);
        }

        if ($available !== []) {
            return $available;
        }

        if ($this->isFreeAccessPlan()) {
            return ['mensal' => 0.0];
        }

        return ['mensal' => round((float) ($this->price ?? 0), 2)];
    }

    public static function calculateProrata(self $currentPlan, self $newPlan, string $period = 'mensal'): float
    {
        $period = self::sanitizePeriod($period);
        $daysInPeriod = match ($period) {
            'trimestral' => 90,
            'semestral' => 180,
            'anual' => 365,
            default => 30,
        };

        /** @var \App\Models\User $user */
        $user = auth()->user();
        $expiresAt = $user?->plan_expires_at;

        $daysRemaining = $expiresAt ? max(0, (int) now()->diffInDays($expiresAt, false)) : 0;
        if ($daysRemaining <= 0) {
            return $newPlan->getPriceForPeriod($period);
        }

        $dailyCurrent = $currentPlan->getPriceForPeriod($period) / $daysInPeriod;
        $dailyNew = $newPlan->getPriceForPeriod($period) / $daysInPeriod;
        $diff = ($dailyNew - $dailyCurrent) * $daysRemaining;

        return max(0, round($diff, 2));
    }

    public function hasFeature($feature)
    {
        $feature = trim((string) $feature);
        if ($feature === '') {
            return false;
        }

        $features = $this->resolvedPermissions();

        if (in_array('*', $features, true)) {
            return true;
        }

        $checks = array_values(array_unique(array_merge([$feature], self::aliasesForFeature($feature))));
        foreach ($checks as $check) {
            if (in_array($check, $features, true)) {
                return true;
            }
        }

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

    public function resolvedPermissions(): array
    {
        $features = $this->permissions ?? [];
        if (!is_array($features)) {
            $features = [];
        }

        if ($features === []) {
            $blueprint = self::blueprintForPlan((string) ($this->slug ?? ''), $this->isFreeAccessPlan());
            $features = is_array($blueprint['permissions'] ?? null) ? $blueprint['permissions'] : [];
        }

        return self::normalizeCommercialPermissions(
            $features,
            (bool) $this->is_free,
            (float) ($this->price ?? 0)
        );
    }

    public static function normalizeCommercialPermissions(array $permissions, bool $isFree, float $price = 0): array
    {
        $permissions = array_values(array_unique(array_filter(
            $permissions,
            static fn ($value) => is_string($value) && trim($value) !== ''
        )));

        if ($isFree || $price <= 0) {
            $permissions = array_values(array_diff(
                $permissions,
                array_merge(self::PRO_PLAN_PERMISSIONS, self::ELITE_PLAN_PERMISSIONS, self::FREE_PLAN_RESTRICTED_FEATURES)
            ));

            return array_values(array_unique(array_merge($permissions, self::DEFAULT_FREE_PLAN_PERMISSIONS)));
        }

        return $permissions;
    }

    public static function parseMoneyValue(mixed $value): ?float
    {
        if ($value === null) {
            return null;
        }

        if (is_int($value) || is_float($value)) {
            return round((float) $value, 2);
        }

        $value = trim((string) $value);
        if ($value === '') {
            return null;
        }

        $value = str_replace(['R$', ' ', "\u{00A0}"], '', $value);
        $value = preg_replace('/[^0-9,.\-]/', '', $value) ?? '';
        if ($value === '' || $value === '-') {
            return null;
        }

        $hasComma = str_contains($value, ',');
        $hasDot = str_contains($value, '.');

        if ($hasComma && $hasDot) {
            $lastComma = strrpos($value, ',');
            $lastDot = strrpos($value, '.');

            if ($lastComma !== false && $lastDot !== false && $lastComma > $lastDot) {
                $value = str_replace('.', '', $value);
                $value = str_replace(',', '.', $value);
            } else {
                $value = str_replace(',', '', $value);
            }
        } elseif ($hasComma) {
            $value = str_replace('.', '', $value);
            $value = str_replace(',', '.', $value);
        }

        if (!is_numeric($value)) {
            return null;
        }

        return round((float) $value, 2);
    }

    public static function normalizePricePeriods(array $rawPeriods, float $basePrice, bool $isFree = false): array
    {
        $normalized = [];

        foreach (self::PERIOD_KEYS as $period) {
            if ($period === 'mensal') {
                $normalized[$period] = $isFree ? 0.0 : round($basePrice, 2);
                continue;
            }

            if (!array_key_exists($period, $rawPeriods)) {
                continue;
            }

            $value = self::parseMoneyValue($rawPeriods[$period]);
            if ($value === null) {
                continue;
            }

            $normalized[$period] = round($value, 2);
        }

        if ($isFree) {
            return ['mensal' => 0.0];
        }

        return $normalized;
    }

    public static function normalizePeriodSettings(array $rawSettings, array $pricePeriods, bool $isFree = false): array
    {
        $settings = [];

        foreach (self::PERIOD_KEYS as $period) {
            $enabled = $period === 'mensal';

            if ($period !== 'mensal') {
                $enabled = array_key_exists($period, $pricePeriods) && (float) ($pricePeriods[$period] ?? 0) > 0;
            }

            if (array_key_exists($period, $rawSettings)) {
                $rawValue = $rawSettings[$period];

                if (is_array($rawValue) && array_key_exists('enabled', $rawValue)) {
                    $enabled = self::toBoolean($rawValue['enabled']);
                } else {
                    $enabled = self::toBoolean($rawValue);
                }
            }

            $settings[$period] = ['enabled' => $enabled];
        }

        if ($isFree) {
            foreach (self::PERIOD_KEYS as $period) {
                $settings[$period]['enabled'] = $period === 'mensal';
            }

            return $settings;
        }

        if (!self::hasEnabledPeriod($settings)) {
            $settings[self::firstEnabledPeriodFromPrices($pricePeriods)]['enabled'] = true;
        }

        return $settings;
    }

    public static function ensureEnabledPeriodPrices(array $pricePeriods, array $periodSettings, float $basePrice): array
    {
        $pricePeriods['mensal'] = round($basePrice, 2);

        foreach (self::PERIOD_KEYS as $period) {
            $enabled = (bool) ($periodSettings[$period]['enabled'] ?? false);
            if (!$enabled) {
                continue;
            }

            if (array_key_exists($period, $pricePeriods) && $pricePeriods[$period] !== null) {
                continue;
            }

            $multiplier = self::PERIOD_MULTIPLIERS[$period] ?? 1;
            $pricePeriods[$period] = round($basePrice * $multiplier, 2);
        }

        return $pricePeriods;
    }

    public static function sanitizePeriod(?string $period): string
    {
        $period = strtolower(trim((string) $period));

        return in_array($period, self::PERIOD_KEYS, true) ? $period : 'mensal';
    }

    public static function periodLabels(): array
    {
        return self::PERIOD_LABELS;
    }

    public static function siteFeatureLabels(): array
    {
        return self::FEATURE_LABELS;
    }

    public static function siteFeatureGroups(): array
    {
        return self::FEATURE_GROUPS;
    }

    public static function premiumComparisonRows(): array
    {
        return self::PREMIUM_COMPARISON_ROWS;
    }

    public static function blueprintForPlan(?string $slug, bool $isFree = false): ?array
    {
        if ($isFree) {
            return self::COMMERCIAL_BLUEPRINTS['free'];
        }

        $slug = strtolower(trim((string) $slug));
        if ($slug === '') {
            return null;
        }

        return self::COMMERCIAL_BLUEPRINTS[$slug] ?? null;
    }

    public static function aliasesForFeature(string $feature): array
    {
        $feature = trim($feature);
        if ($feature === '') {
            return [];
        }

        $aliases = [];

        if ($feature === 'admin_panel') {
            $aliases[] = 'admin.panel';
        } elseif ($feature === 'admin.panel') {
            $aliases[] = 'admin_panel';
        }

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

        if ($feature === 'rankings') {
            $aliases[] = 'points_access';
        } elseif ($feature === 'points_access') {
            $aliases[] = 'rankings';
        }

        if (preg_match('/^(courses|events|mentorships)_(create|edit|delete)$/', $feature, $m)) {
            $aliases[] = $m[1] . '.' . $m[2];
        } elseif (preg_match('/^(courses|events|mentorships)\.(create|edit|delete)$/', $feature, $m)) {
            $aliases[] = $m[1] . '_' . $m[2];
        }

        if ($feature === 'events.exhibitors.manage') {
            $aliases[] = 'events_exhibitors_manage';
        } elseif ($feature === 'events_exhibitors_manage') {
            $aliases[] = 'events.exhibitors.manage';
        }

        if ($feature === 'courses_review') {
            $aliases[] = 'courses.edit';
            $aliases[] = 'courses_edit';
        } elseif ($feature === 'mentorships_review') {
            $aliases[] = 'mentorships.edit';
            $aliases[] = 'mentorships_edit';
        }

        if ($feature === 'events_reserve') {
            $aliases[] = 'events';
            $aliases[] = 'events_access';
        }

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

        if (str_starts_with($feature, 'certificates_')) {
            $aliases[] = 'courses.certificates';
        } elseif ($feature === 'courses.certificates') {
            $aliases[] = 'certificates_access';
            $aliases[] = 'certificates_create';
            $aliases[] = 'certificates_generate';
            $aliases[] = 'certificates_delete';
        }

        if ($feature === 'marketplace.buy') {
            $aliases[] = 'marketplace';
        } elseif ($feature === 'marketplace') {
            $aliases[] = 'marketplace.buy';
        }

        return array_values(array_unique(array_filter(
            $aliases,
            static fn ($value) => is_string($value) && trim($value) !== '' && $value !== $feature
        )));
    }

    private static function toBoolean(mixed $value): bool
    {
        if (is_bool($value)) {
            return $value;
        }

        if (is_int($value) || is_float($value)) {
            return (bool) $value;
        }

        $normalized = strtolower(trim((string) $value));

        return in_array($normalized, ['1', 'true', 'on', 'yes'], true);
    }

    private static function hasEnabledPeriod(array $settings): bool
    {
        foreach (self::PERIOD_KEYS as $period) {
            if ((bool) ($settings[$period]['enabled'] ?? false)) {
                return true;
            }
        }

        return false;
    }

    private static function firstEnabledPeriodFromPrices(array $pricePeriods): string
    {
        foreach (self::PERIOD_KEYS as $period) {
            if (!array_key_exists($period, $pricePeriods)) {
                continue;
            }

            if ($period === 'mensal') {
                return $period;
            }

            if ((float) ($pricePeriods[$period] ?? 0) > 0) {
                return $period;
            }
        }

        return 'mensal';
    }
}
