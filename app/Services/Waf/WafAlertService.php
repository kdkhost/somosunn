<?php

namespace App\Services\Waf;

use App\Mail\WafAlertMail;
use App\Models\Waf\WafAlertConfig;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;

/**
 * Dispara alertas do WAF pelos canais configurados (email, webhook).
 *
 * Respeita silence_until e is_active de cada alerta.
 *
 * Spec: .kiro/specs/waf-e-auditoria-seguranca
 * Requisitos: 16.2, 16.3, 16.4
 */
final class WafAlertService
{
    /**
     * Dispara alerta para todos os canais configurados para o gatilho.
     *
     * @param string $trigger   block_spike|auto_block|critical_finding
     * @param array  $data      Dados contextuais do alerta
     */
    public function fire(string $trigger, array $data): void
    {
        if (! Schema::hasTable('waf_alerts_config')) {
            return;
        }

        try {
            $configs = WafAlertConfig::query()
                ->active()
                ->byTrigger($trigger)
                ->get();

            foreach ($configs as $config) {
                $this->dispatch($config, $trigger, $data);
            }
        } catch (\Throwable $e) {
            Log::channel('waf')->error('Falha ao disparar alerta WAF', [
                'trigger' => $trigger,
                'error'   => $e->getMessage(),
            ]);
        }
    }

    private function dispatch(WafAlertConfig $config, string $trigger, array $data): void
    {
        try {
            if ($config->channel === WafAlertConfig::CHANNEL_EMAIL) {
                $this->sendEmail($config->target, $trigger, $data);
            } elseif ($config->channel === WafAlertConfig::CHANNEL_WEBHOOK) {
                $this->sendWebhook($config->target, $trigger, $data);
            }
        } catch (\Throwable $e) {
            Log::channel('waf')->warning('Falha ao enviar alerta WAF', [
                'channel' => $config->channel,
                'target'  => $config->target,
                'trigger' => $trigger,
                'error'   => $e->getMessage(),
            ]);
        }
    }

    private function sendEmail(string $to, string $trigger, array $data): void
    {
        Mail::to($to)->send(new WafAlertMail($trigger, $data));
    }

    private function sendWebhook(string $url, string $trigger, array $data): void
    {
        Http::timeout(10)->post($url, [
            'event'     => 'waf_alert',
            'trigger'   => $trigger,
            'data'      => $data,
            'timestamp' => now()->toIso8601String(),
            'site'      => config('app.url'),
        ]);
    }
}
