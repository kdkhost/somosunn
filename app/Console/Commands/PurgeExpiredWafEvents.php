<?php

namespace App\Console\Commands;

use App\Models\Waf\WafEvent;
use App\Services\Waf\WafSettings;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;

/**
 * Remove WAF_Events que ultrapassaram o prazo de retenção.
 *
 * Spec: .kiro/specs/waf-e-auditoria-seguranca
 * Requisitos: 12.4, 12.5
 */
class PurgeExpiredWafEvents extends Command
{
    protected $signature = 'waf:purge-events {--dry-run : Apenas mostra quantos seriam removidos}';
    protected $description = 'Remove WAF events expirados conforme política de retenção.';

    public function handle(): int
    {
        if (! Schema::hasTable('waf_events')) {
            $this->warn('Tabela waf_events não existe. Rode as migrations primeiro.');
            return self::SUCCESS;
        }

        $settings  = WafSettings::load();
        $retention = $settings->retention;
        $dryRun    = (bool) $this->option('dry-run');
        $total     = 0;

        foreach ($retention as $decision => $days) {
            if ($days <= 0) {
                continue;
            }

            $cutoff = now()->subDays($days);

            $query = WafEvent::query()
                ->where('decision', $decision)
                ->where('occurred_at', '<', $cutoff);

            $count = $query->count();

            if ($count > 0 && ! $dryRun) {
                // Deleta em chunks para não travar o banco
                WafEvent::query()
                    ->where('decision', $decision)
                    ->where('occurred_at', '<', $cutoff)
                    ->chunkById(1000, function ($events) {
                        WafEvent::query()->whereIn('id', $events->pluck('id'))->delete();
                    });
            }

            $this->line(sprintf(
                '  %s: %d eventos %s (retenção: %d dias)',
                $decision,
                $count,
                $dryRun ? 'seriam removidos' : 'removidos',
                $days
            ));

            $total += $count;
        }

        $this->info(sprintf('Total: %d eventos %s.', $total, $dryRun ? '(dry-run)' : 'purgados'));

        return self::SUCCESS;
    }
}
