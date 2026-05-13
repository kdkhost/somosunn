<?php

namespace App\Console\Commands;

use App\Models\Waf\WafEvent;
use App\Services\Waf\WafAlertService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

/**
 * Verifica picos de bloqueios do WAF e dispara alertas.
 *
 * Agendado a cada 5 minutos no Kernel.
 * Usa cache para nao disparar alertas repetidos na mesma janela.
 */
class WafCheckBlockSpike extends Command
{
    protected $signature = 'waf:check-spike';
    protected $description = 'Verifica picos de bloqueios do WAF e dispara alertas por email/webhook.';

    public function handle(): int
    {
        if (! Schema::hasTable('waf_events') || ! Schema::hasTable('waf_alerts_config')) {
            return self::SUCCESS;
        }

        $windowMinutes = 5;
        $threshold = 50; // padrao: 50 bloqueios em 5 min = pico

        // Contar bloqueios na janela
        $blockedCount = WafEvent::query()
            ->where('decision', 'blocked')
            ->where('occurred_at', '>=', now()->subMinutes($windowMinutes))
            ->count();

        if ($blockedCount < $threshold) {
            return self::SUCCESS;
        }

        // Evitar alertas repetidos (1 alerta por janela de 15 min)
        $cacheKey = 'waf:spike_alert_sent';
        if (Cache::get($cacheKey)) {
            return self::SUCCESS;
        }

        // Buscar top IP atacante na janela
        $topIp = WafEvent::query()
            ->where('decision', 'blocked')
            ->where('occurred_at', '>=', now()->subMinutes($windowMinutes))
            ->selectRaw('ip, COUNT(*) as cnt')
            ->groupBy('ip')
            ->orderByDesc('cnt')
            ->first();

        // Disparar alerta
        $alertService = new WafAlertService();
        $alertService->fire('block_spike', [
            'count'     => $blockedCount,
            'window'    => $windowMinutes,
            'top_ip'    => $topIp?->ip ?? 'N/A',
            'timestamp' => now()->format('d/m/Y H:i:s'),
        ]);

        // Marcar como enviado (nao repetir por 15 min)
        Cache::put($cacheKey, true, now()->addMinutes(15));

        $this->info("Alerta de pico disparado: {$blockedCount} bloqueios em {$windowMinutes} min.");

        return self::SUCCESS;
    }
}
