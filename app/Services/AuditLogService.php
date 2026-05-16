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
 * Sistema UNN - AuditLogService
 *
 * Servico responsavel por:
 *   - log()       Despachar registro de auditoria como job na queue
 *                 (WriteAuditLogJob, queue 'default') preservando o
 *                 response time da request original. Coleta
 *                 automaticamente user_id, ip, user_agent e request_id
 *                 (header X-Request-Id ou UUID gerado). Em caso de
 *                 falha no dispatch da queue aplica fallback sincrono
 *                 via DB::table; se ate o fallback falhar, apenas loga
 *                 no canal stack e NUNCA propaga excecao.
 *   - query()     Consultar audit_logs com filtros (date_from,
 *                 date_to, user_id, action, target_type, target_id)
 *                 com paginacao ordenada por created_at DESC.
 *   - purgeOld()  Remover registros mais antigos que o periodo de
 *                 retencao (parametro ou setting `audit_retention_days`,
 *                 default 90 dias).
 *
 * Padrao fail-safe: nenhuma falha do audit logger pode interromper
 * a operacao original do usuario.
 *
 * Spec: .kiro/specs/advanced-security-performance
 * Requirements: 6.1, 6.2, 6.3, 6.4, 6.5, 6.6, 6.7
 */

namespace App\Services;

use App\Contracts\AuditLogInterface;
use App\Jobs\WriteAuditLogJob;
use App\Models\AuditLog;
use App\Models\Setting;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class AuditLogService implements AuditLogInterface
{
    public const ACTION_LOGIN = 'login';
    public const ACTION_LOGOUT = 'logout';
    public const ACTION_PASSWORD_CHANGE = 'password_change';
    public const ACTION_CONFIG_CHANGE = 'config_change';
    public const ACTION_FILE_UPLOAD = 'file_upload';
    public const ACTION_FILE_DELETE = 'file_delete';
    public const ACTION_PAYMENT = 'payment';
    public const ACTION_WEBHOOK = 'webhook';
    public const ACTION_ADMIN_ACTION = 'admin_action';
    public const ACTION_PERMISSION_CHANGE = 'permission_change';

    /**
     * Queue padrao para dispatch dos jobs de auditoria
     * (alinhada com QueueManagerService::QUEUE_DEFAULT).
     */
    private const AUDIT_QUEUE = 'default';

    /**
     * Retencao padrao (dias) caso a setting `audit_retention_days`
     * nao esteja configurada.
     */
    private const DEFAULT_RETENTION_DAYS = 90;

    /**
     * Limite de tamanho para user_agent (alinhado a coluna VARCHAR(500)).
     */
    private const USER_AGENT_MAX_LENGTH = 500;

    public function log(
        string $action,
        ?Model $target = null,
        array $oldValues = [],
        array $newValues = [],
        array $meta = []
    ): void {
        $payload = null;

        try {
            $payload = $this->buildPayload($action, $target, $oldValues, $newValues, $meta);

            WriteAuditLogJob::dispatch($payload)->onQueue(self::AUDIT_QUEUE);
        } catch (\Throwable $dispatchException) {
            // Fallback 1: tenta gravar sincrono via DB::table.
            Log::channel('stack')->warning('AuditLogService.log: dispatch da queue falhou, aplicando fallback sincrono', [
                'exception' => $dispatchException->getMessage(),
                'action' => $action,
            ]);

            try {
                if ($payload === null) {
                    $payload = $this->buildPayload($action, $target, $oldValues, $newValues, $meta);
                }
                $this->writeSync($payload);
            } catch (\Throwable $syncException) {
                // Fallback 2: ate o sincrono falhou. Apenas loga; jamais propaga.
                Log::channel('stack')->error('AuditLogService.log: fallback sincrono tambem falhou', [
                    'exception' => $syncException->getMessage(),
                    'action' => $action,
                    'target_type' => $target ? get_class($target) : null,
                ]);
            }
        }
    }

    public function query(array $filters = [], int $perPage = 50): LengthAwarePaginator
    {
        $perPage = max(1, $perPage);

        $query = AuditLog::query()->orderBy('created_at', 'desc');

        if (! empty($filters['date_from'])) {
            $query->where('created_at', '>=', $filters['date_from']);
        }

        if (! empty($filters['date_to'])) {
            $query->where('created_at', '<=', $filters['date_to']);
        }

        if (! empty($filters['user_id'])) {
            $query->where('user_id', (int) $filters['user_id']);
        }

        if (! empty($filters['action'])) {
            $query->where('action', (string) $filters['action']);
        }

        if (! empty($filters['target_type'])) {
            $query->where('target_type', (string) $filters['target_type']);
        }

        if (! empty($filters['target_id'])) {
            $query->where('target_id', (int) $filters['target_id']);
        }

        return $query->paginate($perPage);
    }

    public function purgeOld(int $retentionDays = self::DEFAULT_RETENTION_DAYS): int
    {
        try {
            $configured = Setting::get('audit_retention_days', null);

            if ($configured !== null && is_numeric($configured)) {
                $retentionDays = (int) $configured;
            }

            $retentionDays = max(1, $retentionDays);

            $cutoff = Carbon::now()->subDays($retentionDays);

            return (int) AuditLog::query()
                ->where('created_at', '<', $cutoff)
                ->delete();
        } catch (\Throwable $e) {
            Log::channel('stack')->error('AuditLogService.purgeOld falhou', [
                'retention_days' => $retentionDays,
                'exception' => $e->getMessage(),
            ]);

            return 0;
        }
    }

    /**
     * Monta o payload pronto para insercao em audit_logs, capturando
     * contexto da request atual (auth, ip, ua, request_id).
     *
     * @param  array<string, mixed> $oldValues
     * @param  array<string, mixed> $newValues
     * @param  array<string, mixed> $meta
     * @return array<string, mixed>
     */
    private function buildPayload(
        string $action,
        ?Model $target,
        array $oldValues,
        array $newValues,
        array $meta
    ): array {
        return [
            'user_id' => $this->resolveUserId(),
            'ip_address' => $this->resolveIpAddress(),
            'user_agent' => $this->resolveUserAgent(),
            'action' => $action,
            'target_type' => $target ? get_class($target) : null,
            'target_id' => $target && $target->getKey() !== null ? (int) $target->getKey() : null,
            'old_values' => empty($oldValues) ? null : $oldValues,
            'new_values' => empty($newValues) ? null : $newValues,
            'request_id' => $this->resolveRequestId(),
            'metadata' => empty($meta) ? null : $meta,
            'created_at' => Carbon::now(),
        ];
    }

    /**
     * Fallback sincrono: insere o registro diretamente via DB::table.
     * Usado quando o dispatch da queue falha. Pode ainda lancar
     * excecao, que e capturada pelo chamador (log()).
     *
     * @param array<string, mixed> $payload
     */
    private function writeSync(array $payload): void
    {
        foreach (['old_values', 'new_values', 'metadata'] as $jsonField) {
            if (! array_key_exists($jsonField, $payload)) {
                continue;
            }
            $value = $payload[$jsonField];
            if ($value === null || $value === '') {
                $payload[$jsonField] = null;
                continue;
            }
            if (is_array($value)) {
                $payload[$jsonField] = json_encode(
                    $value,
                    JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
                );
            }
        }

        if (isset($payload['created_at']) && $payload['created_at'] instanceof \DateTimeInterface) {
            $payload['created_at'] = $payload['created_at']->format('Y-m-d H:i:s');
        }

        DB::table('audit_logs')->insert($payload);
    }

    private function resolveUserId(): ?int
    {
        try {
            if (function_exists('auth')) {
                $id = auth()->id();
                return $id !== null ? (int) $id : null;
            }
        } catch (\Throwable $e) {
            // sem contexto de auth disponivel
        }

        return null;
    }

    private function resolveIpAddress(): string
    {
        try {
            if (function_exists('request')) {
                $ip = request()->ip();
                if (is_string($ip) && $ip !== '') {
                    return $ip;
                }
            }
        } catch (\Throwable $e) {
            // sem request HTTP (CLI)
        }

        return '0.0.0.0';
    }

    private function resolveUserAgent(): ?string
    {
        try {
            if (function_exists('request')) {
                $ua = request()->userAgent();
                if (is_string($ua) && $ua !== '') {
                    return mb_substr($ua, 0, self::USER_AGENT_MAX_LENGTH);
                }
            }
        } catch (\Throwable $e) {
            // sem request HTTP (CLI)
        }

        return null;
    }

    private function resolveRequestId(): string
    {
        try {
            if (function_exists('request')) {
                $headerId = request()->header('X-Request-Id');
                if (is_string($headerId) && $headerId !== '') {
                    return mb_substr($headerId, 0, 36);
                }
            }
        } catch (\Throwable $e) {
            // sem request HTTP, gera UUID novo abaixo
        }

        return (string) Str::uuid();
    }
}
