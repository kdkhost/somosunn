<?php

namespace App\Services\Waf;

/**
 * WafSettings - value object imutavel com as configuracoes operacionais
 * do WAF consultadas a cada inspecao.
 *
 * Prefere valores da tabela `waf_settings` (quando a migration ja rodou)
 * e cai para `config('waf.*')` como fallback quando o banco/cache
 * estao indisponiveis.
 *
 * Spec: .kiro/specs/waf-e-auditoria-seguranca
 * Requisitos: 9.5, 9.6, 9.7, 22.1
 */
final class WafSettings
{
    public const MODE_DETECTION = 'detection-only';
    public const MODE_ENFORCE   = 'enforce';

    public const FAIL_OPEN   = 'open';
    public const FAIL_CLOSED = 'closed';

    public function __construct(
        public readonly bool   $enabled,
        public readonly string $mode,
        public readonly string $failPolicy,
        public readonly int    $thresholdMonitor,
        public readonly int    $thresholdChallenge,
        public readonly int    $thresholdBlock,
        public readonly array  $retention,
        public readonly array  $exemptRoutes,
        public readonly array  $rateLimits,
        public readonly array  $autoBlock,
        public readonly float  $allowedSamplingRatio,
        public readonly int    $sampleMaxBytes,
        public readonly int    $regexTimeoutMs,
        public readonly array  $masking,
    ) {}

    /**
     * Monta WafSettings a partir de config('waf.*') apenas (fallback puro).
     */
    public static function fromConfig(): self
    {
        return new self(
            enabled:               (bool)  config('waf.enabled', false),
            mode:                  (string) config('waf.mode', self::MODE_DETECTION),
            failPolicy:            (string) config('waf.fail_policy', self::FAIL_OPEN),
            thresholdMonitor:      (int)   config('waf.thresholds.monitor', 20),
            thresholdChallenge:    (int)   config('waf.thresholds.challenge', 50),
            thresholdBlock:        (int)   config('waf.thresholds.block', 80),
            retention:             (array) config('waf.retention', []),
            exemptRoutes:          (array) config('waf.exempt_routes', []),
            rateLimits:            (array) config('waf.rate_limits', []),
            autoBlock:             (array) config('waf.auto_block', []),
            allowedSamplingRatio:  (float) config('waf.allowed_sampling_ratio', 0.0),
            sampleMaxBytes:        (int)   config('waf.sample_max_bytes', 2048),
            regexTimeoutMs:        (int)   config('waf.regex_timeout_ms', 20),
            masking:               (array) config('waf.masking', []),
        );
    }

    /**
     * Tenta ler overrides da tabela waf_settings. Em caso de qualquer falha
     * (tabela inexistente, banco indisponivel), retorna config fallback.
     */
    public static function load(): self
    {
        $base = self::fromConfig();

        try {
            if (! class_exists(\Schema::class) || ! \Schema::hasTable('waf_settings')) {
                return $base;
            }

            $rows = \App\Models\Waf\WafSetting::query()->get()->keyBy('key');
            if ($rows->isEmpty()) {
                return $base;
            }

            $enabled = $rows->has('waf.enabled')
                ? (bool)  $rows->get('waf.enabled')->value
                : $base->enabled;

            $mode = $rows->has('waf.mode')
                ? (string) $rows->get('waf.mode')->value
                : $base->mode;

            $failPolicy = $rows->has('waf.fail_policy')
                ? (string) $rows->get('waf.fail_policy')->value
                : $base->failPolicy;

            $thresholds = $rows->has('waf.thresholds')
                ? (array)  $rows->get('waf.thresholds')->value
                : [
                    'monitor'   => $base->thresholdMonitor,
                    'challenge' => $base->thresholdChallenge,
                    'block'     => $base->thresholdBlock,
                ];

            $retention    = $rows->has('waf.retention')    ? (array) $rows->get('waf.retention')->value    : $base->retention;
            $exemptRoutes = $rows->has('waf.exempt_routes')? (array) $rows->get('waf.exempt_routes')->value: $base->exemptRoutes;
            $rateLimits   = $rows->has('waf.rate_limits')  ? (array) $rows->get('waf.rate_limits')->value  : $base->rateLimits;
            $autoBlock    = $rows->has('waf.auto_block')   ? (array) $rows->get('waf.auto_block')->value   : $base->autoBlock;

            return new self(
                enabled:              $enabled,
                mode:                 $mode,
                failPolicy:           $failPolicy,
                thresholdMonitor:     (int) ($thresholds['monitor']   ?? $base->thresholdMonitor),
                thresholdChallenge:   (int) ($thresholds['challenge'] ?? $base->thresholdChallenge),
                thresholdBlock:       (int) ($thresholds['block']     ?? $base->thresholdBlock),
                retention:            $retention,
                exemptRoutes:         $exemptRoutes,
                rateLimits:           $rateLimits,
                autoBlock:            $autoBlock,
                allowedSamplingRatio: $base->allowedSamplingRatio,
                sampleMaxBytes:       $base->sampleMaxBytes,
                regexTimeoutMs:       $base->regexTimeoutMs,
                masking:              $base->masking,
            );
        } catch (\Throwable $e) {
            return $base;
        }
    }

    public function isDetectionOnly(): bool
    {
        return $this->mode === self::MODE_DETECTION;
    }

    public function isEnforce(): bool
    {
        return $this->mode === self::MODE_ENFORCE;
    }

    public function isFailOpen(): bool
    {
        return in_array($this->failPolicy, [self::FAIL_OPEN, 'allow', 'open'], true);
    }
}
