<?php

use App\Support\DomainManager;

if (!function_exists('cdn_asset')) {
    /**
     * Gera a URL para arquivos estaticos e midias, utilizando o subdominio CDN
     * cookieless quando habilitado no painel administrativo.
     */
    function cdn_asset(string $path, ?bool $secure = null): string
    {
        $trimmed = trim($path);
        if ($trimmed === '') {
            return '';
        }

        if (class_exists(DomainManager::class)) {
            return DomainManager::cdnAsset($trimmed, $secure);
        }

        return asset(ltrim($trimmed, '/'), $secure);
    }
}

if (!function_exists('unn_asset')) {
    /**
     * Alias semantico de cdn_asset() com versionamento opcional via filemtime
     * para evitar cache obsoleto no Cloudflare apos deploy.
     */
    function unn_asset(string $path, ?bool $secure = null): string
    {
        $url = cdn_asset($path, $secure);
        if ($url === '' || preg_match('#[?&]v=#', $url)) {
            return $url;
        }

        $relative = ltrim(trim($path), '/');
        $fullPath = public_path($relative);
        if (is_file($fullPath)) {
            $version = @filemtime($fullPath);
            if ($version) {
                $separator = str_contains($url, '?') ? '&' : '?';

                return $url . $separator . 'v=' . $version;
            }
        }

        return $url;
    }
}
