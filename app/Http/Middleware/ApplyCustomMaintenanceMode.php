<?php
// UTF-8 sem BOM

namespace App\Http\Middleware;

use App\Models\Setting;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class ApplyCustomMaintenanceMode
{
    public function handle(Request $request, Closure $next)
    {
        if ($this->shouldBypass($request)) {
            return $next($request);
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
            return $next($request);
        }

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
            'api/*',
            'manifest.webmanifest',
            'service-worker.js',
            'offline'
        )) {
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
