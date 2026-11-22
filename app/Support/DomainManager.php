<?php

namespace App\Support;

use App\Models\Setting;
use Illuminate\Http\Request;

class DomainManager
{
    /**
     * Resolve o esquema HTTP/HTTPS atual para montagem de URLs absolutas.
     */
    public static function scheme(?Request $request = null): string
    {
        if ($request) {
            return $request->getScheme() ?: 'https';
        }

        if (app()->bound('request')) {
            $current = request();
            if ($current instanceof Request) {
                return $current->getScheme() ?: 'https';
            }
        }

        $appUrl = (string) config('app.url', '');
        if (str_starts_with(strtolower($appUrl), 'http://')) {
            return 'http';
        }

        return 'https';
    }

    /**
     * Extrai e normaliza apenas o hostname sem porta e em minusculas.
     */
    public static function normalizeHost(?string $host): string
    {
        $raw = strtolower(trim((string) $host));
        if ($raw === '') {
            return '';
        }

        if (str_contains($raw, '://')) {
            $parsed = parse_url($raw, PHP_URL_HOST);
            $raw = strtolower(trim((string) $parsed));
        }

        $raw = explode('/', $raw)[0];
        $raw = preg_replace('/:\d+$/', '', $raw) ?: $raw;

        return trim($raw, '.');
    }

    /**
     * Verifica se o host atual e local, IP bruto ou ambiente de testes.
     */
    public static function isLocalHost(?string $host = null): bool
    {
        if (app()->runningUnitTests() || app()->environment('testing')) {
            return true;
        }

        if ($host === null && app()->bound('request')) {
            $host = request()->getHost();
        }

        $normalized = self::normalizeHost($host);
        if ($normalized === '') {
            return true;
        }

        if (in_array($normalized, ['localhost', '127.0.0.1', '::1', '0.0.0.0'], true)) {
            return true;
        }

        if (filter_var($normalized, FILTER_VALIDATE_IP) !== false) {
            return true;
        }

        return str_ends_with($normalized, '.localhost')
            || str_ends_with($normalized, '.local')
            || str_ends_with($normalized, '.test');
    }

    public static function mainDomain(): string
    {
        $value = (string) self::setting('domain_main', (string) config('domains.main', 'somosunn.com.br'));
        $host = self::normalizeHost($value);

        return $host !== '' ? $host : 'somosunn.com.br';
    }

    public static function appDomain(): string
    {
        $value = (string) self::setting('domain_app', (string) config('domains.app', 'app.' . self::mainDomain()));
        $host = self::normalizeHost($value);

        return $host !== '' ? $host : 'app.' . self::mainDomain();
    }

    public static function apiDomain(): string
    {
        $value = (string) self::setting('domain_api', (string) config('domains.api', 'api.' . self::mainDomain()));
        $host = self::normalizeHost($value);

        return $host !== '' ? $host : 'api.' . self::mainDomain();
    }

    public static function cdnDomain(): string
    {
        $value = (string) self::setting('domain_cdn', (string) config('domains.cdn', 'cdn.' . self::mainDomain()));
        $host = self::normalizeHost($value);

        return $host !== '' ? $host : 'cdn.' . self::mainDomain();
    }

    /**
     * Indica se o isolamento rigoroso por subdominio esta habilitado.
     */
    public static function isRoutingEnabled(?string $currentHost = null): bool
    {
        if (app()->runningUnitTests() || app()->environment('testing')) {
            return false;
        }

        $enabled = self::toBool(self::setting('domain_routing_enabled', config('domains.routing_enabled', false)));
        if (!$enabled) {
            return false;
        }

        $host = self::normalizeHost($currentHost ?: (app()->bound('request') ? request()->getHost() : ''));
        if ($host === '' || self::isLocalHost($host)) {
            return false;
        }

        return true;
    }

    /**
     * Calcula o dominio de cookie compartilhado entre o dominio principal e subdominios.
     */
    public static function sessionCookieDomain(?string $currentHost = null): ?string
    {
        if (app()->runningUnitTests() || app()->environment('testing')) {
            return null;
        }

        $host = self::normalizeHost($currentHost ?: (app()->bound('request') ? request()->getHost() : ''));
        if ($host === '' || self::isLocalHost($host)) {
            return null;
        }

        $base = preg_replace('/^www\./i', '', self::mainDomain()) ?: self::mainDomain();
        if ($base === '') {
            return null;
        }

        if ($host === $base || str_ends_with($host, '.' . $base)) {
            return '.' . $base;
        }

        return null;
    }

    public static function isCdnEnabled(): bool
    {
        if (app()->runningUnitTests() || app()->environment('testing')) {
            return false;
        }

        $enabled = self::toBool(self::setting('cdn_enabled', config('domains.cdn_enabled', false)));
        if (!$enabled) {
            return false;
        }

        if (app()->bound('request') && self::isLocalHost(request()->getHost())) {
            return false;
        }

        return self::cdnBaseUrl() !== '';
    }

    public static function cdnBaseUrl(?Request $request = null): string
    {
        $configuredUrl = trim((string) self::setting('cdn_url', (string) config('domains.cdn_url', '')));
        if ($configuredUrl !== '') {
            return rtrim($configuredUrl, '/');
        }

        $cdnDomain = self::cdnDomain();
        if ($cdnDomain === '') {
            return '';
        }

        return self::scheme($request) . '://' . $cdnDomain;
    }

    /**
     * Retorna a URL de asset servida pelo subdominio cookieless de CDN quando ativo.
     */
    public static function cdnAsset(string $path, ?bool $secure = null): string
    {
        $trimmed = trim($path);
        if ($trimmed === '') {
            return '';
        }

        if (preg_match('#^(?:https?:)?//#i', $trimmed) || str_starts_with($trimmed, 'data:') || str_starts_with($trimmed, 'blob:')) {
            return $trimmed;
        }

        $normalized = ltrim($trimmed, '/');
        if (self::isCdnEnabled()) {
            return self::cdnBaseUrl() . '/' . $normalized;
        }

        return asset($normalized, $secure);
    }

    /**
     * Determina se o cache de borda para paginas publicas de visitantes anonimos esta ativo.
     */
    public static function isPublicCacheEnabled(): bool
    {
        return self::toBool(self::setting('public_page_cache_enabled', config('domains.public_page_cache_enabled', true)));
    }

    public static function appUrl(string $path = '/', ?Request $request = null): string
    {
        $normalized = '/' . ltrim($path, '/');

        if (!self::isRoutingEnabled($request?->getHost())) {
            return url($normalized);
        }

        return self::scheme($request) . '://' . self::appDomain() . $normalized;
    }

    public static function mainUrl(string $path = '/', ?Request $request = null): string
    {
        $normalized = '/' . ltrim($path, '/');

        if (!self::isRoutingEnabled($request?->getHost())) {
            return url($normalized);
        }

        return self::scheme($request) . '://' . self::mainDomain() . $normalized;
    }

    public static function apiUrl(string $path = '/', ?Request $request = null): string
    {
        $normalized = '/' . ltrim($path, '/');

        if (!self::isRoutingEnabled($request?->getHost())) {
            return url($normalized);
        }

        return self::scheme($request) . '://' . self::apiDomain() . $normalized;
    }

    private static function setting(string $key, mixed $default = null): mixed
    {
        try {
            $value = Setting::get($key);
            if ($value !== null && trim((string) $value) !== '') {
                return $value;
            }
        } catch (\Throwable) {
            // Ignora falhas de banco durante boot ou comandos console sem conexao
        }

        return $default;
    }

    private static function toBool(mixed $value): bool
    {
        if (is_bool($value)) {
            return $value;
        }

        return in_array(strtolower(trim((string) $value)), ['1', 'true', 'yes', 'on'], true);
    }
}
