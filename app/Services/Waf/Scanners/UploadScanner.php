<?php

namespace App\Services\Waf\Scanners;

/**
 * Scanner de uploads.
 *
 * Busca por rotas/controllers que recebem arquivos mas NAO utilizam
 * `UploadStorage::storeUploadedFile()` e por ausencia de validacao
 * de MIME/extensao.
 *
 * Heuristica:
 *   - Arquivo PHP contem `UploadedFile` ou `$request->file(` (e similares)
 *   - Checa se faz referencia a `UploadStorage` ou se usa `->store(`/`->move(`/`->storeAs(`
 *   - Checa se tem `mimes`/`mimetypes`/`extensions`/`max:` em validacao proxima
 *
 * Spec: .kiro/specs/waf-e-auditoria-seguranca
 * Requisitos: 1.6, 6.1, 6.2, 6.3
 */
class UploadScanner extends AbstractScanner
{
    private int $counter = 0;

    public function id(): string
    {
        return 'upload';
    }

    public function label(): string
    {
        return 'Uploads sem UploadStorage ou validacao MIME/extensao';
    }

    public function scan(AuditContext $ctx): iterable
    {
        foreach ($this->iterateFiles($ctx, ['.php'], ['app/Http/Controllers', 'app/Http/Requests']) as $file) {
            $absPath = $file->getPathname();
            $rel     = $ctx->rel($absPath);
            $content = @file_get_contents($absPath);

            if ($content === false || $content === '') {
                continue;
            }

            // Heuristica: procura sinais de que o arquivo lida com upload
            $handlesUpload = preg_match(
                '/\b(UploadedFile|->\s*file\s*\(|->\s*allFiles\s*\(|\$request->file\(|->hasFile\()/i',
                $content
            );

            if (! $handlesUpload) {
                continue;
            }

            $usesUploadStorage = preg_match('/UploadStorage::storeUploadedFile/i', $content) === 1;
            $usesMoveOrStore   = preg_match('/->\s*(store|storeAs|move)\s*\(/i', $content) === 1;
            $hasMimeValidation = preg_match('/(mimes|mimetypes|extensions)\s*:/', $content) === 1
                || preg_match('/finfo_(file|open)|mime_content_type/i', $content) === 1;
            $hasSizeValidation = preg_match('/\bmax\s*:\s*\d+/', $content) === 1;

            if ($usesMoveOrStore && ! $usesUploadStorage) {
                $this->counter++;

                yield new AuditFinding(
                    id:              sprintf('SEC-UPLOAD-STORE-%04d', $this->counter),
                    category:        'SEC-UPLOAD',
                    severity:        AuditFinding::SEVERITY_HIGH,
                    area:            $this->areaFromPath($rel),
                    title:           'Upload salvo sem UploadStorage::storeUploadedFile',
                    recommendation:  'Usar `App\\Support\\UploadStorage::storeUploadedFile($file, $diretorio, $regras)` para centralizar validacao, renomeacao UUID e politica de allowlist. AGENTS.md exige este uso.',
                    file:            $rel,
                    line:            null,
                    context:         null,
                    wafMitigable:    false,
                    deadline:        AuditFinding::defaultDeadline(AuditFinding::SEVERITY_HIGH),
                );
            }

            if ($handlesUpload && ! $hasMimeValidation) {
                $this->counter++;

                yield new AuditFinding(
                    id:              sprintf('SEC-UPLOAD-MIME-%04d', $this->counter),
                    category:        'SEC-UPLOAD',
                    severity:        AuditFinding::SEVERITY_HIGH,
                    area:            $this->areaFromPath($rel),
                    title:           'Upload sem validacao explicita de MIME/extensao por allowlist',
                    recommendation:  'Adicionar regra `mimes:` ou `mimetypes:` (ou `finfo` manual) com allowlist por contexto (imagem, video, PDF). Rejeitar extensoes perigosas (php, phtml, phar, htaccess etc.).',
                    file:            $rel,
                    line:            null,
                    context:         null,
                    wafMitigable:    true,
                    compensatingControl: 'Regra WAF Malicious_Upload detecta divergencia MIME/extensao em bordas.',
                    deadline:        AuditFinding::defaultDeadline(AuditFinding::SEVERITY_HIGH),
                );
            }

            if ($handlesUpload && ! $hasSizeValidation) {
                $this->counter++;

                yield new AuditFinding(
                    id:              sprintf('SEC-UPLOAD-SIZE-%04d', $this->counter),
                    category:        'SEC-UPLOAD',
                    severity:        AuditFinding::SEVERITY_MEDIUM,
                    area:            $this->areaFromPath($rel),
                    title:           'Upload sem limite explicito de tamanho',
                    recommendation:  'Aplicar `max:TAMANHO_EM_KB` no FormRequest/Validator para evitar DoS por upload grande.',
                    file:            $rel,
                    line:            null,
                    context:         null,
                    wafMitigable:    false,
                    deadline:        AuditFinding::defaultDeadline(AuditFinding::SEVERITY_MEDIUM),
                );
            }
        }
    }
}
