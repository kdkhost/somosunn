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

namespace App\Support;

/**
 * Value object retornado pelo BackupService apos uma operacao de backup
 * (banco de dados ou arquivos de configuracao).
 *
 * Campos:
 *   - success:          true em caso de sucesso, false caso contrario
 *   - path:             caminho relativo no bucket S3 (ex: backups/db/2024-01-15_030000.sql.gz)
 *                       ou null em caso de falha antes do upload
 *   - sizeBytes:        tamanho em bytes do artefato gerado (medido localmente
 *                       antes do upload). Zero em caso de falha
 *   - durationSeconds:  duracao total da operacao em segundos (microtime delta)
 *   - error:            mensagem de erro humana (apenas quando success = false)
 *
 * Spec: .kiro/specs/advanced-security-performance
 * Requirements: 7.1, 7.2, 7.3, 7.4, 7.7, 7.8
 */
class BackupResult
{
    public function __construct(
        public bool $success,
        public ?string $path = null,
        public int $sizeBytes = 0,
        public float $durationSeconds = 0.0,
        public ?string $error = null,
    ) {
    }

    /**
     * Converte o resultado para array (util para logs e respostas JSON).
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'success' => $this->success,
            'path' => $this->path,
            'size_bytes' => $this->sizeBytes,
            'duration_seconds' => round($this->durationSeconds, 3),
            'error' => $this->error,
        ];
    }
}
