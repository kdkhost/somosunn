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
 */

namespace App\Contracts;

/**
 * Contrato do AdvancedRateLimitMiddleware.
 *
 * Define a API utilizada pelo Rate Limiter avancado para detectar
 * User-Agents suspeitos, contar requisicoes por IP em janela
 * deslizante de 60 segundos, gerenciar whitelist e bloqueios
 * persistidos na tabela `rate_limit_blocks`.
 *
 * Storage de contagem: arquivo em
 * `storage/framework/rate-limits/{md5(ip)}.json` no formato
 * `{"timestamps":[<unix_ts>, ...]}`.
 *
 * Configuracoes (tabela settings):
 *   - rate_limit_threshold (default 100)
 *   - rate_limit_block_duration (default 15 minutos)
 *   - rate_limit_block_increment (default 5 minutos)
 *   - rate_limit_whitelist (JSON array de IPs)
 *   - rate_limit_ua_patterns (JSON array de substrings em lowercase)
 *
 * Spec: .kiro/specs/advanced-security-performance
 * Requisitos: 5.1, 5.2, 5.3, 5.4, 5.5, 5.6, 5.7
 */
interface RateLimiterInterface
{
    /**
     * Indica se o IP esta atualmente bloqueado conforme registro
     * em `rate_limit_blocks` (blocked_until > now()).
     */
    public function isBlocked(string $ip): bool;

    /**
     * Indica se o User-Agent contem qualquer um dos padroes
     * configurados como suspeitos (comparacao case-insensitive,
     * substring match).
     */
    public function isSuspiciousAgent(string $userAgent): bool;

    /**
     * Registra um timestamp de requisicao no arquivo do IP e remove
     * entradas mais antigas que a janela de 60 segundos.
     */
    public function recordRequest(string $ip): void;

    /**
     * Insere ou atualiza um registro de bloqueio para o IP. Se o IP
     * ja possui bloqueio ativo, incrementa `attempts` e estende
     * `blocked_until` somando `$durationMinutes` ao instante atual.
     */
    public function blockIp(string $ip, int $durationMinutes, string $reason = 'rate_limit_exceeded'): void;

    /**
     * Indica se o IP consta na whitelist configurada (match exato).
     */
    public function isWhitelisted(string $ip): bool;

    /**
     * Retorna o numero de requisicoes registradas para o IP dentro
     * da janela em segundos informada (padrao 60s).
     */
    public function getRequestCount(string $ip, int $windowSeconds = 60): int;
}
