<?php

namespace App\Services\Waf\Scanners;

/**
 * Contexto compartilhado entre scanners da auditoria de seguranca.
 *
 * Carrega raiz do projeto, lista de paths alvo (relativos a raiz) e
 * filtros configuraveis (extensoes, ignores).
 *
 * Spec: .kiro/specs/waf-e-auditoria-seguranca
 * Requisito: 1.1
 */
class AuditContext
{
    /**
     * @param string        $basePath Diretorio raiz do projeto (sem barra final).
     * @param array<string> $paths    Paths alvo (relativos a raiz).
     * @param array<string> $ignoredDirectories
     */
    public function __construct(
        public readonly string $basePath,
        public readonly array  $paths = [
            'app',
            'routes',
            'config',
            'database',
            'resources/views',
            'resources/js',
            'public',
        ],
        public readonly array $ignoredDirectories = [
            'vendor',
            'node_modules',
            'storage',
            '.git',
            '.phpunit.cache',
            '.kiro',
            'public/storage',
        ],
    ) {}

    /**
     * Retorna o caminho absoluto a partir de um caminho relativo.
     */
    public function abs(string $relative): string
    {
        return rtrim($this->basePath, '/\\') . DIRECTORY_SEPARATOR . ltrim($relative, '/\\');
    }

    /**
     * Retorna um caminho relativo a raiz (para exibicao em relatorios).
     */
    public function rel(string $absolute): string
    {
        $base = rtrim($this->basePath, '/\\');

        if (strncmp($absolute, $base, strlen($base)) === 0) {
            $relative = ltrim(substr($absolute, strlen($base)), '/\\');

            return str_replace('\\', '/', $relative);
        }

        return str_replace('\\', '/', $absolute);
    }

    /**
     * Verifica se um caminho absoluto esta em um diretorio ignorado.
     */
    public function isIgnored(string $absolute): bool
    {
        $normalized = str_replace('\\', '/', $absolute);
        $base       = str_replace('\\', '/', rtrim($this->basePath, '/\\'));

        foreach ($this->ignoredDirectories as $ignored) {
            $needle = $base . '/' . trim($ignored, '/') . '/';

            if (stripos($normalized, $needle) !== false) {
                return true;
            }
        }

        return false;
    }
}
