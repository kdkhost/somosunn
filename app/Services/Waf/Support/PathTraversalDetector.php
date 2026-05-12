<?php

namespace App\Services\Waf\Support;

/**
 * Deteccao de padroes de path traversal.
 *
 * Spec: .kiro/specs/waf-e-auditoria-seguranca
 * Requisitos: 5.1, 5.2, Property 13
 */
class PathTraversalDetector
{
    private const SUSPICIOUS_ABS_PREFIXES = [
        '/etc/', '/proc/', '/sys/', '/var/log/', '/root/',
        'c:\\', 'c:/', '\\\\?\\',
    ];

    public static function detect(string $input): bool
    {
        if ($input === '') {
            return false;
        }

        $lower = strtolower($input);

        // Bytes nulos literais ou codificados
        if (str_contains($input, "\x00")) {
            return true;
        }

        if (str_contains($lower, '%00')) {
            return true;
        }

        // Sequencias de traversal
        $needles = [
            '../', '..\\', '%2e%2e/', '%2e%2e\\',
            '..%2f', '..%5c', '%252e%252e', '....//', '....\\\\',
        ];

        foreach ($needles as $needle) {
            if (str_contains($lower, $needle)) {
                return true;
            }
        }

        foreach (self::SUSPICIOUS_ABS_PREFIXES as $prefix) {
            if (str_starts_with($lower, $prefix)) {
                return true;
            }
        }

        return false;
    }
}
