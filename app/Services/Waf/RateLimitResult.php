<?php

namespace App\Services\Waf;

/**
 * Resultado de uma checagem do RateLimitStore.
 *
 * Spec: .kiro/specs/waf-e-auditoria-seguranca
 * Requisito: 11.2
 */
final class RateLimitResult
{
    public function __construct(
        public readonly bool $allowed,
        public readonly int  $remaining,
        public readonly int  $retryAfter,
    ) {}
}
