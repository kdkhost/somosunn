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
 * Sistema UNN - Contrato BackupInterface
 *
 * Define a API publica do servico de backup automatico do banco de dados
 * e dos arquivos de configuracao da aplicacao para um bucket S3 (ou
 * compativel S3, como IDrive E2). Inclui retencao automatica configuravel
 * via tabela settings (backup_keep_daily, backup_keep_weekly).
 *
 * Spec: .kiro/specs/advanced-security-performance
 * Requirements: 7.1, 7.2, 7.3, 7.4, 7.6, 7.7, 7.8
 */

namespace App\Contracts;

use App\Support\BackupResult;

interface BackupInterface
{
    /**
     * Executa um dump completo do banco de dados via mysqldump, comprime
     * em gzip e envia para `backups/db/YYYY-MM-DD_HHmmss.sql.gz` no disco S3.
     *
     * Em caso de falha, registra log de erro, dispara notificacao por email
     * ao Superadmin e retorna um BackupResult com success = false.
     */
    public function backupDatabase(): BackupResult;

    /**
     * Empacota os arquivos de configuracao (.env e config/*.php) em um
     * tar.gz e envia para `backups/config/YYYY-MM-DD_HHmmss.tar.gz` no disco S3.
     *
     * Em caso de falha, registra log de erro, dispara notificacao por email
     * ao Superadmin e retorna um BackupResult com success = false.
     */
    public function backupConfig(): BackupResult;

    /**
     * Lista os backups armazenados no bucket S3 sob `backups/{type}`.
     *
     * Tipos validos: 'db' (default) ou 'config'. Tipos desconhecidos retornam
     * lista vazia (sem throw).
     *
     * @return array<int, array{path: string, size: int, modified: int}>
     *         Ordenado por modified (timestamp) DESC (mais recente primeiro).
     */
    public function listBackups(string $type = 'db'): array;

    /**
     * Aplica retencao em ambos os tipos de backup (db e config), mantendo
     * apenas os $keepDaily backups de banco mais recentes e os $keepWeekly
     * backups de configuracao mais recentes. Os demais sao removidos do bucket.
     *
     * Retorna o numero total de arquivos removidos (db + config).
     */
    public function deleteOldBackups(int $keepDaily = 30, int $keepWeekly = 12): int;

    /**
     * Retorna o tamanho em bytes de um backup armazenado no bucket S3.
     * Retorna 0 se o arquivo nao existir ou em caso de falha de I/O.
     */
    public function getBackupSize(string $path): int;
}
