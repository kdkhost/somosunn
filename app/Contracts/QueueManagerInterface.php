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
 * Sistema UNN - Contrato QueueManagerInterface
 *
 * Define a API publica do gerenciador de filas usado para despachar
 * jobs em background, consultar metricas (pending/failed), reprocessar
 * jobs falhos e purgar registros antigos da tabela failed_jobs.
 *
 * Spec: .kiro/specs/advanced-security-performance
 * Requirements: 1.1, 1.6, 1.7, 1.8
 */

namespace App\Contracts;

use Illuminate\Contracts\Queue\ShouldQueue;

interface QueueManagerInterface
{
    /**
     * Despacha um job para a queue informada.
     */
    public function dispatch(string $queue, ShouldQueue $job): void;

    /**
     * Retorna a configuracao consolidada (retry attempts, timeout, connection)
     * para a queue informada, baseada nas settings do sistema.
     *
     * @return array<string, mixed>
     */
    public function getQueueConfig(string $queue): array;

    /**
     * Conta jobs pendentes na tabela jobs. Se $queue for informada,
     * filtra por aquele nome de queue.
     */
    public function getPendingCount(?string $queue = null): int;

    /**
     * Conta jobs na tabela failed_jobs.
     */
    public function getFailedCount(): int;

    /**
     * Reprocessa um job falho identificado pelo uuid.
     */
    public function retryFailed(string $uuid): bool;

    /**
     * Remove jobs falhos com failed_at anterior a (now - $daysOld dias).
     * Retorna a quantidade de registros removidos.
     */
    public function purgeOldFailed(int $daysOld = 30): int;
}
