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
 * Sistema UNN - Contrato AnomalyDetectorInterface
 *
 * Define a API publica do servico de deteccao de anomalias basicas
 * (logins falhos em rajada, flood de uploads, webhooks invalidos
 * repetidos). Os contadores sao mantidos em cache file-based
 * (compativel com hospedagem cPanel/LiteSpeed sem Redis), e cada
 * anomalia detectada e persistida em `anomaly_events` para
 * auditoria, gerando notificacao por email ao Superadmin e,
 * opcionalmente, bloqueio automatico via WAF/Rate Limiter.
 *
 * Spec: .kiro/specs/advanced-security-performance
 * Requisitos: 11.1, 11.2, 11.3, 11.4, 11.5, 11.6, 11.7
 */

namespace App\Contracts;

interface AnomalyDetectorInterface
{
    /**
     * Registra uma tentativa de login (sucesso ou falha) para o IP.
     *
     * Em caso de sucesso, o contador de falhas para o IP pode ser
     * limpo. Em caso de falha, o contador e incrementado em janela
     * deslizante; se ultrapassar o threshold configurado, a
     * anomalia e flaggeada (registrada + notificada + opcional
     * auto-block via WAF).
     */
    public function recordLoginAttempt(string $ip, bool $success): void;

    /**
     * Registra um upload realizado pelo usuario informado.
     *
     * O contador por usuario e incrementado em janela deslizante;
     * se ultrapassar o threshold de upload flood configurado, a
     * anomalia e flaggeada (registrada + notificada).
     */
    public function recordUpload(int $userId): void;

    /**
     * Registra um callback de webhook recebido (valido ou invalido)
     * para a fonte informada (gateway ou identificador da origem).
     *
     * Apenas webhooks invalidos contam para deteccao. Se o numero
     * de invalidos para a mesma fonte ultrapassar o threshold, a
     * anomalia e flaggeada (registrada + notificada).
     */
    public function recordWebhook(string $source, bool $valid): void;

    /**
     * Retorna a lista de anomalias ativas/recentes (ainda dentro
     * da janela monitorada) formatada para exibicao em painel
     * administrativo.
     *
     * @return array<int, array<string, mixed>>
     */
    public function checkThresholds(): array;

    /**
     * Retorna os thresholds configurados atualmente (lidos da tabela
     * settings, com fallback para os valores padrao).
     *
     * Chaves esperadas:
     *   - anomaly_login_threshold
     *   - anomaly_upload_threshold
     *   - anomaly_webhook_threshold
     *   - anomaly_auto_block
     *
     * @return array<string, int|bool>
     */
    public function getThresholds(): array;
}
