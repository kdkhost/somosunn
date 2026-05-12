<?php

namespace App\Http\Middleware;

use App\Services\Waf\WafEngine;
use App\Services\Waf\WafSettings;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

/**
 * WAF Middleware (Web Application Firewall da Unn)
 *
 * Primeiro gate de seguranca no pipeline global. Delega para o
 * WafEngine quando WAF_ENABLED=true; caso contrario curto-circuita
 * sem impacto.
 *
 * Spec: .kiro/specs/waf-e-auditoria-seguranca
 * Requisitos: 9.1, 22.4
 */
class WafMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        // Propaga request_id para correlacao com logs
        if (! $request->headers->has('X-Request-Id')) {
            $request->headers->set('X-Request-Id', (string) Str::uuid());
        }

        // WAF desligado? segue sem inspecao
        if (! config('waf.enabled', false)) {
            return $next($request);
        }

        try {
            $settings = WafSettings::load();

            // Double-check: settings do banco pode ter desligado
            if (! $settings->enabled) {
                return $next($request);
            }

            $engine   = WafEngine::make($settings);
            $decision = $engine->inspect($request);

            // Se o engine decidiu bloquear ou desafiar, retorna resposta
            $response = $engine->buildResponse($decision, $request);
            if ($response !== null) {
                return $response;
            }

            // Segue para o pipeline Laravel
            return $next($request);
        } catch (\Throwable $e) {
            // Fail-open: se algo der errado no WAF, nao trava o sistema
            try {
                \Illuminate\Support\Facades\Log::channel('waf')->error(
                    'WafMiddleware exception: ' . $e->getMessage(),
                    ['path' => $request->path(), 'ip' => $request->ip()]
                );
            } catch (\Throwable $ee) {
                // ignora
            }

            return $next($request);
        }
    }
}
