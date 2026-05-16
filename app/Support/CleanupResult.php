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
 * Value object retornado pelo LogRotator (comando `logs:cleanup`)
 * apos uma execucao de varredura de logs.
 *
 * Campos:
 *   - filesRemoved:    total de arquivos deletados por excederem a retencao
 *   - filesCompressed: total de arquivos comprimidos via gzip (>7 dias)
 *   - bytesReclaimed:  bytes liberados em disco (soma do tamanho dos
 *                      arquivos removidos + economia bruta da compressao)
 *   - errors:          lista de mensagens de erro por arquivo (fail-safe).
 *                      A operacao NAO interrompe quando um arquivo falha;
 *                      o erro eh registrado e o processamento continua.
 *
 * Spec: .kiro/specs/advanced-security-performance
 * Requirements: 10.5, 10.7
 */
class CleanupResult
{
    public int $filesRemoved = 0;

    public int $filesCompressed = 0;

    public int $bytesReclaimed = 0;

    /**
     * @var array<int, string>
     */
    public array $errors = [];

    /**
     * Converte o resultado para array (util para logs estruturados e
     * respostas JSON).
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'files_removed' => $this->filesRemoved,
            'files_compressed' => $this->filesCompressed,
            'bytes_reclaimed' => $this->bytesReclaimed,
            'errors' => $this->errors,
        ];
    }
}
