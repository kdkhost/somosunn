<?php

/**
 * ============================================================
 * PROPRIEDADE INTELECTUAL E DIREITOS AUTORAIS
 * ============================================================
 *
 * @autor marcelo-brad rj
 * @contato
 * Tel: 21 981325441
 * WhatsApp: 21 98132-5441
 * Email: contato@kdkhost.com.br
 * Telegram: @MARCELO_BRAD
 * Instagram: @marcelobradrj
 *
 * ============================================================
 *
 * Sistema UNN - Contrato AuditLogInterface
 *
 * Define a API publica do servico de auditoria responsavel por registrar
 * eventos criticos do sistema (login, logout, password change, config
 * change, file upload/delete, payment, webhook, admin action, permission
 * change), consultar registros com filtros e aplicar retencao
 * configuravel.
 *
 * Spec: .kiro/specs/advanced-security-performance
 * Requirements: 6.1, 6.2, 6.3, 6.4, 6.5, 6.6, 6.7
 */

namespace App\Contracts;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Model;

interface AuditLogInterface
{
    /**
     * Registra um evento de auditoria. O dispatch ocorre via job na queue
     * para nao impactar response time. Em caso de falha de dispatch o
     * erro e logado e a operacao original NAO e interrompida.
     *
     * @param string                                   $action     Tipo do evento (constantes ACTION_*)
     * @param \Illuminate\Database\Eloquent\Model|null $target     Entidade alvo (opcional)
     * @param array<string, mixed>                     $oldValues  Valores anteriores (para diff)
     * @param array<string, mixed>                     $newValues  Valores novos (para diff)
     * @param array<string, mixed>                     $meta       Metadados adicionais
     */
    public function log(
        string $action,
        ?Model $target = null,
        array $oldValues = [],
        array $newValues = [],
        array $meta = []
    ): void;

    /**
     * Consulta registros de audit_logs com filtros e paginacao.
     * Filtros suportados: date_from, date_to, user_id, action,
     * target_type, target_id.
     *
     * @param array<string, mixed> $filters
     */
    public function query(array $filters = [], int $perPage = 50): LengthAwarePaginator;

    /**
     * Remove registros com created_at < (now - $retentionDays). Le a chave
     * `audit_retention_days` da tabela settings (sobrepoe parametro se
     * configurada). Retorna quantidade de registros removidos.
     */
    public function purgeOld(int $retentionDays = 90): int;
}
