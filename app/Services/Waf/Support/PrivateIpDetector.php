<?php

namespace App\Services\Waf\Support;

/**
 * Deteccao de IPs pertencentes a faixas privadas / loopback / link-local.
 *
 * Spec: .kiro/specs/waf-e-auditoria-seguranca
 * Requisitos: 5.4, 5.5, Property 14
 */
class PrivateIpDetector
{
    /** @var array<int, string> Faixas CIDR IPv4 consideradas privadas/especiais. */
    public const V4_PRIVATE_CIDRS = [
        '10.0.0.0/8',
        '172.16.0.0/12',
        '192.168.0.0/16',
        '127.0.0.0/8',     // loopback
        '169.254.0.0/16',  // link-local
        '0.0.0.0/8',       // "este host"
        '100.64.0.0/10',   // CGNAT
        '224.0.0.0/4',     // multicast
    ];

    /** @var array<int, string> Faixas CIDR IPv6 consideradas privadas/especiais. */
    public const V6_PRIVATE_CIDRS = [
        '::1/128',      // loopback
        'fc00::/7',     // ULA (unique local)
        'fe80::/10',    // link-local
        'ff00::/8',     // multicast
        '::/128',       // unspecified
    ];

    public static function isPrivate(string $ip): bool
    {
        $ip = trim($ip, " \t[]"); // aceita "[::1]" com brackets

        if ($ip === '') {
            return false;
        }

        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) !== false) {
            foreach (self::V4_PRIVATE_CIDRS as $cidr) {
                if (self::ipv4InCidr($ip, $cidr)) {
                    return true;
                }
            }

            return false;
        }

        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6) !== false) {
            foreach (self::V6_PRIVATE_CIDRS as $cidr) {
                if (self::ipv6InCidr($ip, $cidr)) {
                    return true;
                }
            }

            return false;
        }

        return false;
    }

    public static function ipv4InCidr(string $ip, string $cidr): bool
    {
        [$subnet, $bits] = array_pad(explode('/', $cidr, 2), 2, '32');
        $bits = (int) $bits;

        $ipLong     = ip2long($ip);
        $subnetLong = ip2long($subnet);

        if ($ipLong === false || $subnetLong === false) {
            return false;
        }

        if ($bits <= 0)  return true;
        if ($bits > 32)  return false;

        $mask = -1 << (32 - $bits);
        $mask &= 0xFFFFFFFF;

        return ($ipLong & $mask) === ($subnetLong & $mask);
    }

    public static function ipv6InCidr(string $ip, string $cidr): bool
    {
        [$subnet, $bits] = array_pad(explode('/', $cidr, 2), 2, '128');
        $bits = (int) $bits;

        $ipBin     = @inet_pton($ip);
        $subnetBin = @inet_pton($subnet);

        if ($ipBin === false || $subnetBin === false || strlen($ipBin) !== 16 || strlen($subnetBin) !== 16) {
            return false;
        }

        if ($bits <= 0)   return true;
        if ($bits > 128)  return false;

        $fullBytes = intdiv($bits, 8);
        $partial   = $bits % 8;

        if ($fullBytes > 0) {
            if (substr($ipBin, 0, $fullBytes) !== substr($subnetBin, 0, $fullBytes)) {
                return false;
            }
        }

        if ($partial === 0) {
            return true;
        }

        $mask    = chr(0xFF << (8 - $partial) & 0xFF);
        $ipByte  = $ipBin[$fullBytes];
        $subByte = $subnetBin[$fullBytes];

        return (ord($ipByte) & ord($mask)) === (ord($subByte) & ord($mask));
    }
}
