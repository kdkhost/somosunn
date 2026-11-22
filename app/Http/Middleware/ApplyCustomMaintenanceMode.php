<?php
// UTF-8 sem BOM

namespace App\Http\Middleware;

use App\Models\Setting;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cookie;
use Symfony\Component\HttpFoundation\IpUtils;

class ApplyCustomMaintenanceMode
{
    public const DEVICE_COOKIE_NAME = 'unn_maintenance_device';

    public function handle(Request $request, Closure $next)
    {
        $issuedDeviceCookie = $this->handleBypassTokenQuery($request);

        if ($this->shouldBypass($request)) {
            $response = $next($request);
            if ($issuedDeviceCookie !== null) {
                $response->headers->setCookie($issuedDeviceCookie);
                $response->headers->set('Cache-Control', 'no-store, private, max-age=0');
            }

            return $response;
        }

        $manualEnabled = $this->toBool(Setting::get('maintenance_enabled', '0'));
        $autoEnabled = $this->toBool(Setting::get('maintenance_auto_enabled', '0'));

        $startAt = $this->parseDateTime(Setting::get('maintenance_start_at', ''));
        $endAt = $this->parseDateTime(Setting::get('maintenance_end_at', ''));

        $scheduledActive = false;
        if ($autoEnabled && $startAt) {
            $now = now();
            $afterStart = $now->greaterThanOrEqualTo($startAt);
            $beforeEnd = !$endAt || $now->lessThanOrEqualTo($endAt);
            $scheduledActive = $afterStart && $beforeEnd;
        }

        $isActive = $manualEnabled || $scheduledActive;
        if (!$isActive) {
            $response = $next($request);
            if ($issuedDeviceCookie !== null) {
                $response->headers->setCookie($issuedDeviceCookie);
            }

            return $response;
        }

        // Garante que nenhuma CDN faça cache de paginas em modo de manutencao
        $request->attributes->set('unn.no_edge_cache', true);

        $title = trim((string) Setting::get('maintenance_title', 'Sistema em manutencao'));
        $subtitle = trim((string) Setting::get('maintenance_subtitle', 'Estamos melhorando sua experiencia.'));
        $message = trim((string) Setting::get('maintenance_message', 'Voltamos em instantes. Obrigado pela paciencia.'));
        $buttonLabel = trim((string) Setting::get('maintenance_button_label', 'Ir para a home'));
        $buttonUrl = trim((string) Setting::get('maintenance_button_url', route('home')));
        $contactEmail = trim((string) (Setting::get('maintenance_contact_email') ?: Setting::get('smtp_from_email') ?: Setting::get('company_email', '')));

        if ($title === '') {
            $title = 'Sistema em manutencao';
        }
        if ($subtitle === '') {
            $subtitle = 'Estamos melhorando sua experiencia.';
        }
        if ($message === '') {
            $message = 'Voltamos em instantes. Obrigado pela paciencia.';
        }
        if ($buttonLabel === '') {
            $buttonLabel = 'Ir para a home';
        }
        if ($buttonUrl === '') {
            $buttonUrl = route('home');
        }

        $returnAt = $endAt && $endAt->isFuture() ? $endAt : null;
        $retryAfter = $returnAt ? max(60, $returnAt->timestamp - now()->timestamp) : null;

        $response = response()->view('maintenance.index', [
            'maintenanceTitle' => $title,
            'maintenanceSubtitle' => $subtitle,
            'maintenanceMessage' => $message,
            'maintenanceButtonLabel' => $buttonLabel,
            'maintenanceButtonUrl' => $buttonUrl,
            'maintenanceContactEmail' => $contactEmail,
            'maintenanceReturnAt' => $returnAt,
        ], 503);

        $response->headers->set('Cache-Control', 'no-store, private, max-age=0');
        $response->headers->set('Pragma', 'no-cache');

        if ($retryAfter) {
            $response->headers->set('Retry-After', (string) $retryAfter);
        }

        return $response;
    }

    private function shouldBypass(Request $request): bool
    {
        if ($request->is(
            'admin',
            'admin/*',
            'painel',
            'painel/*',
            'login',
            'logout',
            'register',
            'password/*',
            'auth/*',
            'install',
            'install/*',
            'backend/install',
            'backend/install/*',
            'webhook/*',
            'v1/webhooks/*',
            'api/*',
            'health',
            'up',
            'manifest.webmanifest',
            'service-worker.js',
            'offline'
        )) {
            return true;
        }

        if ($this->hasAllowedIp($request)) {
            $request->attributes->set('unn.no_edge_cache', true);

            return true;
        }

        if ($this->hasValidDeviceBypass($request)) {
            $request->attributes->set('unn.no_edge_cache', true);

            return true;
        }

        if (!auth()->check()) {
            return false;
        }

        $user = auth()->user();
        if (method_exists($user, 'isAdmin') && $user->isAdmin()) {
            return true;
        }

        $role = (string) ($user->role ?? '');
        $level = (string) ($user->level ?? '');

        return $role === 'superadmin' || in_array($level, ['superadmin', 'sucesso'], true);
    }

    /**
     * Valida se o endereco IP do cliente (incluindo CIDR) esta na lista de liberacao
     * gerenciada via painel administrativo.
     */
    private function hasAllowedIp(Request $request): bool
    {
        $rawAllowed = trim((string) Setting::get('maintenance_allowed_ips', ''));
        if ($rawAllowed === '') {
            return false;
        }

        $entries = preg_split('/[\r\n,;]+/', $rawAllowed) ?: [];
        $entries = array_values(array_filter(array_map('trim', $entries)));
        if ($entries === []) {
            return false;
        }

        $candidateIps = array_values(array_unique(array_filter([
            trim((string) $request->header('CF-Connecting-IP', '')),
            trim((string) $request->ip()),
        ])));

        foreach ($candidateIps as $clientIp) {
            if (filter_var($clientIp, FILTER_VALIDATE_IP) === false) {
                continue;
            }

            if (IpUtils::checkIp($clientIp, $entries)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Emite um cookie assinado de dispositivo autorizado quando a URL recebe
     * `?maintenance_bypass_token=...` valido (dispositivos confiaveis fora da rede).
     */
    private function handleBypassTokenQuery(Request $request): ?\Symfony\Component\HttpFoundation\Cookie
    {
        $queryToken = trim((string) $request->query('maintenance_bypass_token', ''));
        if ($queryToken === '') {
            return null;
        }

        $expectedToken = trim((string) Setting::get('maintenance_bypass_token', ''));
        if ($expectedToken === '' || !hash_equals($expectedToken, $queryToken)) {
            return null;
        }

        $request->attributes->set('unn.no_edge_cache', true);
        $request->attributes->set('unn.maintenance_device_verified', true);

        return self::makeDeviceBypassCookie($expectedToken);
    }

    private function hasValidDeviceBypass(Request $request): bool
    {
        if ((bool) $request->attributes->get('unn.maintenance_device_verified', false)) {
            return true;
        }

        $cookieValue = (string) $request->cookies->get(self::DEVICE_COOKIE_NAME, '');
        if ($cookieValue === '') {
            return false;
        }

        $expectedToken = trim((string) Setting::get('maintenance_bypass_token', ''));
        if ($expectedToken === '') {
            return false;
        }

        $expectedSignature = self::signDeviceToken($expectedToken);

        return hash_equals($expectedSignature, $cookieValue);
    }

    public static function signDeviceToken(string $bypassToken): string
    {
        $appKey = (string) config('app.key', 'somosunn-maintenance');

        return hash_hmac('sha256', 'maintenance-device:' . $bypassToken, $appKey);
    }

    public static function makeDeviceBypassCookie(string $bypassToken, int $minutes = 10080): \Symfony\Component\HttpFoundation\Cookie
    {
        return Cookie::make(
            self::DEVICE_COOKIE_NAME,
            self::signDeviceToken($bypassToken),
            $minutes,
            '/',
            null,
            null,
            true,
            false,
            'lax'
        );
    }

    private function toBool($value): bool
    {
        if (is_bool($value)) {
            return $value;
        }

        $normalized = strtolower(trim((string) $value));

        return in_array($normalized, ['1', 'true', 'yes', 'on'], true);
    }

    private function parseDateTime($value): ?Carbon
    {
        $raw = trim((string) $value);
        if ($raw === '') {
            return null;
        }

        try {
            return Carbon::parse($raw);
        } catch (\Throwable $e) {
            return null;
        }
    }
}
