<?php

namespace App\Console\Commands;

use App\Services\Waf\IpListService;
use Illuminate\Console\Command;

/**
 * Remove entradas expiradas de waf_ip_blocklist e waf_ip_allowlist.
 *
 * Spec: .kiro/specs/waf-e-auditoria-seguranca
 * Requisito: 11.5
 */
class PurgeExpiredIpEntries extends Command
{
    protected $signature = 'waf:purge-ips';
    protected $description = 'Remove IPs expirados das listas de bloqueio/permissão do WAF.';

    public function handle(): int
    {
        $service = new IpListService();
        $count   = $service->purgeExpired();

        $this->info(sprintf('%d entradas expiradas removidas.', $count));

        return self::SUCCESS;
    }
}
