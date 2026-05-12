<?php

namespace App\Services\Waf\Scanners;

/**
 * Scanner de webhooks.
 *
 * Busca por controllers cujos nomes ou conteudos indicam webhook
 * (SumUp, MercadoPago, generico) e verifica se:
 *   - Faz validacao HMAC (hash_hmac / hash_equals)
 *   - Valida timestamp (janela de minutos)
 *   - Tem controle de idempotencia (por event_id)
 *
 * Spec: .kiro/specs/waf-e-auditoria-seguranca
 * Requisitos: 1.7, 8.1, 8.3, 8.4
 */
class WebhookScanner extends AbstractScanner
{
    private int $counter = 0;

    public function id(): string
    {
        return 'webhook';
    }

    public function label(): string
    {
        return 'Webhooks sem HMAC, janela de timestamp ou idempotencia';
    }

    public function scan(AuditContext $ctx): iterable
    {
        $candidates = [];

        foreach ($this->iterateFiles($ctx, ['.php'], ['app/Http/Controllers']) as $file) {
            $abs = $file->getPathname();
            $rel = $ctx->rel($abs);

            $basename = strtolower($file->getBasename());
            $content  = @file_get_contents($abs);

            if ($content === false) {
                continue;
            }

            // So considera webhook real se:
            //  - nome do arquivo tem "webhook" ou "sumup" OU
            //  - conteudo tem chamada explicita a hash_hmac (signature check) OU
            //  - classe tem "Webhook" no nome
            $nameHints = str_contains($basename, 'webhook') || str_contains($basename, 'sumup');
            $contentHints = (bool) preg_match('/\bhash_hmac\s*\(|signature_verify|webhook_secret|x-signature/i', $content);
            $classHint = (bool) preg_match('/class\s+\w*Webhook\w*/i', $content);

            if (! $nameHints && ! $contentHints && ! $classHint) {
                continue;
            }

            $candidates[$rel] = $content;
        }

        foreach ($candidates as $rel => $content) {
            $hasHmac        = (bool) preg_match('/\b(hash_hmac|hash_equals|createHmac)\s*\(/i', $content);
            $hasTimestamp   = (bool) preg_match('/\b(timestamp|Signature-Timestamp|x-.*-timestamp|now\s*\(\)|->subMinutes|->addMinutes)/i', $content);
            $hasIdempotency = (bool) preg_match('/\b(event_id|eventId|idempotency|already_processed|findByEventId|where\([\'\"]event_id)/i', $content);

            if (! $hasHmac) {
                $this->counter++;

                yield new AuditFinding(
                    id:              sprintf('SEC-WEBHOOK-HMAC-%04d', $this->counter),
                    category:        'SEC-WEBHOOK',
                    severity:        AuditFinding::SEVERITY_CRITICAL,
                    area:            'Webhooks',
                    title:           'Webhook sem verificacao HMAC de assinatura',
                    recommendation:  'Validar assinatura conforme provedor (SumUp/MercadoPago) usando hash_hmac + hash_equals com secret em `.env`. Rejeitar payload sem assinatura.',
                    file:            $rel,
                    line:            null,
                    context:         null,
                    wafMitigable:    true,
                    compensatingControl: 'Ate correcao, regra WAF `Webhook_Invalid_Signature` pode bloquear tentativas forjadas.',
                    deadline:        AuditFinding::defaultDeadline(AuditFinding::SEVERITY_CRITICAL),
                );
            }

            if (! $hasTimestamp) {
                $this->counter++;

                yield new AuditFinding(
                    id:              sprintf('SEC-WEBHOOK-TS-%04d', $this->counter),
                    category:        'SEC-WEBHOOK',
                    severity:        AuditFinding::SEVERITY_HIGH,
                    area:            'Webhooks',
                    title:           'Webhook sem validacao de janela de timestamp',
                    recommendation:  'Rejeitar requisicoes com timestamp fora da janela (padrao 5min) para prevenir replay. Conferir header `X-Signature-Timestamp` ou campo do payload.',
                    file:            $rel,
                    line:            null,
                    context:         null,
                    wafMitigable:    false,
                    deadline:        AuditFinding::defaultDeadline(AuditFinding::SEVERITY_HIGH),
                );
            }

            if (! $hasIdempotency) {
                $this->counter++;

                yield new AuditFinding(
                    id:              sprintf('SEC-WEBHOOK-IDEMP-%04d', $this->counter),
                    category:        'SEC-WEBHOOK',
                    severity:        AuditFinding::SEVERITY_HIGH,
                    area:            'Webhooks',
                    title:           'Webhook sem controle de idempotencia por event_id',
                    recommendation:  'Persistir o `event_id` em tabela de processados e rejeitar reentregas duplicadas retornando HTTP 200 sem re-executar.',
                    file:            $rel,
                    line:            null,
                    context:         null,
                    wafMitigable:    false,
                    deadline:        AuditFinding::defaultDeadline(AuditFinding::SEVERITY_HIGH),
                );
            }
        }
    }
}
