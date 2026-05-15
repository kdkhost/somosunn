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

namespace App\Helpers;

use App\Support\UploadStorage;
use Illuminate\Http\UploadedFile;

/**
 * Helper de upload de arquivos.
 *
 * Wrapper simplificado sobre UploadStorage para uso rapido em controllers.
 * Todas as operacoes respeitam o disco ativo (local ou S3) configurado no painel.
 */
class FileUploadHelper
{
    /**
     * Armazena um arquivo enviado no disco ativo (local ou S3).
     *
     * @param UploadedFile $file      Arquivo enviado via request
     * @param string       $directory Diretorio de destino (ex: 'event-images', 'course-thumbs')
     * @param string|null  $filename  Nome customizado (null = gera automatico)
     * @param array        $options   Opcoes extras: ['watermark' => bool, 'prefix' => string]
     * @return string Path relativo do arquivo armazenado
     */
    public static function store(
        UploadedFile $file,
        string $directory,
        ?string $filename = null,
        array $options = []
    ): string {
        return UploadStorage::storeUploadedFile($file, $directory, $filename, $options);
    }

    /**
     * Gera a URL publica de um arquivo armazenado.
     *
     * Prioriza local se existir, depois S3 (com URL assinada se bucket privado).
     *
     * @param string|null $path    Path relativo do arquivo
     * @param string|null $default URL padrao se arquivo nao encontrado
     * @return string|null URL publica ou default
     */
    public static function url(?string $path, ?string $default = null): ?string
    {
        return UploadStorage::url($path, $default);
    }

    /**
     * Deleta um arquivo do disco ativo.
     *
     * Remove do S3 e/ou local conforme disponibilidade.
     *
     * @param string|null $path Path relativo do arquivo
     * @return bool True se deletou com sucesso
     */
    public static function delete(?string $path): bool
    {
        return UploadStorage::delete($path);
    }

    /**
     * Verifica se um arquivo existe (local ou S3).
     *
     * @param string|null $path Path relativo do arquivo
     * @return bool
     */
    public static function exists(?string $path): bool
    {
        return UploadStorage::exists($path);
    }

    /**
     * Retorna o tamanho do arquivo em bytes.
     *
     * @param string|null $path Path relativo do arquivo
     * @return int|null Tamanho em bytes ou null se nao encontrado
     */
    public static function size(?string $path): ?int
    {
        return UploadStorage::size($path);
    }

    /**
     * Verifica se o disco ativo e local.
     *
     * @return bool
     */
    public static function isLocal(): bool
    {
        return UploadStorage::isLocal();
    }

    /**
     * Retorna o nome do disco efetivo ('public' ou 's3').
     *
     * @return string
     */
    public static function activeDisk(): string
    {
        return UploadStorage::effectiveDisk();
    }
}
