<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GatewayAccount extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'provider',
        'public_key',
        'access_token',
        'client_id',
        'client_secret',
        'webhook_secret',
        'pix_key',
        'enabled',
        'extra',
    ];

    protected $casts = [
        'enabled' => 'boolean',
        'extra'   => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // -------------------------------------------------------------------------
    // Resolução de gateways
    // -------------------------------------------------------------------------

    /**
     * Resolve o MercadoPago para um vendedor (compatibilidade retroativa).
     *
     * Prioridade:
     * 1. Credenciais do vendedor em gateway_accounts.
     * 2. Credenciais globais da plataforma (tabela settings).
     */
    public static function resolveForSeller(int $sellerId): array
    {
        // Respeita o toggle global do admin: se MP foi desativado em Settings,
        // nenhuma credencial (do vendedor ou global) pode ativa-lo.
        $mpToggle = (int) Setting::get('mercadopago_enabled', 0) === 1;
        if (!$mpToggle) {
            return [
                'mpEnabled'           => false,
                'mpPublicKey'         => '',
                'useGlobalCredentials' => true,
                'source'              => 'disabled',
            ];
        }

        if ($sellerId > 0) {
            $account = self::query()
                ->where('user_id', $sellerId)
                ->where('provider', 'mercadopago')
                ->where('enabled', true)
                ->first();

            if ($account) {
                $mpPublicKey = trim((string) ($account->public_key ?? ''));
                $mpToken     = trim((string) ($account->access_token ?? ''));

                if ($mpPublicKey !== '' && $mpToken !== '') {
                    return [
                        'mpEnabled'  => true,
                        'mpPublicKey' => $mpPublicKey,
                        'source'     => 'seller',
                    ];
                }
            }
        }

        return static::resolveGlobalSettings();
    }

    /**
     * Resolve credenciais SumUp para um vendedor (compatibilidade retroativa).
     */
    public static function resolveForSellerSumUp(int $sellerId): array
    {
        // Respeita o toggle global do admin
        $sumupToggle = (int) Setting::get('sumup_enabled', 0) === 1;
        if (!$sumupToggle) {
            return [
                'sumupEnabled' => false,
                'apiKey'       => '',
                'merchantCode' => '',
                'source'       => 'disabled',
            ];
        }

        if ($sellerId > 0) {
            $account = self::query()
                ->where('user_id', $sellerId)
                ->where('provider', 'sumup')
                ->where('enabled', true)
                ->first();

            if ($account && !empty($account->access_token)) {
                $extra = $account->extra ?? [];
                return [
                    'sumupEnabled' => true,
                    'apiKey'       => trim($account->access_token),
                    'merchantCode' => $extra['merchant_code'] ?? '',
                    'source'       => 'seller',
                ];
            }
        }

        $apiKey       = trim((string) (Setting::get('sumup_api_key') ?: config('payments.sumup.api_key', '')));
        $merchantCode = trim((string) (Setting::get('sumup_merchant_code') ?: config('payments.sumup.merchant_code', '')));

        return [
            'sumupEnabled' => $apiKey !== '' && $merchantCode !== '',
            'apiKey'       => $apiKey,
            'merchantCode' => $merchantCode,
            'source'       => 'global',
        ];
    }

    /**
     * Resolve o gateway ativo para um vendedor — retorna apenas o primeiro.
     *
     * @deprecated Use resolveAllActiveGatewaysForSeller() para suporte a múltiplos gateways.
     */
    public static function resolveActiveGatewayForSeller(int $sellerId): array
    {
        $gateways = static::resolveAllActiveGatewaysForSeller($sellerId);

        if (empty($gateways)) {
            return [
                'provider' => null,
                'enabled'  => false,
                'config'   => [],
                'source'   => 'none',
            ];
        }

        return $gateways[0];
    }

    /**
     * Resolve TODOS os gateways ativos para um vendedor.
     *
     * Prioridade por gateway:
     *   1. Credenciais do vendedor em gateway_accounts (enabled = true)
     *   2. Credenciais globais da plataforma (tabela settings)
     *
     * Retorna apenas gateways com enabled = true e credenciais válidas.
     * Retorna [] se nenhum gateway estiver ativo.
     *
     * @return array<int, array{provider: string, enabled: bool, config: array, source: string}>
     */
    public static function resolveAllActiveGatewaysForSeller(int $sellerId): array
    {
        $result = [];

        $mp = static::resolveMercadoPagoForSeller($sellerId);
        if ($mp !== null) {
            $result[] = $mp;
        }

        $sumup = static::resolveSumUpForSeller($sellerId);
        if ($sumup !== null) {
            $result[] = $sumup;
        }

        return $result;
    }

    // -------------------------------------------------------------------------
    // Helpers privados
    // -------------------------------------------------------------------------

    private static function resolveGlobalSettings(): array
    {
        // Respeita o toggle global - se desativado, nao retorna como habilitado
        $mpToggle = (int) Setting::get('mercadopago_enabled', 0) === 1;

        $mpEnv    = (string) Setting::get('mercadopago_env', 'sandbox');
        $mpPrefix = $mpEnv === 'production' ? 'mercadopago_prod_' : 'mercadopago_sandbox_';

        $mpPublicKey = trim((string) (Setting::get($mpPrefix . 'public_key') ?: Setting::get('mercadopago_public_key', '')));
        $mpToken     = trim((string) (Setting::get($mpPrefix . 'access_token') ?: Setting::get('mercadopago_access_token', '')));
        $mpEnabled   = $mpToggle && $mpPublicKey !== '' && $mpToken !== '';

        return [
            'mpEnabled'           => $mpEnabled,
            'mpPublicKey'         => $mpEnabled ? $mpPublicKey : '',
            'useGlobalCredentials' => true,
            'source'              => $mpToggle ? 'global' : 'disabled',
        ];
    }

    /**
     * Resolve Mercado Pago para um vendedor (seller → global).
     * Retorna null se não estiver habilitado ou sem credenciais válidas.
     */
    private static function resolveMercadoPagoForSeller(int $sellerId): ?array
    {
        // Só retorna se MP estiver habilitado nas settings
        if (!(int) Setting::get('mercadopago_enabled', 0)) {
            return null;
        }

        // Tentar credenciais do vendedor
        if ($sellerId > 0) {
            $account = self::query()
                ->where('user_id', $sellerId)
                ->where('provider', 'mercadopago')
                ->where('enabled', true)
                ->first();

            if ($account) {
                $mpPublicKey = trim((string) ($account->public_key ?? ''));
                $mpToken     = trim((string) ($account->access_token ?? ''));

                if ($mpPublicKey !== '' && $mpToken !== '') {
                    return [
                        'provider' => 'mercadopago',
                        'enabled'  => true,
                        'config'   => [
                            'mpEnabled'     => true,
                            'mpPublicKey'   => $mpPublicKey,
                            'mpAccessToken' => $mpToken,
                        ],
                        'source' => 'seller',
                    ];
                }
            }
        }

        // Fallback para credenciais globais
        $mpGlobal = static::resolveGlobalSettings();
        if ($mpGlobal['mpEnabled']) {
            return [
                'provider' => 'mercadopago',
                'enabled'  => true,
                'config'   => $mpGlobal,
                'source'   => 'global',
            ];
        }

        return null;
    }

    /**
     * Resolve SumUp para um vendedor (seller → global).
     * Retorna null se não estiver habilitado ou sem credenciais válidas.
     */
    private static function resolveSumUpForSeller(int $sellerId): ?array
    {
        // Só retorna se SumUp estiver habilitado nas settings
        if (!(int) Setting::get('sumup_enabled', 0)) {
            return null;
        }

        // Tentar credenciais do vendedor
        if ($sellerId > 0) {
            $account = self::query()
                ->where('user_id', $sellerId)
                ->where('provider', 'sumup')
                ->where('enabled', true)
                ->first();

            if ($account && !empty($account->access_token)) {
                $extra        = $account->extra ?? [];
                $merchantCode = $extra['merchant_code'] ?? '';

                // Fallback para merchant_code global
                if (empty($merchantCode)) {
                    $merchantCode = trim((string) (Setting::get('sumup_merchant_code')
                        ?: config('payments.sumup.merchant_code', '')));
                }

                return [
                    'provider' => 'sumup',
                    'enabled'  => true,
                    'config'   => [
                        'sumupEnabled' => true,
                        'apiKey'       => trim($account->access_token),
                        'merchantCode' => $merchantCode,
                    ],
                    'source' => 'seller',
                ];
            }
        }

        // Fallback para credenciais globais
        $apiKey       = trim((string) (Setting::get('sumup_api_key') ?: config('payments.sumup.api_key', '')));
        $merchantCode = trim((string) (Setting::get('sumup_merchant_code') ?: config('payments.sumup.merchant_code', '')));

        if ($apiKey !== '') {
            return [
                'provider' => 'sumup',
                'enabled'  => true,
                'config'   => [
                    'sumupEnabled' => true,
                    'apiKey'       => $apiKey,
                    'merchantCode' => $merchantCode,
                ],
                'source' => 'global',
            ];
        }

        return null;
    }
}
