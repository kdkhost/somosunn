<?php

namespace App\Console\Commands;

use App\Services\OrderSplitPayoutService;
use Illuminate\Console\Command;

class ProcessAutomaticSplitPayoutsCommand extends Command
{
    protected $signature = 'splits:process-payouts {--force : Ignora o prazo D+N agendado e tenta liquidar imediatamente}';

    protected $description = 'Processa automaticamente os repasses pendentes de comissão (Pix Out Efí Pay -> Mercado Pago)';

    public function handle(OrderSplitPayoutService $payoutService): int
    {
        $force = (bool) $this->option('force');
        $result = $payoutService->processDueAutomaticPayouts($force);

        if ($result['skipped']) {
            $this->info('Automação de repasses desativada ou configurada em modo manual/roteamento MP.');

            return self::SUCCESS;
        }

        $this->info(sprintf(
            'Repasses processados: %d | Pagos: %d | Aguardando saldo/prazo: %d | Falhas: %d',
            $result['processed'],
            $result['paid'],
            $result['waiting_balance'],
            $result['failed']
        ));

        return self::SUCCESS;
    }
}
