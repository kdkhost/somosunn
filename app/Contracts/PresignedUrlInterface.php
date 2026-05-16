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
 * Sistema UNN - Contrato PresignedUrlInterface
 *
 * Define a API publica do servico de geracao de URLs assinadas
 * temporarias (presigned URLs) para arquivos hospedados em S3
 * (ou compativel S3). Mapeia extensoes para TTLs configuraveis
 * via settings (docs, media, default).
 *
 * Spec: .kiro/specs/advanced-security-performance
 * Requirements: 3.1, 3.2, 3.4, 3.5, 3.6
 */

namespace App\Contracts;

interface PresignedUrlInterface
{
    /**
     * Gera uma URL assinada temporaria para o arquivo informado.
     *
     * Se $expirationMinutes nao for informado, deriva do tipo do arquivo
     * (extensao) usando getExpirationForType(). A URL e gerada via
     * Storage::disk('s3')->temporaryUrl().
     *
     * @param string   $filePath           Caminho relativo do arquivo no bucket.
     * @param int|null $expirationMinutes  Minutos ate expirar (sobrepoe mapeamento por tipo).
     *
     * @throws \RuntimeException Quando S3 indisponivel (sem expor path interno).
     */
    public function generate(string $filePath, ?int $expirationMinutes = null): string;

    /**
     * Retorna o TTL em minutos para a extensao informada.
     *
     * Funcao DETERMINISTICA e PURA: mesma entrada produz sempre a mesma saida
     * dentro do mesmo request (mappings sao cacheados por request). Realiza
     * lowercase da extensao antes do matching.
     *
     * Mapeamento padrao:
     *   - docs (pdf, doc, docx, xls, xlsx)  -> 30 min
     *   - media (mp4, webm, mp3, wav)       -> 120 min
     *   - default (qualquer outra extensao) -> 60 min
     */
    public function getExpirationForType(string $fileExtension): int;

    /**
     * Verifica se a URL informada ja expirou no momento atual.
     *
     * Faz parse dos parametros X-Amz-Date e X-Amz-Expires presentes na
     * query string da URL assinada e calcula se o tempo absoluto de
     * expiracao ja passou.
     */
    public function isExpired(string $url): bool;
}
