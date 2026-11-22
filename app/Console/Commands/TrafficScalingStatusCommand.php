<?php

namespace App\Console\Commands;

use App\Support\TrafficLoadDetector;
use Illuminate\Console\Command;

class TrafficScalingStatusCommand extends Command
{
    protected $signature = 'traffic:status {--mode= : Forçar modo específico (auto, normal, elevated, high, surge)}';
    protected $description = 'Exibe ou ajusta o status do motor de auto-escalabilidade e tráfego adaptativo';

    public function handle(): int
    {
        $modeOption = $this->option('mode');
        if ($modeOption !== null) {
            $validModes = ['auto', TrafficLoadDetector::LEVEL_NORMAL, TrafficLoadDetector::LEVEL_ELEVATED, TrafficLoadDetector::LEVEL_HIGH, TrafficLoadDetector::LEVEL_SURGE];
            if (!in_array($modeOption, $validModes, true)) {
                $this->error("Modo inválido. Opções: " . implode(', ', $validModes));
                return 1;
            }

            \App\Models\Setting::set('traffic_scaling_force_mode', $modeOption, 'system');
            \Illuminate\Support\Facades\Cache::forget('traffic:force_mode_active');
            $this->info("Modo de escalabilidade configurado para: [{$modeOption}] com sucesso!");
        }

        $rpm = TrafficLoadDetector::getCurrentRpm();
        $policy = TrafficLoadDetector::getAdaptivePolicy();

        $this->info("\n=== SOMOS UNN - MOTOR DE AUTO-ESCALABILIDADE E TRÁFEGO ADAPTATIVO ===");
        $this->table(
            ['Métrica', 'Valor'],
            [
                ['Volume Atual (RPM)', "{$rpm} req/min"],
                ['Nível de Carga', strtoupper($policy['level']) . " ({$policy['label']})"],
                ['TTL Cache Browser', "{$policy['edge_max_age']} segundos (" . round($policy['edge_max_age'] / 60, 1) . " min)"],
                ['TTL Borda CDN (Cloudflare)', "{$policy['edge_s_maxage']} segundos (" . round($policy['edge_s_maxage'] / 60, 1) . " min)"],
                ['Stale-While-Revalidate', "{$policy['edge_stale_while_revalidate']} segundos"],
                ['Stale-If-Error (Tolerância a Falhas)', "{$policy['edge_stale_if_error']} segundos"],
                ['Microcache de Queries Públicas', "{$policy['query_microcache_ttl']} segundos"],
                ['Otimização de Analytics/Logs', $policy['defer_analytics'] ? 'Ativo (Prioridade de Entrega)' : 'Normal'],
            ]
        );

        $this->comment("O sistema se ajusta dinamicamente a cada 10 segundos com base no tráfego real recebido.\n");

        return 0;
    }
}
