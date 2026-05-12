<?php

namespace App\Services\Waf\Scanners;

use App\Services\Waf\Scanners\Contracts\Scanner;
use FilesystemIterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

/**
 * Base de conveniencia para scanners: iteracao recursiva com filtros.
 *
 * Spec: .kiro/specs/waf-e-auditoria-seguranca
 */
abstract class AbstractScanner implements Scanner
{
    /**
     * Itera recursivamente os arquivos cuja extensao esteja na lista,
     * respeitando os ignores do AuditContext.
     *
     * @return iterable<\SplFileInfo>
     */
    protected function iterateFiles(
        AuditContext $ctx,
        array        $extensions,
        ?array       $onlyPaths = null
    ): iterable {
        $targets = $onlyPaths ?? $ctx->paths;

        foreach ($targets as $relative) {
            $abs = $ctx->abs($relative);

            if (! is_dir($abs) && ! is_file($abs)) {
                continue;
            }

            if (is_file($abs)) {
                if ($this->matchesExtension($abs, $extensions) && ! $ctx->isIgnored($abs)) {
                    yield new \SplFileInfo($abs);
                }

                continue;
            }

            $iterator = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($abs, FilesystemIterator::SKIP_DOTS),
                RecursiveIteratorIterator::LEAVES_ONLY
            );

            foreach ($iterator as $file) {
                /** @var \SplFileInfo $file */
                if (! $file->isFile()) {
                    continue;
                }

                $path = $file->getPathname();

                if ($ctx->isIgnored($path)) {
                    continue;
                }

                if (! $this->matchesExtension($path, $extensions)) {
                    continue;
                }

                yield $file;
            }
        }
    }

    protected function matchesExtension(string $path, array $extensions): bool
    {
        foreach ($extensions as $ext) {
            if (strtolower(substr($path, -strlen($ext))) === strtolower($ext)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Extrai um trecho de contexto (linha-alvo +/- raio).
     */
    protected function excerpt(string $filePath, int $line, int $radius = 2): string
    {
        $content = @file_get_contents($filePath);

        if ($content === false) {
            return '';
        }

        $lines = preg_split('/\R/', $content) ?: [];
        $from  = max(0, $line - 1 - $radius);
        $to    = min(count($lines) - 1, $line - 1 + $radius);
        $out   = [];

        for ($i = $from; $i <= $to; $i++) {
            $marker = ($i + 1 === $line) ? '>> ' : '   ';
            $out[]  = sprintf('%s%4d: %s', $marker, $i + 1, $lines[$i] ?? '');
        }

        return implode("\n", $out);
    }

    /**
     * Define a area funcional a partir do caminho do arquivo.
     */
    protected function areaFromPath(string $relPath): string
    {
        $lp = strtolower(str_replace('\\', '/', $relPath));

        // Rotas
        if (str_starts_with($lp, 'routes/api.php')) {
            return 'API';
        }
        if (str_starts_with($lp, 'routes/web.php')) {
            return 'Area Publica';
        }

        if (str_contains($lp, 'auth') || str_contains($lp, 'logincontroller') || str_contains($lp, 'resetpassword')) {
            return 'Auth';
        }
        if (str_contains($lp, 'upload') || str_contains($lp, 'chunkedupload') || str_contains($lp, 'filepond')) {
            return 'Uploads';
        }
        if (str_contains($lp, 'webhook') || str_contains($lp, 'sumup')) {
            return 'Webhooks';
        }
        if (str_contains($lp, 'impersonate')) {
            return 'Impersonacao';
        }
        if (str_starts_with($lp, 'app/http/controllers/api/') || str_contains($lp, 'apicontroller') || str_contains($lp, 'routes/api.php')) {
            return 'API';
        }
        if (str_contains($lp, 'app/http/controllers/admin/') || str_contains($lp, 'admin.layouts') || str_contains($lp, 'resources/views/admin/')) {
            return 'Painel Admin';
        }
        if (str_contains($lp, 'resources/views/panel/') || str_contains($lp, 'panel/layouts')) {
            return 'Painel Novo';
        }
        if (str_starts_with($lp, 'config/')) {
            return 'Config';
        }
        if (str_ends_with($lp, '.blade.php')) {
            return 'Blade';
        }
        if (str_contains($lp, 'middleware') && (str_contains($lp, 'header') || str_contains($lp, 'security'))) {
            return 'Headers';
        }
        if (str_starts_with($lp, 'resources/views/')) {
            return 'Area Publica';
        }
        if (str_starts_with($lp, 'app/http/controllers/')) {
            return 'Area Publica';
        }

        return 'Outros';
    }
}
