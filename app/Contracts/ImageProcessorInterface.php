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

use App\Support\ImageProcessResult;
use Illuminate\Http\UploadedFile;

/**
 * Contrato para servicos de processamento de imagem.
 *
 * Implementacoes devem usar a GD library (funcoes image* do PHP nativas)
 * para garantir compatibilidade com hospedagem compartilhada cPanel/LiteSpeed
 * sem dependencia de Imagick ou Intervention Image.
 *
 * Operacoes principais:
 *   - process:           orquestra o pipeline completo (resize -> stripExif -> save -> thumbs -> webp)
 *   - generateThumbnails: gera variantes em multiplas dimensoes preservando aspect ratio
 *   - convertToWebP:     gera variante WebP da imagem fonte
 *   - stripExif:         remove metadados EXIF recriando a imagem com GD
 *   - optimize:          aplica compressao/qualidade configurada
 *
 * Em caso de falha, os metodos devem registrar o erro no log e preservar
 * o arquivo original sem modificacao (padrao fail-safe).
 */
interface ImageProcessorInterface
{
    /**
     * Processa uma imagem enviada pelo usuario, gerando variantes otimizadas.
     *
     * @param UploadedFile $file      Arquivo enviado pelo usuario
     * @param string       $directory Diretorio de destino relativo (ex: 'uploads/imagens/posts')
     * @param array        $options   Opcoes opcionais:
     *                                - filename: nome customizado do arquivo (sem extensao)
     *                                - generate_webp: bool (default: true)
     *                                - generate_thumbnails: bool (default: true)
     *                                - strip_exif: bool (default: true)
     *                                - max_resolution: int override de configuracao
     *                                - jpeg_quality: int override (1-100)
     *                                - webp_quality: int override (1-100)
     *
     * @return ImageProcessResult Resultado contendo paths e metadados
     */
    public function process(UploadedFile $file, string $directory, array $options = []): ImageProcessResult;

    /**
     * Gera multiplas variantes de thumbnail a partir de uma imagem fonte.
     *
     * @param string $sourcePath Path absoluto da imagem fonte
     * @param array  $sizes      Mapa label => max dimensao em px, ex: ['thumb' => 150, 'medium' => 600]
     *
     * @return array<string,string> Mapa label => path absoluto do thumbnail gerado
     */
    public function generateThumbnails(string $sourcePath, array $sizes): array;

    /**
     * Converte uma imagem para WebP.
     *
     * @param string $sourcePath Path absoluto da imagem fonte
     * @param int    $quality    Qualidade 1-100 (default: 85)
     *
     * @return string|null Path absoluto do arquivo WebP gerado, ou null em falha
     */
    public function convertToWebP(string $sourcePath, int $quality = 85): ?string;

    /**
     * Remove metadados EXIF da imagem recriando-a com GD.
     *
     * GD nao preserva metadados EXIF ao recriar uma imagem, portanto este e
     * o mecanismo nativo de "strip" usado pelo servico.
     *
     * @param string $sourcePath Path absoluto da imagem
     *
     * @return bool true se a operacao foi bem sucedida
     */
    public function stripExif(string $sourcePath): bool;

    /**
     * Aplica otimizacao (qualidade/compressao) na imagem in-place.
     *
     * @param string $sourcePath Path absoluto da imagem
     * @param array  $options    Opcoes de otimizacao (jpeg_quality, webp_quality, png_compression)
     *
     * @return string Path da imagem otimizada (mesmo path se sucesso, original em caso de falha)
     */
    public function optimize(string $sourcePath, array $options = []): string;
}
