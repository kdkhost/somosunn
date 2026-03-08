<?php

namespace App\Http\Controllers\Auth\Concerns;

use Illuminate\Http\Request;
use Illuminate\Support\Str;

trait SanitizesIntendedRedirect
{
    protected function redirectToSafeIntended(Request $request, string $defaultUrl)
    {
        $intendedUrl = $request->session()->pull('url.intended');

        if ($this->isSafeIntendedUrl($request, $intendedUrl)) {
            return redirect()->to($intendedUrl);
        }

        return redirect()->to($defaultUrl);
    }

    protected function isSafeIntendedUrl(Request $request, mixed $url): bool
    {
        $url = is_string($url) ? trim($url) : '';
        if ($url === '') {
            return false;
        }

        $host = strtolower((string) parse_url($url, PHP_URL_HOST));
        $allowedHosts = array_filter([
            strtolower((string) $request->getHost()),
            strtolower((string) parse_url((string) config('app.url'), PHP_URL_HOST)),
        ]);

        if ($host !== '' && !in_array($host, $allowedHosts, true)) {
            return false;
        }

        $path = '/' . ltrim((string) parse_url($url, PHP_URL_PATH), '/');

        if ($path === '//') {
            $path = '/';
        }

        if (in_array($path, ['/login', '/logout', '/register'], true)) {
            return false;
        }

        $unsafePrefixes = [
            '/painel/dashboard/stats',
            '/painel/admin/dashboard/stats',
            '/admin/dashboard/stats',
            '/api/',
            '/webhook/',
            '/broadcasting/',
            '/auth/redirect/',
            '/auth/callback/',
        ];

        foreach ($unsafePrefixes as $prefix) {
            if (Str::startsWith($path, $prefix)) {
                return false;
            }
        }

        return true;
    }
}
