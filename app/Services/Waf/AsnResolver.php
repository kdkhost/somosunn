<?php

namespace App\Services\Waf;

/**
 * Resolve ASN (numero) a partir de IP.
 *
 * Degradacao graciosa: quando o arquivo MaxMind ASN nao esta disponivel,
 * retorna null sem quebrar.
 *
 * Spec: .kiro/specs/waf-e-auditoria-seguranca
 * Requisito: 11.7
 */
final class AsnResolver
{
    public function resolve(string $ip): ?int
    {
        // Stub (ver GeoIpResolver).
        return null;
    }
}
