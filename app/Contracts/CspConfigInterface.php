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
 * Sistema UNN - Contrato CspConfigInterface
 *
 * Define a API publica do servico/value object responsavel por construir
 * a Content-Security-Policy do sistema, expor a allowlist (base + extras
 * configuraveis via settings) e calcular o valor de Permissions-Policy
 * sensivel a rota (camera liberada apenas para rotas de QR scanner).
 *
 * Spec: .kiro/specs/advanced-security-performance
 * Requirements: 8.1, 8.2, 8.3, 8.6, 8.8
 */

namespace App\Contracts;

use Illuminate\Http\Request;

interface CspConfigInterface
{
    /**
     * Retorna o mapa final de directives da CSP no formato:
     * [
     *     'default-src' => ["'self'"],
     *     'script-src'  => ["'self'", "'unsafe-inline'", 'https://cdn.jsdelivr.net', ...],
     *     'style-src'   => ["'self'", "'unsafe-inline'", ...],
     *     'img-src'     => ["'self'", 'data:', 'blob:', 'https:', 'http:'],
     *     'font-src'    => ["'self'", 'data:', ...],
     *     'connect-src' => ["'self'", 'https:', 'wss:'],
     *     'frame-src'   => ["'self'", 'https://www.youtube.com', ...],
     * ]
     *
     * As entradas da allowlist configuravel (settings.csp_extra_allowlist)
     * SAO mescladas com as fontes base.
     *
     * @return array<string, array<int, string>>
     */
    public function getDirectives(): array;

    /**
     * Retorna apenas a allowlist (base + extras configuraveis), sem os
     * tokens especiais ('self', 'unsafe-inline', 'unsafe-eval'). Util
     * para diagnostico/relatorios.
     *
     * @return array<string, array<int, string>>
     */
    public function getAllowlist(): array;

    /**
     * Constroi o valor do header Permissions-Policy adequado a rota da
     * request: rotas de QR scanner recebem `camera=(self)`, demais rotas
     * recebem `camera=()`. Outros recursos (microphone, geolocation,
     * payment) seguem politica restritiva por padrao.
     */
    public function getPermissionsPolicy(Request $request): string;

    /**
     * Adiciona uma fonte a uma directive em runtime (uso em testes ou em
     * fluxos administrativos que precisam estender a allowlist sem
     * recarregar a configuracao).
     */
    public function addToAllowlist(string $directive, string $source): void;
}
