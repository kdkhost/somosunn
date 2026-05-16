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
 * Sistema UNN - Contrato LogRotatorInterface
 *
 * Define a API publica do servico de rotacao e limpeza de logs.
 * Responsavel por remover arquivos antigos conforme retencao
 * configurada por canal (waf, security, application) e comprimir
 * via gzip os arquivos com mais de 7 dias para economizar disco.
 *
 * Spec: .kiro/specs/advanced-security-performance
 * Requirements: 10.1, 10.2, 10.3, 10.4, 10.5, 10.6, 10.7
 */

namespace App\Contracts;

use App\Support\CleanupResult;

interface LogRotatorInterface
{
    /**
     * Executa a varredura de `storage/logs/*.log` e `*.log.gz`, aplicando
     * retencao por canal e compressao de arquivos antigos. Retorna um
     * CleanupResult com totais de arquivos removidos, comprimidos e bytes
     * recuperados, alem da lista de erros (fail-safe por arquivo).
     */
    public function cleanup(): CleanupResult;

    /**
     * Comprime via gzip o arquivo informado, gerando um `.gz` ao lado e
     * removendo o original em caso de sucesso. Retorna true em sucesso,
     * false em qualquer falha de I/O (sem throw).
     */
    public function compress(string $logPath): bool;

    /**
     * Retorna o numero de dias de retencao configurado para o canal
     * informado. Canais conhecidos: 'waf' (default 30), 'security'
     * (default 90), demais (default 30, configuravel via setting
     * `log_retention_days`).
     */
    public function getRetentionDays(string $channel): int;
}
