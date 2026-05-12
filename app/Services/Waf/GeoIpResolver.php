<?php

namespace App\Services\Waf;

/**
 * Resolve pais (ISO-2) a partir de IP.
 *
 * Degradacao graciosa: quando o arquivo GeoLite2 nao esta disponivel,
 * retorna null e o dashboard mostra "desconhecido" sem quebrar nada.
 *
 * Spec: .kiro/specs/waf-e-auditoria-seguranca
 * Requisito: 11.7
 */
final class GeoIpResolver
{
    public function resolve(string $ip): ?string
    {
        // Implementacao stub: a integracao com MaxMind GeoLite2 sera
        // habilitada no rollout (Fase 7). Retornar null e seguro e
        // nao afeta a decisao do engine.
        return null;
    }
}
