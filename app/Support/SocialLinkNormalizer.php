<?php

namespace App\Support;

class SocialLinkNormalizer
{
    /**
     * Normaliza qualquer formato de entrada de rede social ou site
     * para uma URL HTTPS absoluta, navegável e segura.
     *
     * Suporta: links completos, URLs sem protocolo, handles com @,
     * caminhos relativos (/in/usuario, /c/canal, /usuario) e nomes simples.
     */
    public static function normalize(string $network, ?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = trim($value);
        if ($value === '' || preg_match('/^\s*javascript\s*:/i', $value)) {
            return null;
        }

        $network = strtolower(trim($network));

        // Se já tiver protocolo http:// ou https://
        if (preg_match('/^https?:\/\//i', $value)) {
            // Converter http para https para redes conhecidas
            if (in_array($network, ['instagram', 'facebook', 'linkedin', 'twitter', 'x', 'youtube'])) {
                $value = preg_replace('/^http:\/\//i', 'https://', $value);
            }
            return $value;
        }

        // Protocol-relative //exemplo.com
        if (str_starts_with($value, '//')) {
            return 'https:' . $value;
        }

        return match ($network) {
            'instagram' => self::normalizeInstagram($value),
            'facebook'  => self::normalizeFacebook($value),
            'linkedin'  => self::normalizeLinkedin($value),
            'twitter', 'x' => self::normalizeTwitter($value),
            'youtube'   => self::normalizeYoutube($value),
            'website', 'site' => self::normalizeWebsite($value),
            default     => (str_starts_with($value, 'http') ? $value : 'https://' . ltrim($value, '/')),
        };
    }

    /**
     * Normaliza links do Instagram
     * Ex.: @rataplam.loja, /rataplam.loja, rataplam.loja, instagram.com/rataplam.loja
     */
    public static function normalizeInstagram(string $val): string
    {
        $val = trim($val);
        if (preg_match('/^(?:www\.)?instagram\.com\/(.+)$/i', $val, $m)) {
            return 'https://instagram.com/' . ltrim($m[1], '/');
        }

        $clean = ltrim($val, '@/ ');
        return 'https://instagram.com/' . $clean;
    }

    /**
     * Normaliza links do Facebook
     * Ex.: /rataplam.loja, rataplam.loja, facebook.com/rataplam.loja
     */
    public static function normalizeFacebook(string $val): string
    {
        $val = trim($val);
        if (preg_match('/^(?:www\.)?(?:facebook\.com|fb\.com)\/(.+)$/i', $val, $m)) {
            return 'https://facebook.com/' . ltrim($m[1], '/');
        }

        $clean = ltrim($val, '@/ ');
        if (str_contains($clean, ' ')) {
            return 'https://facebook.com/search/top/?q=' . urlencode($clean);
        }

        return 'https://facebook.com/' . $clean;
    }

    /**
     * Normaliza links do LinkedIn
     * Ex.: /in/seuusuario, in/seuusuario, /company/empresa, seuusuario, linkedin.com/in/...
     */
    public static function normalizeLinkedin(string $val): string
    {
        $val = trim($val);
        if (preg_match('/^(?:www\.)?linkedin\.com\/(.+)$/i', $val, $m)) {
            return 'https://linkedin.com/' . ltrim($m[1], '/');
        }

        $clean = ltrim($val, '@/ ');
        if (str_starts_with($clean, 'in/') || str_starts_with($clean, 'company/')) {
            return 'https://linkedin.com/' . $clean;
        }

        return 'https://linkedin.com/in/' . $clean;
    }

    /**
     * Normaliza links do Twitter / X
     * Ex.: @seuusuario, /seuusuario, seuusuario, twitter.com/..., x.com/...
     */
    public static function normalizeTwitter(string $val): string
    {
        $val = trim($val);
        if (preg_match('/^(?:www\.)?(?:twitter\.com|x\.com)\/(.+)$/i', $val, $m)) {
            return 'https://x.com/' . ltrim($m[1], '/');
        }

        $clean = ltrim($val, '@/ ');
        return 'https://x.com/' . $clean;
    }

    /**
     * Normaliza links do YouTube
     * Ex.: /c/seucanal, c/seucanal, /channel/..., @seucanal, seucanal, youtube.com/...
     */
    public static function normalizeYoutube(string $val): string
    {
        $val = trim($val);
        if (preg_match('/^(?:www\.)?(?:youtube\.com|youtu\.be)\/(.+)$/i', $val, $m)) {
            return 'https://youtube.com/' . ltrim($m[1], '/');
        }

        $clean = ltrim($val, '/ ');
        if (str_starts_with($clean, 'c/') || str_starts_with($clean, 'channel/') || str_starts_with($clean, 'user/')) {
            return 'https://youtube.com/' . $clean;
        }

        if (str_starts_with($clean, '@')) {
            return 'https://youtube.com/' . $clean;
        }

        return 'https://youtube.com/@' . $clean;
    }

    /**
     * Normaliza links de Website
     * Ex.: https://rataplam.com.br, www.rataplam.com.br, rataplam.com.br
     */
    public static function normalizeWebsite(string $val): string
    {
        $clean = ltrim($val, '/ ');
        return 'https://' . $clean;
    }
}
