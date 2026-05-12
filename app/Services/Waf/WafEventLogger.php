<?php

namespace App\Services\Waf;

use App\Models\Waf\WafEvent;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

/**
 * Persiste WafEvents e escreve no canal de log `waf`.
 *
 * Spec: .kiro/specs/waf-e-auditoria-seguranca
 * Requisitos: 12.1, 12.2, 12.3, 21.1, 21.2
 */
final class WafEventLogger
{
    public function __construct(
        private readonly SensitiveDataMasker $masker,
        private readonly WafSettings         $settings,
    ) {}

    /**
     * Registra o evento. Retorna o UID do WafEvent persistido (ou null
     * quando a tabela nao existe/persistencia falha - nesse caso so
     * grava no canal de log).
     */
    public function log(WafContext $ctx, WafDecision $decision): ?string
    {
        $level = match ($decision->decision) {
            WafDecision::BLOCKED   => 'error',
            WafDecision::CHALLENGED,
            WafDecision::MONITORED => 'warning',
            default                => 'info',
        };

        // Sempre loga no canal `waf` (sem depender do banco)
        try {
            Log::channel('waf')->log($level, 'WAF decision', [
                'request_id'  => $ctx->requestId,
                'ip'          => $ctx->ip,
                'method'      => $ctx->method,
                'route'       => $ctx->routeName,
                'path'        => $ctx->path,
                'decision'    => $decision->decision,
                'risk_score'  => $decision->riskScore,
                'user_id'     => $ctx->userId,
                'rules'       => array_map(fn ($r) => $r->toArray(), $decision->rules),
                'reason'      => $decision->reason,
                'original'    => $decision->originalDecision,
            ]);
        } catch (\Throwable $e) {
            // ignora
        }

        // Persiste na tabela se ela existir
        if (! $this->tableExists()) {
            return null;
        }

        try {
            $samples = $this->buildSamples($ctx, $decision);

            $event = WafEvent::query()->create([
                'request_id'         => $ctx->requestId,
                'occurred_at'        => now(),
                'ip'                 => $ctx->ip,
                'country'            => $ctx->country,
                'asn'                => $ctx->asn,
                'user_id'            => $ctx->userId,
                'method'             => $ctx->method,
                'route'              => $ctx->routeName,
                'path'               => substr($ctx->path, 0, 500),
                'status'             => $decision->status,
                'risk_score'         => $decision->riskScore,
                'decision'           => $decision->originalDecision ?? $decision->decision,
                'rules_fired'        => array_map(fn ($r) => $r->toArray(), $decision->rules),
                'samples'            => $samples,
                'user_agent'         => substr((string) $ctx->userAgent, 0, 500),
                'referrer'           => $ctx->referrer ? substr($ctx->referrer, 0, 500) : null,
                'is_false_positive'  => false,
            ]);

            return $event->uid;
        } catch (\Throwable $e) {
            try {
                Log::channel('waf')->error('Falha ao persistir WafEvent: ' . $e->getMessage(), [
                    'request_id' => $ctx->requestId,
                ]);
            } catch (\Throwable $ee) {
                // ignora
            }
            return null;
        }
    }

    /**
     * Amostras mascaradas e truncadas dos campos que dispararam regras.
     */
    private function buildSamples(WafContext $ctx, WafDecision $decision): array
    {
        $max = $this->settings->sampleMaxBytes;
        $samples = [];

        // Captura apenas os campos mencionados nos matches
        $fields = [];
        foreach ($decision->rules as $match) {
            $fields[$match->field] = true;
        }

        foreach (array_keys($fields) as $field) {
            $raw = $ctx->targetString($field);
            if ($raw === '') {
                continue;
            }

            $samples[$field] = $this->masker->truncated($raw, $max);
        }

        // Tambem anexa rapid snapshot da requisicao (sem corpo completo)
        $samples['_meta'] = [
            'method' => $ctx->method,
            'path'   => $ctx->path,
            'scope'  => $ctx->scope,
        ];

        return $samples;
    }

    private function tableExists(): bool
    {
        try {
            return Schema::hasTable('waf_events');
        } catch (\Throwable $e) {
            return false;
        }
    }
}
