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
 * Value object retornado pelo ImageProcessorService apos processamento de imagem.
 *
 * Campos:
 *   - originalPath:   path relativo do arquivo principal armazenado (sempre presente)
 *   - webpPath:       path relativo da variante WebP, ou null se nao foi gerada
 *   - thumbnails:     ['thumb' => path, 'medium' => path, 'large' => path] (vazio em falha)
 *   - originalSize:   bytes do arquivo enviado pelo usuario (entrada)
 *   - processedSize:  bytes do arquivo final apos otimizacao (saida)
 *   - wasResized:     true se a imagem foi redimensionada para caber em max resolution
 *
 * Em caso de falha de processamento, o objeto e retornado com:
 *   - originalPath setado (arquivo original preservado)
 *   - webpPath = null
 *   - thumbnails = []
 *   - wasResized = false
 *   - processedSize = originalSize
 */
class ImageProcessResult
{
    public function __construct(
        public string $originalPath = '',
        public ?string $webpPath = null,
        public array $thumbnails = [],
        public int $originalSize = 0,
        public int $processedSize = 0,
        public bool $wasResized = false,
    ) {
    }

    /**
     * Converte o resultado para array (util para logs e respostas JSON).
     */
    public function toArray(): array
    {
        return [
            'original_path' => $this->originalPath,
            'webp_path' => $this->webpPath,
            'thumbnails' => $this->thumbnails,
            'original_size' => $this->originalSize,
            'processed_size' => $this->processedSize,
            'was_resized' => $this->wasResized,
        ];
    }
}
