<?php

namespace App\Services\Waf;

/**
 * Exceção lançada quando o WafParser encontra JSON inválido.
 *
 * Spec: .kiro/specs/waf-e-auditoria-seguranca
 * Requisito: 20.3
 */
class WafParseException extends \RuntimeException
{
}
