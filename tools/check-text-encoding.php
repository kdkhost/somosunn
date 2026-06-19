<?php

declare(strict_types=1);

/**
 * Checks text files for UTF-8 BOM and common mojibake byte sequences.
 *
 * Usage:
 *   php tools/check-text-encoding.php
 */

$projectRoot = realpath(__DIR__ . DIRECTORY_SEPARATOR . '..');
if (!is_string($projectRoot) || $projectRoot === '') {
    fwrite(STDERR, "Unable to resolve project root.\n");
    exit(2);
}

$skipDirs = [
    '.git',
    'vendor',
    'storage',
    'bootstrap' . DIRECTORY_SEPARATOR . 'cache',
    'graphify-out',
    'node_modules',
    'public' . DIRECTORY_SEPARATOR . 'build',
];

$textExtensions = [
    'php',
    'css',
    'js',
    'json',
    'md',
    'txt',
    'xml',
    'yml',
    'yaml',
    'env',
    'example',
];

$bom = "\xEF\xBB\xBF";
$mojibakePatterns = [
    'mojibake-a-acute' => "\xC3\x83\xC2\xA1",
    'mojibake-a-grave' => "\xC3\x83\xC2\xA0",
    'mojibake-a-circ' => "\xC3\x83\xC2\xA2",
    'mojibake-a-tilde' => "\xC3\x83\xC2\xA3",
    'mojibake-c-cedilla' => "\xC3\x83\xC2\xA7",
    'mojibake-e-acute' => "\xC3\x83\xC2\xA9",
    'mojibake-e-circ' => "\xC3\x83\xC2\xAA",
    'mojibake-i-acute' => "\xC3\x83\xC2\xAD",
    'mojibake-o-acute' => "\xC3\x83\xC2\xB3",
    'mojibake-o-circ' => "\xC3\x83\xC2\xB4",
    'mojibake-o-tilde' => "\xC3\x83\xC2\xB5",
    'mojibake-u-acute' => "\xC3\x83\xC2\xBA",
    'mojibake-u-umlaut' => "\xC3\x83\xC2\xBC",
    'mojibake-dash-quote-prefix' => "\xC3\xA2\xE2\x82\xAC",
    'mojibake-nbsp' => "\xC3\x82\xC2\xA0",
    'replacement-char' => "\xEF\xBF\xBD",
];

$findings = [];

$iterator = new RecursiveIteratorIterator(
    new RecursiveCallbackFilterIterator(
        new RecursiveDirectoryIterator($projectRoot, FilesystemIterator::SKIP_DOTS),
        function (SplFileInfo $current) use ($skipDirs): bool {
            if (!$current->isDir()) {
                return true;
            }

            $path = $current->getPathname();
            foreach ($skipDirs as $skip) {
                if (str_contains($path, DIRECTORY_SEPARATOR . $skip) || str_ends_with($path, DIRECTORY_SEPARATOR . $skip)) {
                    return false;
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

    if ($fileInfo->getSize() > 2 * 1024 * 1024) {
        continue;
    }

    $path = $fileInfo->getPathname();
    $name = $fileInfo->getFilename();
    $ext = strtolower((string) pathinfo($path, PATHINFO_EXTENSION));
    $isBlade = str_ends_with($name, '.blade.php');

    if (!$isBlade && !in_array($ext, $textExtensions, true)) {
        continue;
    }

    $content = @file_get_contents($path);
    if (!is_string($content)) {
        continue;
    }

    $rel = ltrim(str_replace($projectRoot, '', $path), "\\/");

    if (strncmp($content, $bom, 3) === 0) {
        $findings[] = [$rel, 1, 'utf8-bom'];
    }

    $lines = preg_split('/\R/', $content);
    if (!is_array($lines)) {
        $lines = [$content];
    }

    foreach ($lines as $lineNumber => $line) {
        foreach ($mojibakePatterns as $label => $needle) {
            if (str_contains($line, $needle)) {
                $findings[] = [$rel, $lineNumber + 1, $label];
            }
        }
    }
}

if ($findings !== []) {
    fwrite(STDERR, "ERRO: Foram encontrados problemas de codificacao de texto:\n\n");
    foreach ($findings as [$file, $line, $label]) {
        fwrite(STDERR, "- {$file}:{$line} ({$label})\n");
    }
    fwrite(STDERR, "\nCorrija os arquivos como UTF-8 sem BOM e sem mojibake.\n");
    exit(1);
}

fwrite(STDOUT, "OK: Nenhum BOM ou mojibake comum encontrado.\n");
exit(0);
