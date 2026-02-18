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
        'mp_plan_id',
        'is_recurring'
    ];

    protected $casts = [
        'is_featured' => 'boolean',
        'highlight_legacy' => 'boolean',
        'highlight' => 'boolean',
        'coupons_enabled' => 'boolean',
        'is_active' => 'boolean',
        'prorata' => 'boolean',
        'is_recurring' => 'boolean',
        'benefits' => 'array',
        'permissions' => 'array',
        'comparison' => 'array',
        'price' => 'decimal:2'
    ];

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
