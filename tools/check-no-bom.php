<?php

declare(strict_types=1);

/**
 * Verifica se existe UTF-8 BOM (EF BB BF) em arquivos do projeto.
 *
 * Uso:
 *   php tools/check-no-bom.php
 *
 * Saida:
 *   - Lista os arquivos com BOM e retorna exit code 1
 *   - Caso nao encontre BOM, retorna exit code 0
 */

$projectRoot = realpath(__DIR__ . DIRECTORY_SEPARATOR . "..");
if (!is_string($projectRoot) || $projectRoot === "") {
    fwrite(STDERR, "Nao foi possivel resolver a raiz do projeto.\n");
    exit(2);
}

$skipDirs = [
    ".git",
    "vendor",
    "storage",
    "bootstrap" . DIRECTORY_SEPARATOR . "cache",
    "node_modules",
];

$binaryExtensions = [
    "png", "jpg", "jpeg", "gif", "webp", "ico",
    "pdf",
    "zip", "gz", "7z", "rar",
    "ttf", "otf", "woff", "woff2", "eot",
    "mp4", "mp3", "wav", "mov", "avi", "mkv",
    "exe", "dll", "so", "dylib",
];

$bom = "\xEF\xBB\xBF";
$found = [];

$iterator = new RecursiveIteratorIterator(
    new RecursiveCallbackFilterIterator(
        new RecursiveDirectoryIterator($projectRoot, FilesystemIterator::SKIP_DOTS),
        function (SplFileInfo $current) use ($skipDirs): bool {
            if ($current->isDir()) {
                $path = $current->getPathname();
                foreach ($skipDirs as $skip) {
                    if (str_contains($path, DIRECTORY_SEPARATOR . $skip) || str_ends_with($path, DIRECTORY_SEPARATOR . $skip)) {
                        return false;
                    }
                }
            }

            return true;
        }
    ),
    RecursiveIteratorIterator::LEAVES_ONLY
);

foreach ($iterator as $fileInfo) {
    if (!$fileInfo instanceof SplFileInfo || !$fileInfo->isFile()) {
        continue;
    }

    $path = $fileInfo->getPathname();

    $ext = strtolower((string) pathinfo($path, PATHINFO_EXTENSION));
    if ($ext !== "" && in_array($ext, $binaryExtensions, true)) {
        continue;
    }

    $h = @fopen($path, "rb");
    if (!is_resource($h)) {
        continue;
    }

    $head = (string) fread($h, 3);
    fclose($h);

    if ($head === $bom) {
        $rel = ltrim(str_replace($projectRoot, "", $path), "\\/");
        $found[] = $rel === "" ? $path : $rel;
    }
}

sort($found);

if (count($found) > 0) {
    fwrite(STDERR, "ERRO: Foram encontrados arquivos com UTF-8 BOM (EF BB BF):\n\n");
    foreach ($found as $p) {
        fwrite(STDERR, "- {$p}\n");
    }
    fwrite(STDERR, "\nRe-salve os arquivos como UTF-8 sem BOM e rode novamente.\n");
    exit(1);
}

fwrite(STDOUT, "OK: Nenhum arquivo com UTF-8 BOM encontrado.\n");
exit(0);
