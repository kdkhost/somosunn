<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * SecureUploadService - validação e hardening de uploads.
 *
 * Centraliza:
 *   - Validação de MIME real (finfo)
 *   - Validação de extensão por allowlist
 *   - Bloqueio de extensões perigosas
 *   - Geração de nome seguro (UUID)
 *   - Limite de tamanho
 *   - Log de uploads suspeitos
 *
 * NÃO substitui UploadStorage::storeUploadedFile() — complementa.
 * Pode ser chamado antes do storeUploadedFile para validação extra.
 *
 * Prompt de segurança item 2: Hardening de Uploads
 * Spec: .kiro/specs/waf-e-auditoria-seguranca Requisitos: 6.1-6.7
 */
class SecureUploadService
{
    /**
     * Extensões SEMPRE bloqueadas, independente do contexto.
     */
    private const BLOCKED_EXTENSIONS = [
        'php', 'phtml', 'phar', 'phps', 'phtm', 'php3', 'php4', 'php5', 'php7', 'php8',
        'pl', 'py', 'rb', 'jsp', 'asp', 'aspx', 'exe', 'sh', 'bat', 'cmd', 'cgi',
        'htaccess', 'htpasswd', 'ini', 'log', 'sql', 'bak', 'swp',
        'com', 'vbs', 'wsf', 'msi', 'dll', 'scr', 'ps1',
    ];

    /**
     * Allowlists por contexto.
     */
    private const ALLOWED_EXTENSIONS = [
        'image'    => ['jpg', 'jpeg', 'png', 'gif', 'webp', 'avif', 'bmp', 'ico'],
        'video'    => ['mp4', 'webm', 'mkv', 'avi', 'mov'],
        'document' => ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'txt', 'csv', 'odt', 'ods'],
        'avatar'   => ['jpg', 'jpeg', 'png', 'webp'],
    ];

    private const ALLOWED_MIMES = [
        'image'    => ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/avif', 'image/bmp', 'image/x-icon'],
        'video'    => ['video/mp4', 'video/webm', 'video/x-matroska', 'video/avi', 'video/quicktime'],
        'document' => ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'application/vnd.ms-powerpoint', 'application/vnd.openxmlformats-officedocument.presentationml.presentation', 'text/plain', 'text/csv'],
        'avatar'   => ['image/jpeg', 'image/png', 'image/webp'],
    ];

    /**
     * Valida um upload. Retorna array de erros (vazio = OK).
     *
     * @param UploadedFile $file
     * @param string       $context  image|video|document|avatar
     * @param int|null     $maxKb    Limite em KB (null = sem limite extra)
     * @return array<string>
     */
    public function validate(UploadedFile $file, string $context = 'image', ?int $maxKb = null): array
    {
        $errors = [];

        // 1. Extensão
        $ext = strtolower($file->getClientOriginalExtension());

        if (in_array($ext, self::BLOCKED_EXTENSIONS, true)) {
            $errors[] = "Extensão '{$ext}' bloqueada por segurança.";
            $this->logSuspicious($file, 'blocked_extension', $ext);
            return $errors; // Não precisa continuar
        }

        $allowedExts = self::ALLOWED_EXTENSIONS[$context] ?? self::ALLOWED_EXTENSIONS['image'];
        if (! in_array($ext, $allowedExts, true)) {
            $errors[] = "Extensão '{$ext}' não permitida para o contexto '{$context}'.";
        }

        // 2. MIME real via finfo
        $realMime = null;
        if ($file->isValid()) {
            $finfo = new \finfo(FILEINFO_MIME_TYPE);
            $realMime = $finfo->file($file->getRealPath());
        }

        $allowedMimes = self::ALLOWED_MIMES[$context] ?? self::ALLOWED_MIMES['image'];
        if ($realMime && ! in_array($realMime, $allowedMimes, true)) {
            $errors[] = "MIME real '{$realMime}' não compatível com contexto '{$context}'.";
            $this->logSuspicious($file, 'mime_mismatch', $realMime);
        }

        // 3. MIME declarado vs real (divergência = suspeito)
        $clientMime = strtolower($file->getClientMimeType());
        if ($realMime && $clientMime && $realMime !== $clientMime) {
            // Não bloqueia necessariamente, mas loga
            $this->logSuspicious($file, 'mime_divergence', "client={$clientMime} real={$realMime}");
        }

        // 4. Tamanho
        if ($maxKb !== null && $file->getSize() > $maxKb * 1024) {
            $errors[] = "Arquivo excede o limite de {$maxKb} KB.";
        }

        // 5. Nome com null byte
        $originalName = $file->getClientOriginalName();
        if (str_contains($originalName, "\x00") || str_contains($originalName, '%00')) {
            $errors[] = 'Nome de arquivo contém byte nulo.';
            $this->logSuspicious($file, 'null_byte_filename', $originalName);
        }

        // 6. Double extension (shell.php.jpg)
        if (preg_match('/\.(php|phtml|phar|asp|jsp|exe|sh|bat|cmd)\./i', $originalName)) {
            $errors[] = 'Nome de arquivo contém extensão perigosa oculta.';
            $this->logSuspicious($file, 'double_extension', $originalName);
        }

        return $errors;
    }

    /**
     * Gera nome seguro (UUID + extensão validada).
     */
    public function secureName(UploadedFile $file): string
    {
        $ext = strtolower($file->getClientOriginalExtension());

        // Garante que a extensão não é perigosa
        if (in_array($ext, self::BLOCKED_EXTENSIONS, true) || $ext === '') {
            $ext = 'bin';
        }

        return Str::uuid()->toString() . '.' . $ext;
    }

    /**
     * Verifica se a extensão é segura (não está na blocklist).
     */
    public function isExtensionSafe(string $extension): bool
    {
        return ! in_array(strtolower($extension), self::BLOCKED_EXTENSIONS, true);
    }

    /**
     * Registra upload suspeito no canal security.
     */
    private function logSuspicious(UploadedFile $file, string $reason, string $detail): void
    {
        try {
            Log::channel('security')->warning('Upload suspeito detectado', [
                'reason'        => $reason,
                'detail'        => $detail,
                'original_name' => $file->getClientOriginalName(),
                'size'          => $file->getSize(),
                'client_mime'   => $file->getClientMimeType(),
                'ip'            => request()->ip(),
                'user_id'       => auth()->id(),
                'path'          => request()->path(),
            ]);
        } catch (\Throwable $e) {
            Log::warning('Upload suspeito: ' . $reason . ' - ' . $detail);
        }
    }
}
