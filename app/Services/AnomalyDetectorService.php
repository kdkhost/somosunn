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
 * Sistema UNN - AnomalyDetectorService
 *
 * Detecta padroes anomalos de uso (logins falhos em rajada, flood
 * de uploads, webhooks invalidos repetidos) usando contadores em
 * janela deslizante mantidos no driver de cache configurado
 * (file-based no ambiente cPanel/LiteSpeed).
 *
 * Cada anomalia detectada e persistida em `anomaly_events`, com
 * notificacao por email ao Superadmin e (opcionalmente) bloqueio
 * automatico do IP via tabela waf_ip_blocklist / rate_limit_blocks.
 *
 * Principios fail-safe (Requirement 11.7):
 *   - Falha ao incrementar contador no cache nao interrompe o
 *     fluxo do request original (apenas warning no canal security).
 *   - Falha de notificacao mantem o anomaly registrado (notified_at
 *     fica null).
 *   - Auto-block sendo opcional, falha de WAF nao impede o registro.
 *
 * Spec: .kiro/specs/advanced-security-performance
 * Requirements: 11.1, 11.2, 11.3, 11.4, 11.5, 11.6, 11.7
 */

namespace App\Services;

use App\Contracts\AnomalyDetectorInterface;
use App\Jobs\SendGenericTemplateEmail;
use App\Models\AnomalyEvent;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Throwable;

class AnomalyDetectorService implements AnomalyDetectorInterface
{
    public const TYPE_FAILED_LOGINS = 'failed_logins';
    public const TYPE_UPLOAD_FLOOD = 'upload_flood';
    public const TYPE_INVALID_WEBHOOKS = 'invalid_webhooks';

    private const DEFAULT_LOGIN_THRESHOLD = 10;
    private const DEFAULT_UPLOAD_THRESHOLD = 20;
    private const DEFAULT_WEBHOOK_THRESHOLD = 5;

    private const WINDOW_LOGIN_MINUTES = 5;
    private const WINDOW_UPLOAD_MINUTES = 10;
    private const WINDOW_WEBHOOK_MINUTES = 10;

    /** Duracao em minutos do bloqueio automatico via WAF (quando habilitado). */
    private const AUTO_BLOCK_DURATION_MINUTES = 60;

    /** Canal de log para todas as operacoes do servico. */
    private const LOG_CHANNEL = 'security';

    /** Prefixo das chaves de cache para garantir namespacing. */
    private const CACHE_PREFIX = 'unn:anomaly:';

    /**
     * Registra uma tentativa de login. Apenas falhas sao rastreadas.
     *
     * @inheritDoc
     */
    public function recordLoginAttempt(string $ip, bool $success): void
    {
        if ($success) {
            return;
        }

        $ip = trim($ip);
        if ($ip === '') {
            return;
        }

        $cacheKey = self::CACHE_PREFIX . 'login_failures:' . $ip;
        $count = $this->pushTimestamp($cacheKey, self::WINDOW_LOGIN_MINUTES);

        $threshold = $this->loginThreshold();
        if ($count > $threshold) {
            $this->flagAnomaly(
                self::TYPE_FAILED_LOGINS,
                $ip,
                null,
                null,
                $threshold,
                $count,
                self::WINDOW_LOGIN_MINUTES
            );
        }
    }

    /**
     * Registra um upload realizado pelo usuario informado.
     *
     * @inheritDoc
     */
    public function recordUpload(int $userId): void
    {
        if ($userId <= 0) {
            return;
        }

        $cacheKey = self::CACHE_PREFIX . 'uploads:' . $userId;
        $count = $this->pushTimestamp($cacheKey, self::WINDOW_UPLOAD_MINUTES);

        $threshold = $this->uploadThreshold();
        if ($count > $threshold) {
            $this->flagAnomaly(
                self::TYPE_UPLOAD_FLOOD,
                null,
                $userId,
                null,
                $threshold,
                $count,
                self::WINDOW_UPLOAD_MINUTES
            );
        }
    }

    /**
     * Registra um callback de webhook recebido. Apenas invalidos
     * contam para a deteccao.
     *
     * @inheritDoc
     */
    public function recordWebhook(string $source, bool $valid): void
    {
        if ($valid) {
            return;
        }

        $source = trim($source);
        if ($source === '') {
            return;
        }

        $cacheKey = self::CACHE_PREFIX . 'invalid_webhooks:' . $source;
        $count = $this->pushTimestamp($cacheKey, self::WINDOW_WEBHOOK_MINUTES);

        $threshold = $this->webhookThreshold();
        if ($count > $threshold) {
            $this->flagAnomaly(
                self::TYPE_INVALID_WEBHOOKS,
                null,
                null,
                $source,
                $threshold,
                $count,
                self::WINDOW_WEBHOOK_MINUTES
            );
        }
    }

    /**
     * Retorna anomalias flaggeadas nas ultimas 24h ainda nao
     * auto-bloqueadas, formatadas para exibicao no painel.
     *
     * @inheritDoc
     */
    public function checkThresholds(): array
    {
        try {
            $since = Carbon::now()->subHours(24);

            $rows = AnomalyEvent::query()
                ->where('created_at', '>=', $since)
                ->where('auto_blocked', false)
                ->orderByDesc('created_at')
                ->limit(100)
                ->get();

            $result = [];
            foreach ($rows as $row) {
                $result[] = [
                    'id' => (int) $row->id,
                    'type' => (string) $row->type,
                    'source_ip' => $row->source_ip,
                    'source_user_id' => $row->source_user_id,
                    'source_identifier' => $row->source_identifier,
                    'threshold_value' => (int) $row->threshold_value,
                    'actual_value' => (int) $row->actual_value,
                    'window_minutes' => (int) $row->window_minutes,
                    'notified_at' => $row->notified_at?->toIso8601String(),
                    'auto_blocked' => (bool) $row->auto_blocked,
                    'created_at' => $row->created_at?->toIso8601String(),
                ];
            }

            return $result;
        } catch (Throwable $e) {
            Log::channel(self::LOG_CHANNEL)->warning('AnomalyDetector::checkThresholds falhou', [
                'exception' => $e->getMessage(),
            ]);

            return [];
        }
    }

    /**
     * Retorna os thresholds configurados (settings com fallback
     * para os defaults). Chaves: login, upload, webhook, auto_block.
     *
     * @inheritDoc
     */
    public function getThresholds(): array
    {
        return [
            'login' => $this->loginThreshold(),
            'upload' => $this->uploadThreshold(),
            'webhook' => $this->webhookThreshold(),
            'auto_block' => $this->autoBlockEnabled(),
        ];
    }

    // ===================================================================
    // Internals
    // ===================================================================

    /**
     * Adiciona um timestamp na lista deslizante associada a $key,
     * removendo entradas mais antigas que a janela. Retorna a
     * contagem atual de eventos dentro da janela.
     */
    protected function pushTimestamp(string $key, int $windowMinutes): int
    {
        $now = time();
        $cutoff = $now - ($windowMinutes * 60);
        // TTL = janela * 2 segundos (auto-cleanup do cache).
        $ttlSeconds = $windowMinutes * 60 * 2;

        try {
            $existing = Cache::get($key, []);
            if (!is_array($existing)) {
                $existing = [];
            }

            // Remove entradas fora da janela.
            $pruned = array_values(array_filter(
                $existing,
                static fn ($ts) => is_numeric($ts) && (int) $ts >= $cutoff
            ));

            $pruned[] = $now;

            Cache::put($key, $pruned, $ttlSeconds);

            return count($pruned);
        } catch (Throwable $e) {
            Log::channel(self::LOG_CHANNEL)->warning(
                'AnomalyDetector::pushTimestamp falhou',
                ['key' => $key, 'exception' => $e->getMessage()]
            );

            return 0;
        }
    }

    /**
     * Persiste a anomalia em `anomaly_events`, dispara notificacao
     * por email ao Superadmin e aplica auto-block opcional via WAF.
     * Em caso de falha de notificacao, mantem o registro com
     * notified_at = null (Requirement 11.7).
     */
    protected function flagAnomaly(
        string $type,
        ?string $sourceIp,
        ?int $sourceUserId,
        ?string $sourceIdentifier,
        int $threshold,
        int $actual,
        int $windowMinutes
    ): void {
        $context = [
            'type' => $type,
            'source_ip' => $sourceIp,
            'source_user_id' => $sourceUserId,
            'source_identifier' => $sourceIdentifier,
            'threshold' => $threshold,
            'actual' => $actual,
            'window_minutes' => $windowMinutes,
        ];

        Log::channel(self::LOG_CHANNEL)->warning('Anomalia detectada', $context);

        $anomaly = null;
        try {
            $anomaly = AnomalyEvent::create([
                'type' => $type,
                'source_ip' => $sourceIp,
                'source_user_id' => $sourceUserId,
                'source_identifier' => $sourceIdentifier,
                'threshold_value' => $threshold,
                'actual_value' => $actual,
                'window_minutes' => $windowMinutes,
                'auto_blocked' => false,
                'created_at' => Carbon::now(),
            ]);
        } catch (Throwable $e) {
            Log::channel(self::LOG_CHANNEL)->error(
                'Falha ao persistir anomaly_event (registro perdido no banco)',
                array_merge($context, ['exception' => $e->getMessage()])
            );

            return;
        }

        $this->sendNotification($anomaly);

        if ($this->autoBlockEnabled() && $sourceIp !== null && $sourceIp !== '') {
            $this->autoBlockIp($anomaly, $sourceIp, $type);
        }
    }

    /**
     * Envia email ao Superadmin avisando da anomalia. Em caso de
     * sucesso, marca notified_at; em caso de falha, registra
     * warning e preserva notified_at = null.
     */
    protected function sendNotification(AnomalyEvent $anomaly): void
    {
        $recipient = $this->resolveSuperadminEmail();
        if ($recipient === null) {
            Log::channel(self::LOG_CHANNEL)->warning(
                'Anomalia sem destinatario Superadmin para notificacao',
                ['anomaly_id' => $anomaly->id, 'type' => $anomaly->type]
            );

            return;
        }

        try {
            $subject = sprintf('[UNN] Anomalia detectada: %s', $this->humanType($anomaly->type));
            $body = $this->buildEmailBody($anomaly);

            SendGenericTemplateEmail::dispatch($recipient, $subject, $body);

            $anomaly->notified_at = Carbon::now();
            $anomaly->save();
        } catch (Throwable $e) {
            Log::channel(self::LOG_CHANNEL)->warning(
                'Falha ao notificar Superadmin (anomaly preservada sem notificacao)',
                [
                    'anomaly_id' => $anomaly->id,
                    'type' => $anomaly->type,
                    'recipient' => $recipient,
                    'exception' => $e->getMessage(),
                ]
            );
            // notified_at permanece null (Requirement 11.7).
        }
    }

    /**
     * Aplica bloqueio automatico do IP via tabela waf_ip_blocklist
     * (preferida) ou rate_limit_blocks (fallback). Atualiza
     * auto_blocked no AnomalyEvent em caso de sucesso.
     */
    protected function autoBlockIp(AnomalyEvent $anomaly, string $ip, string $reasonType): void
    {
        $blocked = false;

        try {
            if (Schema::hasTable('waf_ip_blocklist')) {
                $blocked = $this->insertWafBlocklist($ip, $reasonType);
            }

            if (!$blocked && Schema::hasTable('rate_limit_blocks')) {
                $blocked = $this->insertRateLimitBlock($ip, $reasonType);
            }

            if ($blocked) {
                $anomaly->auto_blocked = true;
                $anomaly->save();
            } else {
                Log::channel(self::LOG_CHANNEL)->warning(
                    'Auto-block solicitado mas nenhuma tabela WAF disponivel',
                    ['anomaly_id' => $anomaly->id, 'ip' => $ip]
                );
            }
        } catch (Throwable $e) {
            Log::channel(self::LOG_CHANNEL)->warning('Auto-block falhou', [
                'anomaly_id' => $anomaly->id,
                'ip' => $ip,
                'exception' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Insere o IP na tabela waf_ip_blocklist como /32 (IPv4) ou
     * /128 (IPv6). Em caso de falha, retorna false.
     */
    protected function insertWafBlocklist(string $ip, string $reasonType): bool
    {
        try {
            $isIpv6 = strpos($ip, ':') !== false;
            $cidr = $ip . ($isIpv6 ? '/128' : '/32');
            $packed = @inet_pton($ip);

            if ($packed === false) {
                return false;
            }

            // Em IPv4 inet_pton retorna 4 bytes; padronizamos para 16
            // bytes mantendo compatibilidade com BINARY(16) de mapeamentos
            // mistos v4/v6 (esquerda-zerada).
            if (strlen($packed) === 4) {
                $packed = str_repeat("\0", 12) . $packed;
            }

            $expiresAt = Carbon::now()->addMinutes(self::AUTO_BLOCK_DURATION_MINUTES);

            DB::table('waf_ip_blocklist')->insert([
                'cidr' => $cidr,
                'ip_start' => $packed,
                'ip_end' => $packed,
                'reason' => 'anomaly:' . $reasonType,
                'expires_at' => $expiresAt,
                'source' => 'auto_brute_force',
                'auto_generated' => true,
                'created_by' => null,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);

            return true;
        } catch (Throwable $e) {
            Log::channel(self::LOG_CHANNEL)->warning(
                'insertWafBlocklist falhou',
                ['ip' => $ip, 'exception' => $e->getMessage()]
            );

            return false;
        }
    }

    /**
     * Insere o IP na tabela rate_limit_blocks. Usado como fallback
     * caso waf_ip_blocklist nao exista.
     */
    protected function insertRateLimitBlock(string $ip, string $reasonType): bool
    {
        try {
            $blockedUntil = Carbon::now()->addMinutes(self::AUTO_BLOCK_DURATION_MINUTES);

            DB::table('rate_limit_blocks')->insert([
                'ip_address' => $ip,
                'reason' => 'anomaly:' . $reasonType,
                'blocked_until' => $blockedUntil,
                'attempts' => 1,
                'created_at' => Carbon::now(),
            ]);

            return true;
        } catch (Throwable $e) {
            Log::channel(self::LOG_CHANNEL)->warning(
                'insertRateLimitBlock falhou',
                ['ip' => $ip, 'exception' => $e->getMessage()]
            );

            return false;
        }
    }

    // ----- Settings helpers -----

    protected function loginThreshold(): int
    {
        return $this->intSetting('anomaly_login_threshold', self::DEFAULT_LOGIN_THRESHOLD);
    }

    protected function uploadThreshold(): int
    {
        return $this->intSetting('anomaly_upload_threshold', self::DEFAULT_UPLOAD_THRESHOLD);
    }

    protected function webhookThreshold(): int
    {
        return $this->intSetting('anomaly_webhook_threshold', self::DEFAULT_WEBHOOK_THRESHOLD);
    }

    protected function autoBlockEnabled(): bool
    {
        $raw = Setting::get('anomaly_auto_block', '0');

        if (is_bool($raw)) {
            return $raw;
        }

        $value = strtolower(trim((string) $raw));

        return in_array($value, ['1', 'true', 'yes', 'on'], true);
    }

    protected function intSetting(string $key, int $default): int
    {
        $raw = Setting::get($key, $default);

        if (is_int($raw)) {
            return $raw > 0 ? $raw : $default;
        }

        if (is_string($raw) && is_numeric($raw)) {
            $value = (int) $raw;

            return $value > 0 ? $value : $default;
        }

        return $default;
    }

    // ----- Notification helpers -----

    protected function resolveSuperadminEmail(): ?string
    {
        $configured = trim((string) Setting::get('admin_alert_email', ''));
        if ($configured !== '' && filter_var($configured, FILTER_VALIDATE_EMAIL)) {
            return $configured;
        }

        try {
            $user = User::query()
                ->where(function ($query) {
                    $query->where('role', 'superadmin')
                        ->orWhere('level', 'superadmin');
                })
                ->whereNotNull('email')
                ->orderBy('id')
                ->first();

            if ($user instanceof User && filter_var((string) $user->email, FILTER_VALIDATE_EMAIL)) {
                return (string) $user->email;
            }
        } catch (Throwable $e) {
            Log::channel(self::LOG_CHANNEL)->warning(
                'Falha ao resolver email do Superadmin',
                ['exception' => $e->getMessage()]
            );
        }

        $fromConfig = trim((string) config('mail.from.address', ''));
        if ($fromConfig !== '' && filter_var($fromConfig, FILTER_VALIDATE_EMAIL)) {
            return $fromConfig;
        }

        return null;
    }

    protected function humanType(string $type): string
    {
        return match ($type) {
            self::TYPE_FAILED_LOGINS => 'logins falhos em rajada',
            self::TYPE_UPLOAD_FLOOD => 'flood de uploads',
            self::TYPE_INVALID_WEBHOOKS => 'webhooks invalidos repetidos',
            default => $type,
        };
    }

    protected function buildEmailBody(AnomalyEvent $anomaly): string
    {
        $rows = [
            'Tipo' => $this->humanType($anomaly->type) . ' (' . $anomaly->type . ')',
            'Origem (IP)' => $anomaly->source_ip ?? '-',
            'Origem (User ID)' => $anomaly->source_user_id !== null ? (string) $anomaly->source_user_id : '-',
            'Origem (Identifier)' => $anomaly->source_identifier ?? '-',
            'Threshold' => (string) $anomaly->threshold_value,
            'Ocorrencias' => (string) $anomaly->actual_value,
            'Janela (min)' => (string) $anomaly->window_minutes,
            'Detectado em' => optional($anomaly->created_at)->toDateTimeString() ?? Carbon::now()->toDateTimeString(),
        ];

        $html = '<h2>Anomalia detectada</h2>'
            . '<p>O sistema de deteccao de anomalias identificou o seguinte evento que ultrapassou o threshold configurado.</p>'
            . '<table cellpadding="6" cellspacing="0" border="1" style="border-collapse:collapse;">';

        foreach ($rows as $label => $value) {
            $html .= '<tr><td><strong>'
                . htmlspecialchars($label, ENT_QUOTES, 'UTF-8')
                . '</strong></td><td>'
                . htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8')
                . '</td></tr>';
        }

        $html .= '</table>'
            . '<p>Caso essa atividade seja legitima, ajuste os thresholds em '
            . '<em>Configuracoes -> Seguranca</em> ou desative o auto-block.</p>';

        return $html;
    }
}
