<?php

namespace App\Services\Site;

use App\Models\SiteContent;

class SitePageContentService
{
    /**
     * Renderiza HTML armazenado em SiteContent com suporte a placeholders simples.
     *
     * Placeholders: [[PLACEHOLDER]] (substitui via $replacements)
     *
     * Importante:
     * - Retorna HTML para ser exibido com {!! !!}.
     * - $replacements deve conter HTML/strings já seguros (escape quando necessário).
     */
    public function render(string $slug, string $key, string $fallback = '', array $replacements = []): string
    {
        $value = null;

        try {
            $value = SiteContent::getValue($slug, $key);
        } catch (\Throwable $e) {
            $value = null;
        }

        $html = trim((string) $value) !== '' ? (string) $value : $fallback;

        if ($html === '') {
            return '';
        }

        foreach ($replacements as $placeholder => $replacement) {
            $html = str_replace('[[' . $placeholder . ']]', (string) $replacement, $html);
        }

        return $html;
    }
}

