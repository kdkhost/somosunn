<?php

namespace App\Services\Waf\Scanners\Contracts;

use App\Services\Waf\Scanners\AuditContext;
use App\Services\Waf\Scanners\AuditFinding;

/**
 * Contrato base para scanners da auditoria de seguranca.
 *
 * Cada scanner recebe um AuditContext (paths, filtros) e produz
 * zero ou mais AuditFindings.
 *
 * Spec: .kiro/specs/waf-e-auditoria-seguranca
 * Requisito: 1.1
 */
interface Scanner
{
    /**
     * Identificador curto do scanner (ex.: "php-ast", "blade", "route").
     */
    public function id(): string;

    /**
     * Descricao curta exibida no relatorio.
     */
    public function label(): string;

    /**
     * Executa a analise e retorna os findings encontrados.
     *
     * @return iterable<AuditFinding>
     */
    public function scan(AuditContext $ctx): iterable;
}
