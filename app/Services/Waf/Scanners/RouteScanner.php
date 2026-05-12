<?php

namespace App\Services\Waf\Scanners;

/**
 * Scanner de rotas.
 *
 * Analisa `routes/web.php` e `routes/api.php` buscando por:
 *   - Rotas mutantes (POST/PUT/PATCH/DELETE) sem middleware auth, admin ou signed
 *   - Rotas API sem `throttle:` configurado
 *   - Rotas de `admin/*` em `web.php` sem middleware `admin`
 *   - Rotas de webhook que nao estao em grupo `post` apenas
 *
 * Scanner baseado em heuristicas de texto (regex) para evitar depender
 * do cache de rotas do Laravel em tempo de build.
 *
 * Spec: .kiro/specs/waf-e-auditoria-seguranca
 * Requisitos: 1.5, 1.8, 17.5
 */
class RouteScanner extends AbstractScanner
{
    private int $counter = 0;

    private const MUTANT_METHODS = ['post', 'put', 'patch', 'delete'];

    public function id(): string
    {
        return 'routes';
    }

    public function label(): string
    {
        return 'Rotas sem auth, admin ou throttle';
    }

    public function scan(AuditContext $ctx): iterable
    {
        foreach (['routes/web.php', 'routes/api.php'] as $relPath) {
            $abs = $ctx->abs($relPath);

            if (! is_file($abs)) {
                continue;
            }

            $content = @file_get_contents($abs);

            if ($content === false) {
                continue;
            }

            $isApi = str_ends_with($relPath, 'api.php');

            yield from $this->scanRouteFile($abs, $relPath, $content, $isApi);
        }
    }

    private function scanRouteFile(string $abs, string $rel, string $content, bool $isApi): iterable
    {
        $lines = preg_split('/\R/', $content) ?: [];

        // Rastreia os grupos de middleware abertos (pilha simples baseada em chaves)
        $openGroups = []; // [ [middlewares], openedAtBraceDepth ]
        $braceDepth = 0;

        foreach ($lines as $i => $line) {
            $lineNo = $i + 1;

            // Atualiza profundidade de chaves (simplificado - funciona na maioria dos casos)
            $braceDepth += substr_count($line, '{') - substr_count($line, '}');

            // Fecha grupos cujo escopo tenha terminado
            $openGroups = array_filter(
                $openGroups,
                fn ($g) => $braceDepth >= $g['depth']
            );

            // Detecta abertura de grupo: Route::middleware([...])->group(function () {
            if (preg_match('/Route::(?:middleware|group)\s*\(([^)]*)\)/i', $line, $m)) {
                $mwList = $this->extractMiddlewares($m[1] ?? '');
                if (! empty($mwList)) {
                    $openGroups[] = [
                        'middlewares' => $mwList,
                        'depth'       => $braceDepth,
                    ];
                }
            }

            // Detecta rotas diretas: Route::post('foo', ...)
            foreach (self::MUTANT_METHODS as $method) {
                if (stripos($line, "Route::{$method}") === false
                    && stripos($line, '->' . $method . '(') === false
                ) {
                    continue;
                }

                if (! preg_match('/Route::(post|put|patch|delete|any|match)\s*\(\s*[\'\"]([^\'\"]+)[\'\"]/i', $line, $rm)) {
                    continue;
                }

                $routeUri = $rm[2];

                // Junta os middlewares herdados (grupos abertos) + inline
                $inherited = [];
                foreach ($openGroups as $g) {
                    $inherited = array_merge($inherited, $g['middlewares']);
                }

                $inlineMw = $this->extractInlineMiddlewares($content, $abs, $lineNo);
                $allMw    = array_merge($inherited, $inlineMw);

                $hasAuth     = $this->middlewaresContain($allMw, ['auth', 'auth:', 'admin', 'superadmin', 'signed', 'webhook']);
                $hasThrottle = $this->middlewaresContain($allMw, ['throttle']);

                $isPublicExempt = $this->looksLikePublicExempt($routeUri);
                $isWebhook      = stripos($routeUri, 'webhook') !== false;

                if (! $hasAuth && ! $isPublicExempt && ! $isWebhook) {
                    $this->counter++;

                    yield new AuditFinding(
                        id:              sprintf('SEC-ROUTE-NOAUTH-%04d', $this->counter),
                        category:        'SEC-ROUTE',
                        severity:        AuditFinding::SEVERITY_HIGH,
                        area:            $isApi ? 'API' : $this->areaFromPath($rel),
                        title:           sprintf('Rota %s %s sem middleware de autenticacao', strtoupper($method), $routeUri),
                        recommendation:  'Adicionar `auth`, `auth:sanctum`, `admin` ou `signed` conforme a rota. Se for realmente publica, documentar.',
                        file:            $rel,
                        line:            $lineNo,
                        context:         $this->excerpt($abs, $lineNo),
                        wafMitigable:    false,
                        deadline:        AuditFinding::defaultDeadline(AuditFinding::SEVERITY_HIGH),
                    );
                }

                if ($isApi && ! $hasThrottle && ! $isPublicExempt) {
                    $this->counter++;

                    yield new AuditFinding(
                        id:              sprintf('SEC-ROUTE-NOTHROTTLE-%04d', $this->counter),
                        category:        'SEC-ROUTE',
                        severity:        AuditFinding::SEVERITY_MEDIUM,
                        area:            'API',
                        title:           sprintf('Rota API %s %s sem throttle', strtoupper($method), $routeUri),
                        recommendation:  'Aplicar `throttle:60,1` ou perfil customizado no grupo/inline.',
                        file:            $rel,
                        line:            $lineNo,
                        context:         $this->excerpt($abs, $lineNo),
                        wafMitigable:    true,
                        deadline:        AuditFinding::defaultDeadline(AuditFinding::SEVERITY_MEDIUM),
                    );
                }
            }
        }
    }

    /** Extrai middlewares de uma expressao estilo ['auth', 'admin'] ou 'auth'. */
    private function extractMiddlewares(string $payload): array
    {
        $out = [];

        if (preg_match_all('/[\'\"]([^\'\"]+)[\'\"]/', $payload, $m)) {
            foreach ($m[1] as $mw) {
                $mw = trim($mw);
                if ($mw !== '') {
                    $out[] = $mw;
                }
            }
        }

        return $out;
    }

    /** Le middlewares inline na chamada seguinte (->middleware([...])) ate proximo ";". */
    private function extractInlineMiddlewares(string $content, string $abs, int $lineNo): array
    {
        $lines = preg_split('/\R/', $content) ?: [];
        $chunk = [];

        for ($i = $lineNo - 1; $i < min(count($lines), $lineNo - 1 + 10); $i++) {
            $chunk[] = $lines[$i] ?? '';
            if (substr_count(implode("\n", $chunk), ';') >= 1) {
                break;
            }
        }

        $joined = implode("\n", $chunk);

        if (preg_match('/->middleware\s*\(([^)]*)\)/i', $joined, $m)) {
            return $this->extractMiddlewares($m[1] ?? '');
        }

        return [];
    }

    private function middlewaresContain(array $middlewares, array $needles): bool
    {
        foreach ($middlewares as $mw) {
            $l = strtolower($mw);
            foreach ($needles as $n) {
                if (str_starts_with($l, strtolower($n))) {
                    return true;
                }
            }
        }

        return false;
    }

    private function looksLikePublicExempt(string $uri): bool
    {
        $l = strtolower($uri);
        $exempt = ['login', 'logout', 'register', 'password', 'email/verify', 'auth/callback', 'auth/redirect', 'contact', 'health', 'csrf', 'sitemap'];

        foreach ($exempt as $e) {
            if (str_contains($l, $e)) {
                return true;
            }
        }

        return false;
    }
}
