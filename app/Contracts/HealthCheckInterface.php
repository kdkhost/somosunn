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
 * Sistema UNN - Contrato HealthCheckInterface
 *
 * Define a API publica do health monitor que valida a saude dos componentes
 * criticos do sistema: banco de dados, S3 (IDrive E2), permissoes de
 * escrita em disco, fila de jobs e permissoes de diretorios de storage.
 *
 * Spec: .kiro/specs/advanced-security-performance
 * Requirements: 9.1, 9.2, 9.3, 9.4, 9.5, 9.6, 9.7
 */

namespace App\Contracts;

use App\Support\ComponentStatus;
use App\Support\HealthResult;

interface HealthCheckInterface
{
    /**
     * Executa todas as verificacoes de saude e retorna um HealthResult
     * agregado contendo o status de cada componente, status global,
     * timestamp e tempo total de resposta.
     *
     * Cada componente e isolado em seu proprio try/catch para que falhas
     * isoladas nao impecam a verificacao dos demais.
     */
    public function check(): HealthResult;

    /**
     * Executa a verificacao de um unico componente identificado por nome.
     *
     * Componentes suportados:
     *   - "database":             SELECT 1 no MySQL/MariaDB
     *   - "s3":                   listagem de objetos no bucket S3/IDrive E2
     *   - "disk_write":           escrita e remocao de um arquivo temporario
     *   - "queue_health":         contagem de jobs pendentes na tabela jobs
     *   - "storage_permissions":  verificacao de permissoes em storage/{app,framework,logs}
     *
     * Em caso de excecao, retorna ComponentStatus com status "error" e a
     * mensagem da excecao.
     */
    public function checkComponent(string $component): ComponentStatus;
}
