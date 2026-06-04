<?php

namespace App\Services\Waf;

use App\Models\Waf\WafRule;
use App\Services\Waf\Matchers\Contracts\RuleMatcher;
use App\Services\Waf\Matchers\FunctionRuleMatcher;
use App\Services\Waf\Matchers\ListRuleMatcher;
use App\Services\Waf\Matchers\NumericRuleMatcher;
use App\Services\Waf\Matchers\RegexRuleMatcher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * Fachada principal do WAF - invocada pelo WafMiddleware.
 *
 * Precedencia (exatamente conforme design):
 *   1. WAF desabilitado ou rota isenta           -> allowed
 *   2. IP_Blocklist ativo                        -> blocked (403)
 *   3. IP_Allowlist                              -> rebaixa blocked/challenged a monitored
 *   4. Rate limit excedido                       -> blocked (429)
 *   5. Regras => Risk_Score => classificacao
 *   6. Modo detection-only rebaixa para monitored
 *
 * Spec: .kiro/specs/waf-e-auditoria-seguranca
 * Requisitos: 9.1 ate 9.12, 22.3
 */
final class WafEngine
{
    public function __construct(
        private readonly WafRuleRepository    $rules,
        private readonly IpListService        $ipLists,
        private readonly RateLimitStore       $rateLimits,
        private readonly RiskScoreCalculator  $scorer,
        private readonly WafEventLogger       $logger,
        private readonly SensitiveDataMasker  $masker,
        private readonly ChallengeManager     $challenges,
        private readonly GeoIpResolver        $geo,
        private readonly AsnResolver          $asn,
        private readonly WafSettings          $settings,
    ) {}

    /**
     * Factory conveniente quando nao se usa o container.
     */
    public static function make(?WafSettings $settings = null): self
    {
        $settings = $settings ?? WafSettings::load();
        $masker   = SensitiveDataMasker::fromConfig();

        return new self(
            rules:      new WafRuleRepository(),
            ipLists:    new IpListService(),
            rateLimits: new RateLimitStore(),
            scorer:     new RiskScoreCalculator(),
            logger:     new WafEventLogger($masker, $settings),
            masker:     $masker,
            challenges: new ChallengeManager(),
            geo:        new GeoIpResolver(),
            asn:        new AsnResolver(),
            settings:   $settings,
        );
    }

    /**
     * Inspeciona a requisicao e retorna a decisao.
     */
    public function inspect(Request $request): WafDecision
    {
        try {
            // 1. WAF desabilitado
            if (! $this->settings->enabled) {
                return WafDecision::allowed('waf_disabled');
            }

            // 1b. Rota isenta
            $path = '/' . ltrim($request->path(), '/');
            foreach ($this->settings->exemptRoutes as $pattern) {
                if (@preg_match($pattern, $path) === 1) {
                    return WafDecision::allowed('exempt_route');
                }
            }

            // 1c. Rotas admin isentas para superadmin/admin autenticado
            if (str_starts_with($path, '/admin/') || str_starts_with($path, '/painel/admin/') || str_starts_with($path, '/admin')) {
                $user = $request->user();
                if ($user && method_exists($user, 'isAdmin') && $user->isAdmin()) {
                    return WafDecision::allowed('admin_exempt');
                }
            }

            // 1d. Qualquer requisição de superadmin ou admin nunca é bloqueada pelo WAF
            $authUser = $request->user();
            if ($authUser && method_exists($authUser, 'isAdmin') && $authUser->isAdmin()) {
                return WafDecision::allowed('authenticated_admin_exempt');
            }

            $ctx = WafContext::fromRequest($request);

            // Enriquecimento opcional
            $country = $this->geo->resolve($ctx->ip);
            $asn     = $this->asn->resolve($ctx->ip);
            if ($country || $asn) {
                $ctx = $ctx->withGeo($country, $asn);
            }

            // 2. IP_Blocklist (precedencia maxima)
            $blocked = $this->ipLists->isBlocked($ctx->ip);
            if ($blocked !== null) {
                $decision = WafDecision::blocked(
                    status:    403,
                    riskScore: 100,
                    rules:     [],
                    reason:    'ip_blocklist:' . ($blocked->source ?? 'manual'),
                );
                $eventId = $this->logger->log($ctx, $decision);
                return $eventId ? $decision->withEventId($eventId) : $decision;
            }

            // 3. IP_Allowlist - setamos flag para rebaixamento posterior
            $allowed = $this->ipLists->isAllowed($ctx->ip);

            // 4. Rate limit por escopo
            $rateResult = $this->checkRateLimit($ctx);
            if ($rateResult !== null && ! $rateResult['allowed']) {
                if ($allowed !== null) {
                    $decision = WafDecision::monitored(
                        riskScore: 50,
                        rules:     [],
                        reason:    'rate_limit_allowlisted',
                        originalDecision: WafDecision::BLOCKED,
                    );
                } else {
                    $decision = WafDecision::blocked(
                        status:    429,
                        riskScore: 50,
                        rules:     [],
                        reason:    'rate_limit:' . $rateResult['scope'],
                    );
                }

                $eventId = $this->logger->log($ctx, $decision);
                return $eventId ? $decision->withEventId($eventId) : $decision;
            }

            // 5. Avalia regras
            $matches = $this->matchRules($ctx);
            $score   = $this->scorer->calculate($matches);
            $class   = $this->scorer->classify($score, $this->settings);

            // 6. Modo detection-only rebaixa blocked/challenged a monitored
            $original = null;
            if ($this->settings->isDetectionOnly() && in_array($class, [WafDecision::BLOCKED, WafDecision::CHALLENGED], true)) {
                $original = $class;
                $class    = WafDecision::MONITORED;
            }

            // Allowlist rebaixa blocked/challenged a monitored (regras podem marcar
            // respect_allowlist=false em evolucao futura para bypassar)
            if ($allowed !== null && in_array($class, [WafDecision::BLOCKED, WafDecision::CHALLENGED], true)) {
                $original = $original ?? $class;
                $class    = WafDecision::MONITORED;
            }

            $decision = match ($class) {
                WafDecision::BLOCKED    => WafDecision::blocked(403, $score, $matches, $this->describeReasons($matches)),
                WafDecision::CHALLENGED => WafDecision::challenged($score, $matches, $this->describeReasons($matches)),
                WafDecision::MONITORED  => WafDecision::monitored($score, $matches, $this->describeReasons($matches), $original),
                default                 => WafDecision::allowed('below_threshold'),
            };

            // Ajuste fino para o status de bloqueio baseado no rate limit ou rebaixamento
            if ($decision->isBlocked()) {
                $status = 403;
                if ($rateResult !== null && ! $rateResult['allowed']) {
                    $status = 429;
                }
                $decision = WafDecision::blocked($status, $score, $matches, $this->describeReasons($matches));
            }

            // Persiste WAF_Event se nao for allowed (ou se amostragem estiver ativa)
            if (! $decision->isAllowed() || $this->shouldSampleAllowed()) {
                $eventId = $this->logger->log($ctx, $decision);
                if ($eventId) {
                    $decision = $decision->withEventId($eventId);
                }
            }

            // Disparar alertas para eventos criticos
            if ($decision->isBlocked() && $score >= 80) {
                $this->fireAlert('critical_finding', [
                    'attack_pattern' => $this->describeReasons($matches),
                    'ip'             => $ctx->ip,
                    'route'          => $ctx->routeName ?? $ctx->path,
                    'risk_score'     => $score,
                    'timestamp'      => now()->format('d/m/Y H:i:s'),
                    'country'        => $ctx->country ?? 'Desconhecido',
                ]);
            }

            // Auto-bloqueio de IP por acumulo
            if ($decision->isBlocked() || $decision->isChallenged()) {
                $autoBlockCfg = $this->settings->autoBlock;
                if (! empty($autoBlockCfg['enabled'])) {
                    $entry = $this->ipLists->autoBlock(
                        $ctx->ip,
                        (int) ($autoBlockCfg['window_minutes'] ?? 15),
                        (int) ($autoBlockCfg['threshold'] ?? 100),
                        (int) ($autoBlockCfg['duration_hours'] ?? 24)
                    );

                    if ($entry) {
                        $this->fireAlert('auto_block', [
                            'ip'          => $ctx->ip,
                            'event_count' => $autoBlockCfg['threshold'] ?? 100,
                            'window'      => $autoBlockCfg['window_minutes'] ?? 15,
                            'duration'    => $autoBlockCfg['duration_hours'] ?? 24,
                            'country'     => $ctx->country ?? 'Desconhecido',
                            'timestamp'   => now()->format('d/m/Y H:i:s'),
                        ]);
                    }
                }
            }

            return $decision;
        } catch (\Throwable $e) {
            return $this->handleFailure($e, $request);
        }
    }

    /**
     * Cria a resposta HTTP a partir da decisao para uso pelo middleware.
     */
    public function buildResponse(WafDecision $decision, Request $request): ?\Symfony\Component\HttpFoundation\Response
    {
        if ($decision->isAllowed() || $decision->isMonitored()) {
            return null; // segue para o kernel
        }

        if ($decision->isChallenged()) {
            $ctx = WafContext::fromRequest($request);
            if ($this->challenges->hasValidChallenge($ctx)) {
                return null;
            }
            return $this->challenges->buildResponse($ctx);
        }

        // blocked
        if ($request->expectsJson() || str_starts_with('/' . ltrim($request->path(), '/'), '/api/')) {
            return response()->json([
                'error' => 'request_blocked',
                'ref'   => $decision->eventId,
            ], $decision->status);
        }

        return response($this->blockedHtml($decision), $decision->status, [
            'Content-Type' => 'text/html; charset=utf-8',
        ]);
    }

    /* ================================================================ */

    private function matchRules(WafContext $ctx): array
    {
        $rules = $this->rules->allActive($ctx->scope);
        if ($rules->isEmpty()) {
            return [];
        }

        $matchers = $this->buildMatchers();
        $matches  = [];

        foreach ($rules as $rule) {
            /** @var WafRule $rule */
            $matcher = $matchers[$rule->matcher_type] ?? null;
            if ($matcher === null) {
                continue;
            }

            try {
                $match = $matcher->evaluate($rule, $ctx);
                if ($match !== null) {
                    $matches[] = $match;
                }
            } catch (\Throwable $e) {
                Log::channel('waf')->warning('Rule eval failed', [
                    'rule_id'    => $rule->id,
                    'matcher'    => $rule->matcher_type,
                    'error'      => $e->getMessage(),
                    'request_id' => $ctx->requestId,
                ]);
            }
        }

        return $matches;
    }

    /**
     * @return array<string, RuleMatcher>
     */
    private function buildMatchers(): array
    {
        return [
            WafRule::MATCHER_REGEX    => new RegexRuleMatcher(),
            WafRule::MATCHER_LIST     => new ListRuleMatcher(),
            WafRule::MATCHER_NUMERIC  => new NumericRuleMatcher(),
            WafRule::MATCHER_FUNCTION => new FunctionRuleMatcher(),
        ];
    }

    /**
     * @return array{allowed: bool, scope: string}|null
     */
    private function checkRateLimit(WafContext $ctx): ?array
    {
        $limits = $this->settings->rateLimits;
        $scope  = $ctx->scope ?? 'default';
        $cfg    = $limits[$scope] ?? $limits['default'] ?? null;

        if (! is_array($cfg)) {
            return null;
        }

        $limit  = (int) ($cfg['limit']  ?? 0);
        $window = (int) ($cfg['window'] ?? 0);

        if ($limit <= 0 || $window <= 0) {
            return null;
        }

        $identity = $scope . ':' . $ctx->ip . ($ctx->userId ? ':u' . $ctx->userId : '');
        $result   = $this->rateLimits->hit($identity, $limit, $window);

        return [
            'allowed' => $result->allowed,
            'scope'   => $scope,
        ];
    }

    private function shouldSampleAllowed(): bool
    {
        $r = $this->settings->allowedSamplingRatio;
        return $r > 0 && mt_rand() / mt_getrandmax() < $r;
    }

    private function describeReasons(array $matches): string
    {
        if (empty($matches)) {
            return 'no_rules';
        }

        $patterns = [];
        foreach ($matches as $m) {
            $patterns[] = $m->rule->attack_pattern;
        }

        return implode(',', array_unique($patterns));
    }

    /**
     * Dispara alerta do WAF de forma assíncrona (não bloqueia a requisição).
     */
    private function fireAlert(string $trigger, array $data): void
    {
        try {
            $alertService = new WafAlertService();
            $alertService->fire($trigger, $data);
        } catch (\Throwable $e) {
            // Nunca bloqueia a requisição por falha no alerta
            Log::channel('waf')->warning('Falha ao disparar alerta WAF: ' . $e->getMessage());
        }
    }

    private function handleFailure(\Throwable $e, Request $request): WafDecision
    {
        try {
            Log::channel('waf')->error('WafEngine exception: ' . $e->getMessage(), [
                'exception'  => get_class($e),
                'file'       => $e->getFile(),
                'line'       => $e->getLine(),
                'request_id' => $request->headers->get('X-Request-Id'),
                'path'       => $request->path(),
            ]);
        } catch (\Throwable $ee) {
            // Log falhou tambem - nao ha o que fazer
        }

        return $this->settings->isFailOpen()
            ? WafDecision::allowed('engine_failure_fail_open')
            : WafDecision::blocked(503, 0, [], 'engine_failure_fail_closed');
    }

    private function blockedHtml(WafDecision $decision): string
    {
        $ref = $decision->eventId ?? 'N/A';

        try {
            return view('errors.waf-blocked', ['ref' => $ref])->render();
        } catch (\Throwable $e) {
            // Fallback se a view não renderizar
            return <<<HTML
<!doctype html>
<html lang="pt-BR"><head>
<meta charset="utf-8"><title>Acesso Bloqueado</title>
<meta name="robots" content="noindex">
<style>body{font-family:sans-serif;display:flex;align-items:center;justify-content:center;min-height:100vh;background:#0f172a;color:#e2e8f0;text-align:center;}</style>
</head><body>
<div><h1>Acesso Bloqueado</h1><p>Sua requisição foi bloqueada pelo sistema de segurança.</p><p>Código: <code>{$ref}</code></p><a href="/" style="color:#93c5fd;">Voltar ao início</a></div>
</body></html>
HTML;
        }
    }
}
