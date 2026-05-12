<?php

namespace App\Services\Waf;

use App\Models\Waf\WafIpAllowlistEntry;
use App\Models\Waf\WafIpBlocklistEntry;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Gestao de IP_Blocklist e IP_Allowlist (IPv4/IPv6, CIDR, expiracao).
 *
 * Armazena `ip_start`/`ip_end` em `BINARY(16)` (IPv4 mapeado em IPv6)
 * para lookups por range em O(log n) com indice.
 *
 * Spec: .kiro/specs/waf-e-auditoria-seguranca
 * Requisitos: 11.3, 11.4, 11.5, 11.6, 9.8, 9.9
 */
final class IpListService
{
    /**
     * True se o IP esta em uma entrada ativa da blocklist.
     */
    public function isBlocked(string $ip): ?WafIpBlocklistEntry
    {
        if (! $this->tableExists('waf_ip_blocklist')) {
            return null;
        }

        $packed = $this->ipToBinary($ip);
        if ($packed === null) {
            return null;
        }

        try {
            return WafIpBlocklistEntry::query()
                ->active()
                ->whereRaw('ip_start <= ?', [$packed])
                ->whereRaw('ip_end   >= ?', [$packed])
                ->first();
        } catch (\Throwable $e) {
            return null;
        }
    }

    public function isAllowed(string $ip): ?WafIpAllowlistEntry
    {
        if (! $this->tableExists('waf_ip_allowlist')) {
            return null;
        }

        $packed = $this->ipToBinary($ip);
        if ($packed === null) {
            return null;
        }

        try {
            return WafIpAllowlistEntry::query()
                ->active()
                ->whereRaw('ip_start <= ?', [$packed])
                ->whereRaw('ip_end   >= ?', [$packed])
                ->first();
        } catch (\Throwable $e) {
            return null;
        }
    }

    /**
     * Adiciona IP/CIDR a blocklist.
     */
    public function block(string $ipOrCidr, ?Carbon $expiresAt, ?string $reason, ?int $actorId, string $source = 'manual'): ?WafIpBlocklistEntry
    {
        if (! $this->tableExists('waf_ip_blocklist')) {
            return null;
        }

        [$start, $end] = $this->cidrToRange($ipOrCidr);
        if ($start === null) {
            return null;
        }

        return WafIpBlocklistEntry::query()->create([
            'cidr'           => $ipOrCidr,
            'ip_start'       => $start,
            'ip_end'         => $end,
            'reason'         => $reason,
            'expires_at'     => $expiresAt,
            'source'         => $source,
            'auto_generated' => $source !== 'manual',
            'created_by'     => $actorId,
        ]);
    }

    public function allow(string $ipOrCidr, ?Carbon $expiresAt, ?string $reason, ?int $actorId): ?WafIpAllowlistEntry
    {
        if (! $this->tableExists('waf_ip_allowlist')) {
            return null;
        }

        [$start, $end] = $this->cidrToRange($ipOrCidr);
        if ($start === null) {
            return null;
        }

        return WafIpAllowlistEntry::query()->create([
            'cidr'       => $ipOrCidr,
            'ip_start'   => $start,
            'ip_end'     => $end,
            'reason'     => $reason,
            'expires_at' => $expiresAt,
            'created_by' => $actorId,
        ]);
    }

    /**
     * Remove entradas expiradas (chamado por job diario).
     */
    public function purgeExpired(): int
    {
        $count = 0;

        try {
            if ($this->tableExists('waf_ip_blocklist')) {
                $count += WafIpBlocklistEntry::query()->expired()->delete();
            }
            if ($this->tableExists('waf_ip_allowlist')) {
                $count += WafIpAllowlistEntry::query()
                    ->whereNotNull('expires_at')
                    ->where('expires_at', '<=', now())
                    ->delete();
            }
        } catch (\Throwable $e) {
            // ignora - job tenta de novo no proximo ciclo
        }

        return $count;
    }

    /**
     * Idempotente: adiciona IP a blocklist se ja houver N eventos blocked/challenged
     * do mesmo IP dentro da janela. Nao duplica entrada.
     */
    public function autoBlock(string $ip, int $windowMinutes, int $threshold, int $durationHours): ?WafIpBlocklistEntry
    {
        if (! $this->tableExists('waf_events') || ! $this->tableExists('waf_ip_blocklist')) {
            return null;
        }

        // Ja tem entrada ativa manual ou automatica? Nao duplica.
        $existing = $this->isBlocked($ip);
        if ($existing !== null) {
            return $existing;
        }

        // Conta eventos blocked/challenged nesta janela
        $count = DB::table('waf_events')
            ->where('ip', $ip)
            ->whereIn('decision', [WafDecision::BLOCKED, WafDecision::CHALLENGED])
            ->where('occurred_at', '>=', now()->subMinutes($windowMinutes))
            ->count();

        if ($count < $threshold) {
            return null;
        }

        return $this->block(
            ipOrCidr:  $ip,
            expiresAt: now()->addHours($durationHours),
            reason:    sprintf('Auto-bloqueio: %d eventos blocked/challenged em %dm', $count, $windowMinutes),
            actorId:   null,
            source:    WafIpBlocklistEntry::SOURCE_AUTO_RISK_SCORE,
        );
    }

    /* ============================================================
     *  Utilitarios de conversao IP/CIDR -> BINARY(16)
     * ============================================================ */

    /**
     * Converte IP v4/v6 em BINARY(16) (IPv4 mapeado em IPv6).
     */
    public function ipToBinary(string $ip): ?string
    {
        $packed = @inet_pton($ip);
        if ($packed === false) {
            return null;
        }

        // IPv4 -> stringto IPv6-mapped (16 bytes)
        if (strlen($packed) === 4) {
            $packed = str_repeat("\x00", 10) . "\xff\xff" . $packed;
        }

        return $packed;
    }

    /**
     * Converte "192.168.0.0/24" ou "2001:db8::/32" em [startBin, endBin].
     * Tambem aceita IP sem mascara (trata como /32 ou /128).
     *
     * @return array{0: ?string, 1: ?string}
     */
    public function cidrToRange(string $cidr): array
    {
        if (! str_contains($cidr, '/')) {
            $binary = $this->ipToBinary($cidr);
            return $binary ? [$binary, $binary] : [null, null];
        }

        [$ip, $mask] = explode('/', $cidr, 2);
        $mask = (int) $mask;

        $packed = @inet_pton($ip);
        if ($packed === false) {
            return [null, null];
        }

        $isV4 = strlen($packed) === 4;
        if ($isV4) {
            // Ajusta mascara v4 para espaco v6 (IPv4 fica nos ultimos 32 bits)
            $packed = str_repeat("\x00", 10) . "\xff\xff" . $packed;
            $mask  += 96;
        }

        if ($mask < 0)  $mask = 0;
        if ($mask > 128) $mask = 128;

        $fullBytes = intdiv($mask, 8);
        $partBits  = $mask % 8;

        $maskBinary = str_repeat("\xff", $fullBytes);
        if ($partBits > 0) {
            $maskBinary .= chr((0xff << (8 - $partBits)) & 0xff);
        }
        $maskBinary = str_pad($maskBinary, 16, "\x00");

        $start = '';
        $end   = '';
        for ($i = 0; $i < 16; $i++) {
            $p = ord($packed[$i]);
            $m = ord($maskBinary[$i]);
            $start .= chr($p & $m);
            $end   .= chr(($p & $m) | (~$m & 0xff));
        }

        return [$start, $end];
    }

    private function tableExists(string $table): bool
    {
        try {
            return Schema::hasTable($table);
        } catch (\Throwable $e) {
            return false;
        }
    }
}
