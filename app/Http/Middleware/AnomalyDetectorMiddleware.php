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
 * Sistema UNN - AnomalyDetectorMiddleware
 *
 * Middleware leve que serve como ponto de extensao para registro
 * de metricas de requisicao no AnomalyDetectorService. NAO bloqueia
 * requisicoes nem altera a resposta - apenas observa.
 *
 * O registro de eventos especificos (login attempts, uploads,
 * webhooks) e feito diretamente nos pontos de entrada onde temos
 * conhecimento semantico (LoginController, UploadStorage,
 * PaymentWebhookController). Este middleware e um placeholder
 * estavel para futuras metricas globais (ex.: 4xx/5xx em rajada
 * por IP) caso seja necessario.
 *
 * Spec: .kiro/specs/advanced-security-performance
 * Requirements: 11.1, 11.2, 11.3
 */

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AnomalyDetectorMiddleware
{
    /**
     * Handle an incoming request.
     *
     * Pass-through: nao bloqueia, apenas observa. As gravacoes
     * concretas no AnomalyDetectorService acontecem nos pontos
     * de entrada com conhecimento semantico (login, upload,
     * webhook). Em caso de falha futura no registro de metricas,
     * a excecao e absorvida para nao quebrar o fluxo original
     * (Requirement 11.7).
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Pass through without blocking - just observe.
        return $next($request);
    }
}
