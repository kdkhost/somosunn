<?php

namespace App\Services;

use App\Models\Page;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class LegalConsentService
{
    private const DOCUMENTS = [
        [
            'slug' => 'termos-de-uso',
            'route' => 'site.termos',
            'title' => 'Termos de Uso',
            'summary' => 'Regras de uso da plataforma, responsabilidades e condicoes aplicaveis ao acesso.',
        ],
        [
            'slug' => 'politica-de-privacidade',
            'route' => 'site.privacidade',
            'title' => 'Politica de Privacidade',
            'summary' => 'Como os dados pessoais sao coletados, tratados, armazenados e protegidos.',
        ],
        [
            'slug' => 'consentimento-lgpd',
            'route' => 'site.lgpd',
            'title' => 'Consentimento LGPD',
            'summary' => 'Direitos do titular e bases de consentimento conforme a legislacao brasileira.',
        ],
    ];

    private ?array $documents = null;

    public function modalData(?User $user): array
    {
        return [
            'requires_consent' => $user instanceof User && !$this->hasAcceptedCurrentVersion($user),
            'version' => $this->currentVersion(),
            'documents' => $this->documents(),
        ];
    }

    public function hasAcceptedCurrentVersion(?User $user): bool
    {
        if (!$user instanceof User) {
            return true;
        }

        $acceptedAt = $user->lgpd_accepted_at;
        $acceptedVersion = trim((string) $user->lgpd_version);

        return $acceptedAt !== null
            && $acceptedVersion !== ''
            && hash_equals($this->currentVersion(), $acceptedVersion);
    }

    public function currentVersion(): string
    {
        $payload = array_map(static function (array $document): array {
            return [
                'slug' => $document['slug'],
                'title' => $document['title'],
                'summary' => $document['summary'],
                'body' => $document['body'],
                'updated_at' => $document['updated_at'],
            ];
        }, $this->documents());

        return hash('sha256', json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?: 'lgpd-v1');
    }

    public function recordAcceptance(User $user, Request $request): void
    {
        $user->forceFill([
            'lgpd_accepted_at' => now(),
            'lgpd_version' => $this->currentVersion(),
            'lgpd_accept_ip' => $request->ip(),
            'lgpd_accept_user_agent' => Str::limit((string) $request->userAgent(), 65535, ''),
        ])->save();
    }

    public function documents(): array
    {
        if ($this->documents !== null) {
            return $this->documents;
        }

        $pages = [];

        if (Page::tableAvailable()) {
            $pages = Page::query()
                ->whereIn('slug', array_column(self::DOCUMENTS, 'slug'))
                ->get(['slug', 'title', 'data', 'updated_at'])
                ->keyBy('slug')
                ->all();
        }

        $this->documents = array_map(static function (array $definition) use ($pages): array {
            $page = $pages[$definition['slug']] ?? null;
            $data = is_array($page?->data) ? $page->data : [];

            $title = trim((string) ($data['hero_title'] ?? $page?->title ?? $definition['title']));
            $summary = trim(strip_tags((string) ($data['hero_subtitle'] ?? $data['seo_description'] ?? $definition['summary'])));
            $body = trim((string) ($data['body_content'] ?? ''));
            $updatedAt = $page?->updated_at instanceof Carbon ? $page->updated_at : null;

            return [
                'slug' => $definition['slug'],
                'title' => $title !== '' ? $title : $definition['title'],
                'summary' => $summary !== '' ? Str::limit($summary, 220) : $definition['summary'],
                'body' => $body,
                'url' => route($definition['route']),
                'updated_at' => $updatedAt?->toIso8601String(),
                'updated_label' => $updatedAt?->format('d/m/Y H:i'),
            ];
        }, self::DOCUMENTS);

        return $this->documents;
    }
}
