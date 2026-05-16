<?php

/**
 * ============================================================
 * PROPRIEDADE INTELECTUAL E DIREITOS AUTORAIS
 * ============================================================
 *
 * @autor marcelo-brad rj
 * @contato
 * Tel: 21 981325441
 * WhatsApp: 21 98132-5441
 * Email: contato@kdkhost.com.br
 * Telegram: @MARCELO_BRAD
 * Instagram: @marcelobradrj
 *
 * ============================================================
 *
 * Sistema UNN - AdvancedRateLimitMiddleware
 *
 * Middleware de rate limit avancado, compativel com hospedagem
 * compartilhada (sem Redis). Usa storage baseado em arquivo em
 * `storage/framework/rate-limits/{md5(ip)}.json` para janela
 * deslizante de 60 segundos e tabela `rate_limit_blocks` para
 * persistir bloqueios. Detecta User-Agents suspeitos, suporta
 * whitelist de IPs configuravel e estende o bloqueio em caso
 * de tentativas adicionais.
 *
 * Padrao fail-open: qualquer falha de I/O ou banco apenas registra
 * warning no canal `security` e permite a requisicao continuar.
 *
 * Spec: .kiro/specs/advanced-security-performance
 * Requirements: 5.1, 5.2, 5.3, 5.4, 5.5, 5.6, 5.7
 */

namespace App\Http\Middleware;

use App\Contracts\RateLimiterInterface;
use App\Models\Setting;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class AdvancedRateLimitMiddleware implements RateLimiterInterface
{
    public const STORAGE_DIR = 'framework/rate-limits';
    public const WINDOW_SECONDS = 60;
    private const DEFAULT_THRESHOLD = 100;
    private const DEFAULT_BLOCK_DURATION = 15;
    private const DEFAULT_BLOCK_INCREMENT = 5;

    /**
     * Padroes de User-Agent considerados suspeitos por padrao quando
     * a setting `rate_limit_ua_patterns` nao esta configurada.
     *
     * @var array<int, string>
     */
    public const DEFAULT_UA_PATTERNS = [
        'sqlmap', 'nikto', 'acunetix', 'masscan', 'nmap',
        'python-requests', 'gobuster', 'dirbuster', 'wpscan', 'nuclei',
    ];

    /**
     * Handler principal do middleware.
     *
     * Fluxo:
     *  1. Whitelist -> permite sem checagens
     *  2. UA suspeito -> 403 + log security
     *  3. IP bloqueado -> 429 + Retry-After
     *  4. Registra request, conta janela; se excedeu threshold -> bloqueia + 429
     *  5. Em caso de erro inesperado -> fail-open
     */
    public function handle(Request $request, Closure $next): Response
    {
        $ip = (string) ($request->ip() ?? '');
        $userAgent = (string) $request->header('User-Agent', '');

        try {
            if ($ip !== '' && $this->isWhitelisted($ip)) {
                return $next($request);
            }

            if ($userAgent !== '' && $this->isSuspiciousAgent($userAgent)) {
                $this->logSecurity('rate_limit.suspicious_ua', [
                    'ip' => $ip,
                    'user_agent' => $userAgent,
                    'path' => $request->path(),
                ]);

                // Persiste o bloqueio em rate_limit_blocks para integracao
                // com o WAF e para que requisicoes subsequentes do mesmo
                // IP sejam barradas via isBlocked() (Requirement 5.3).
                if ($ip !== '') {
                    $this->blockIp($ip, $this->getBlockDuration(), 'suspicious_user_agent');
                }

                return $this->forbiddenResponse($request);
            }

            if ($ip !== '' && $this->isBlocked($ip)) {
                // Requirement 5.6 / Property 6: cada tentativa adicional
                // durante o periodo de bloqueio estende o `blocked_until`
                // pelo incremento configurado, somando ao final atual.
                $this->blockIp($ip, $this->getBlockIncrement(), 'rate_limit_exceeded');

                $retryAfter = $this->retryAfterSeconds($ip);

                return $this->tooManyRequestsResponse($request, $retryAfter);
            }

            if ($ip !== '') {
                $this->recordRequest($ip);

                $count = $this->getRequestCount($ip, self::WINDOW_SECONDS);
                $threshold = $this->getThreshold();

                if ($count > $threshold) {
                    $duration = $this->getBlockDuration();
                    $this->blockIp($ip, $duration, 'rate_limit_exceeded');

                    $this->logSecurity('rate_limit.threshold_exceeded', [
                        'ip' => $ip,
                        'count' => $count,
                        'threshold' => $threshold,
                        'block_minutes' => $duration,
                        'user_agent' => $userAgent,
                        'path' => $request->path(),
                    ]);

                    return $this->tooManyRequestsResponse($request, $duration * 60);
                }
            }
        } catch (\Throwable $e) {
            // Fail-open: nao quebrar o site por causa do rate limit.
            Log::channel($this->channel())->warning('AdvancedRateLimitMiddleware fail-open', [
                'exception' => $e->getMessage(),
                'ip' => $ip,
            ]);
        }

        return $next($request);
    }

    /* -------------------------------------------------------------- */
    /* RateLimiterInterface                                            */
    /* -------------------------------------------------------------- */

    public function isBlocked(string $ip): bool
    {
        if ($ip === '') {
            return false;
        }

        try {
            return DB::table('rate_limit_blocks')
                ->where('ip_address', $ip)
                ->where('blocked_until', '>', Carbon::now())
                ->exists();
        } catch (\Throwable $e) {
            Log::channel($this->channel())->warning('isBlocked falhou', [
                'exception' => $e->getMessage(),
                'ip' => $ip,
            ]);

            return false;
        }
    }

    public function isSuspiciousAgent(string $userAgent): bool
    {
        $ua = strtolower(trim($userAgent));
        if ($ua === '') {
            return false;
        }

        foreach ($this->getUaPatterns() as $pattern) {
            $needle = strtolower(trim((string) $pattern));
            if ($needle === '') {
                continue;
            }
            if (str_contains($ua, $needle)) {
                return true;
            }
        }

        return false;
    }

    public function recordRequest(string $ip): void
    {
        if ($ip === '') {
            return;
        }

        try {
            $path = $this->storagePath($ip);
            $this->ensureDir(dirname($path));

            $now = Carbon::now()->getTimestamp();
            $cutoff = $now - self::WINDOW_SECONDS;

            $timestamps = $this->readTimestamps($path);
            $timestamps[] = $now;

            // Mantem apenas timestamps dentro da janela.
            $timestamps = array_values(array_filter(
                $timestamps,
                static fn ($ts) => is_int($ts) && $ts >= $cutoff
            ));

            $this->writeTimestamps($path, $timestamps);
        } catch (\Throwable $e) {
            // Fail-open: erro de I/O nao deve bloquear request.
            Log::channel($this->channel())->warning('recordRequest falhou', [
                'exception' => $e->getMessage(),
                'ip' => $ip,
            ]);
        }
    }

    public function blockIp(string $ip, int $durationMinutes, string $reason = 'rate_limit_exceeded'): void
    {
        if ($ip === '' || $durationMinutes <= 0) {
            return;
        }

        try {
            $now = Carbon::now();
            $existing = DB::table('rate_limit_blocks')
                ->where('ip_address', $ip)
                ->where('blocked_until', '>', $now)
                ->orderByDesc('id')
                ->first();

            if ($existing !== null) {
                // Requirement 5.6: estende o bloqueio existente em
                // $durationMinutes minutos a partir do `blocked_until`
                // atual, somando aos minutos restantes (Property 6).
                $currentUntil = Carbon::parse($existing->blocked_until);
                $base = $currentUntil->greaterThan($now) ? $currentUntil : $now;

                DB::table('rate_limit_blocks')
                    ->where('id', $existing->id)
                    ->update([
                        'blocked_until' => $base->copy()->addMinutes($durationMinutes),
                        'attempts' => (int) $existing->attempts + 1,
                        'reason' => $reason,
                    ]);

                return;
            }

            DB::table('rate_limit_blocks')->insert([
                'ip_address' => $ip,
                'reason' => $reason,
                'blocked_until' => $now->copy()->addMinutes($durationMinutes),
                'attempts' => 1,
                'created_at' => $now,
            ]);
        } catch (\Throwable $e) {
            Log::channel($this->channel())->warning('blockIp falhou', [
                'exception' => $e->getMessage(),
                'ip' => $ip,
                'duration' => $durationMinutes,
                'reason' => $reason,
            ]);
        }
    }

    public function isWhitelisted(string $ip): bool
    {
        if ($ip === '') {
            return false;
        }

        $whitelist = $this->getWhitelist();
        if ($whitelist === []) {
            return false;
        }

        return in_array($ip, $whitelist, true);
    }

    public function getRequestCount(string $ip, int $windowSeconds = 60): int
    {
        if ($ip === '') {
            return 0;
        }

        try {
            $path = $this->storagePath($ip);
            $timestamps = $this->readTimestamps($path);

            $now = Carbon::now()->getTimestamp();
            $cutoff = $now - max(1, $windowSeconds);

            $count = 0;
            foreach ($timestamps as $ts) {
                if (is_int($ts) && $ts >= $cutoff) {
                    $count++;
                }
            }

            return $count;
        } catch (\Throwable $e) {
            Log::channel($this->channel())->warning('getRequestCount falhou', [
                'exception' => $e->getMessage(),
                'ip' => $ip,
            ]);

            return 0;
        }
    }

    /* -------------------------------------------------------------- */
    /* Helpers internos                                                */
    /* -------------------------------------------------------------- */

    private function storagePath(string $ip): string
    {
        $hash = md5($ip);
        $base = function_exists('storage_path')
            ? storage_path(self::STORAGE_DIR)
            : sys_get_temp_dir() . DIRECTORY_SEPARATOR . self::STORAGE_DIR;

        return rtrim($base, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $hash . '.json';
    }

    private function ensureDir(string $dir): void
    {
        if ($dir === '' || is_dir($dir)) {
            return;
        }
        @mkdir($dir, 0775, true);
    }

    /**
     * @return array<int, int>
     */
    private function readTimestamps(string $path): array
    {
        if (! is_file($path)) {
            return [];
        }

        $raw = @file_get_contents($path);
        if ($raw === false || $raw === '') {
            return [];
        }

        $decoded = json_decode($raw, true);
        if (! is_array($decoded) || ! isset($decoded['timestamps']) || ! is_array($decoded['timestamps'])) {
            return [];
        }

        $result = [];
        foreach ($decoded['timestamps'] as $ts) {
            if (is_int($ts)) {
                $result[] = $ts;
            } elseif (is_numeric($ts)) {
                $result[] = (int) $ts;
            }
        }

        return $result;
    }

    /**
     * @param array<int, int> $timestamps
     */
    private function writeTimestamps(string $path, array $timestamps): void
    {
        $payload = json_encode(
            ['timestamps' => array_values($timestamps)],
            JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
        );

        if ($payload === false) {
            return;
        }

        @file_put_contents($path, $payload, LOCK_EX);
    }

    /* -------------------------------------------------------------- */
    /* HTTP responses                                                  */
    /* -------------------------------------------------------------- */

    /**
     * Resposta 403 para User-Agent suspeito. Negocia formato pelo
     * cabecalho Accept: JSON quando o cliente espera JSON, HTML caso
     * contrario.
     */
    private function forbiddenResponse(Request $request): Response
    {
        if ($this->wantsJson($request)) {
            return response()->json([
                'error' => 'forbidden',
                'message' => 'Acesso bloqueado pelo sistema de seguranca.',
            ], 403);
        }

        return response($this->forbiddenHtml(), 403, [
            'Content-Type' => 'text/html; charset=utf-8',
        ]);
    }

    /**
     * Resposta 429 para rate limit excedido com cabecalho Retry-After.
     */
    private function tooManyRequestsResponse(Request $request, int $retryAfterSeconds): Response
    {
        $retryAfterSeconds = max(1, $retryAfterSeconds);

        if ($this->wantsJson($request)) {
            return response()->json([
                'error' => 'too_many_requests',
                'message' => 'Limite de requisicoes excedido. Tente novamente mais tarde.',
                'retry_after' => $retryAfterSeconds,
            ], 429, ['Retry-After' => (string) $retryAfterSeconds]);
        }

        return response($this->tooManyRequestsHtml($retryAfterSeconds), 429, [
            'Content-Type' => 'text/html; charset=utf-8',
            'Retry-After' => (string) $retryAfterSeconds,
        ]);
    }

    private function wantsJson(Request $request): bool
    {
        try {
            if ($request->expectsJson() || $request->wantsJson()) {
                return true;
            }
        } catch (\Throwable $e) {
            // segue para fallbacks
        }

        $accept = strtolower((string) $request->header('Accept', ''));
        if ($accept !== '' && (str_contains($accept, 'application/json') || str_contains($accept, '+json'))) {
            return true;
        }

        $path = '/' . ltrim($request->path(), '/');

        return str_starts_with($path, '/api/');
    }

    private function forbiddenHtml(): string
    {
        return <<<'HTML'
<!doctype html>
<html lang="pt-BR"><head>
<meta charset="utf-8"><title>Acesso Bloqueado</title>
<meta name="robots" content="noindex">
<style>body{font-family:sans-serif;display:flex;align-items:center;justify-content:center;min-height:100vh;background:#0f172a;color:#e2e8f0;text-align:center;}</style>
</head><body>
<div><h1>Acesso Bloqueado</h1><p>Sua requisicao foi identificada como suspeita pelo sistema de seguranca.</p></div>
</body></html>
HTML;
    }

    private function tooManyRequestsHtml(int $retryAfterSeconds): string
    {
        $retry = (int) $retryAfterSeconds;

        return <<<HTML
<!doctype html>
<html lang="pt-BR"><head>
<meta charset="utf-8"><title>Muitas Requisicoes</title>
<meta name="robots" content="noindex">
<style>body{font-family:sans-serif;display:flex;align-items:center;justify-content:center;min-height:100vh;background:#0f172a;color:#e2e8f0;text-align:center;}</style>
</head><body>
<div><h1>Muitas Requisicoes</h1><p>Limite de requisicoes excedido. Tente novamente em aproximadamente {$retry} segundos.</p></div>
</body></html>
HTML;
    }

    private function retryAfterSeconds(string $ip): int
    {
        try {
            $row = DB::table('rate_limit_blocks')
                ->where('ip_address', $ip)
                ->where('blocked_until', '>', Carbon::now())
                ->orderByDesc('blocked_until')
                ->first();

            if ($row === null) {
                return $this->getBlockDuration() * 60;
            }

            $until = Carbon::parse($row->blocked_until);
            $diff = $until->getTimestamp() - Carbon::now()->getTimestamp();

            return $diff > 0 ? $diff : $this->getBlockDuration() * 60;
        } catch (\Throwable $e) {
            return $this->getBlockDuration() * 60;
        }
    }

    /* -------------------------------------------------------------- */
    /* Settings                                                        */
    /* -------------------------------------------------------------- */

    private function getThreshold(): int
    {
        $value = Setting::get('rate_limit_threshold', self::DEFAULT_THRESHOLD);
        $value = is_numeric($value) ? (int) $value : self::DEFAULT_THRESHOLD;

        return max(1, $value);
    }

    private function getBlockDuration(): int
    {
        $value = Setting::get('rate_limit_block_duration', self::DEFAULT_BLOCK_DURATION);
        $value = is_numeric($value) ? (int) $value : self::DEFAULT_BLOCK_DURATION;

        return max(1, $value);
    }

    /**
     * Incremento de bloqueio em minutos por tentativas adicionais.
     * (Atualmente acionado via blockIp() quando o IP ja possui bloqueio
     * ativo, somando $durationMinutes ao instante atual.)
     */
    private function getBlockIncrement(): int
    {
        $value = Setting::get('rate_limit_block_increment', self::DEFAULT_BLOCK_INCREMENT);
        $value = is_numeric($value) ? (int) $value : self::DEFAULT_BLOCK_INCREMENT;

        return max(1, $value);
    }

    /**
     * @return array<int, string>
     */
    private function getWhitelist(): array
    {
        $raw = Setting::get('rate_limit_whitelist', null);

        return $this->decodeStringList($raw);
    }

    /**
     * @return array<int, string>
     */
    private function getUaPatterns(): array
    {
        $raw = Setting::get('rate_limit_ua_patterns', null);
        $list = $this->decodeStringList($raw);

        if ($list === []) {
            return self::DEFAULT_UA_PATTERNS;
        }

        return $list;
    }

    /**
     * Decodifica uma setting que pode vir como array ja desserializado
     * ou como string JSON. Retorna apenas strings nao vazias.
     *
     * @param mixed $raw
     * @return array<int, string>
     */
    private function decodeStringList($raw): array
    {
        if (is_array($raw)) {
            $list = $raw;
        } elseif (is_string($raw) && $raw !== '') {
            $decoded = json_decode($raw, true);
            $list = is_array($decoded) ? $decoded : [];
        } else {
            return [];
        }

        $result = [];
        foreach ($list as $item) {
            if (! is_scalar($item)) {
                continue;
            }
            $value = trim((string) $item);
            if ($value !== '') {
                $result[] = $value;
            }
        }

        return $result;
    }

    private function logSecurity(string $event, array $context): void
    {
        Log::channel($this->channel())->warning($event, $context);
    }

    private function channel(): string
    {
        $channels = (array) config('logging.channels', []);

        return array_key_exists('security', $channels) ? 'security' : 'stack';
    }
}
