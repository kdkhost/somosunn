<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

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
        'extra' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Resolve o MercadoPago para um vendedor.
     *
     * Prioridade:
     * 1. Credenciais conectadas do vendedor em gateway_accounts.
     * 2. Credenciais globais da plataforma salvas na tabela settings.
     *
     * @return array{
     *   mpEnabled: bool,
     *   mpPublicKey: string,
     *   useGlobalCredentials: bool,
     *   source: string
     * }
     */
    public static function resolveForSeller(int $sellerId): array
    {
        $account = null;

        if ($sellerId > 0) {
            $account = self::query()
                ->where('user_id', $sellerId)
                ->where('provider', 'mercadopago')
                ->where('enabled', true)
                ->first();
        }

        if ($account) {
            $mpPublicKey = trim((string) ($account->public_key ?? ''));
            $mpToken = trim((string) ($account->access_token ?? ''));
            $mpEnabled = $mpPublicKey !== '' && $mpToken !== '';

            if ($mpEnabled) {
                return [
                    'mpEnabled' => true,
                    'mpPublicKey' => $mpPublicKey,
                    'source' => 'seller',
                ];
            }
        }

        return static::resolveGlobalSettings();
    }

    private static function resolveGlobalSettings(): array
    {
        $mpEnv = (string) Setting::get('mercadopago_env', 'sandbox');
        $mpPrefix = $mpEnv === 'production' ? 'mercadopago_prod_' : 'mercadopago_sandbox_';

        $mpPublicKey = trim((string) (Setting::get($mpPrefix . 'public_key') ?: Setting::get('mercadopago_public_key', '')));
        $mpToken = trim((string) (Setting::get($mpPrefix . 'access_token') ?: Setting::get('mercadopago_access_token', '')));

        $mpEnabled = $mpPublicKey !== '' && $mpToken !== '';

        return [
            'mpEnabled' => $mpEnabled,
            'mpPublicKey' => $mpEnabled ? $mpPublicKey : '',
            'useGlobalCredentials' => true,
            'source' => 'global',
        ];
    }

    /**
     * Resolve credenciais SumUp para um vendedor.
     * Prioridade: credenciais do vendedor > credenciais globais da plataforma.
     */
    public static function resolveForSellerSumUp(int $sellerId): array
    {
        if ($sellerId > 0) {
            $account = self::query()
                ->where('user_id', $sellerId)
                ->where('provider', 'sumup')
                ->where('enabled', true)
                ->first();

            if ($account && !empty($account->access_token)) {
                $extra = $account->extra ?? [];
                return [
                    'sumupEnabled'  => true,
                    'apiKey'        => trim($account->access_token),
                    'merchantCode'  => $extra['merchant_code'] ?? '',
                    'source'        => 'seller',
                ];
            }
        }

        $apiKey       = trim((string) (Setting::get('sumup_api_key') ?: config('payments.sumup.api_key', '')));
        $merchantCode = trim((string) (Setting::get('sumup_merchant_code') ?: config('payments.sumup.merchant_code', '')));
        $enabled      = $apiKey !== '' && $merchantCode !== '';

        return [
            'sumupEnabled' => $enabled,
            'apiKey'       => $apiKey,
            'merchantCode' => $merchantCode,
            'source'       => 'global',
        ];
    }

    /**
     * Resolve o gateway ativo para um vendedor (Mercado Pago OU SumUp).
     * Retorna informações sobre qual gateway está ativo e suas configurações.
     *
     * Prioridade:
     * 1. Credenciais conectadas do vendedor em gateway_accounts (enabled = true)
     * 2. Credenciais globais da plataforma salvas na tabela settings
     *
     * @return array{
     *   provider: string|null,
     *   enabled: bool,
     *   config: array,
     *   source: string
     * }
     */
    public static function resolveActiveGatewayForSeller(int $sellerId): array
    {
        $account = null;

        if ($sellerId > 0) {
            $account = self::query()
                ->where('user_id', $sellerId)
                ->where('enabled', true)
                ->whereIn('provider', ['mercadopago', 'sumup'])
                ->first();
        }

        if ($account) {
            if ($account->provider === 'mercadopago') {
                $mpPublicKey = trim((string) ($account->public_key ?? ''));
                $mpToken = trim((string) ($account->access_token ?? ''));
                $mpEnabled = $mpPublicKey !== '' && $mpToken !== '';

                if ($mpEnabled) {
                    return [
                        'provider' => 'mercadopago',
                        'enabled' => true,
                        'config' => [
                            'mpEnabled' => true,
                            'mpPublicKey' => $mpPublicKey,
                            'mpAccessToken' => $mpToken,
                        ],
                        'source' => 'seller',
                    ];
                }
            } elseif ($account->provider === 'sumup') {
                $apiKey = trim((string) ($account->access_token ?? ''));
                $extra = $account->extra ?? [];
                $merchantCode = $extra['merchant_code'] ?? '';
                $sumupEnabled = $apiKey !== '';

                if ($sumupEnabled) {
                    return [
                        'provider' => 'sumup',
                        'enabled' => true,
                        'config' => [
                            'sumupEnabled' => true,
                            'apiKey' => $apiKey,
                            'merchantCode' => $merchantCode,
                        ],
                        'source' => 'seller',
                    ];
                }
            }
        }

        // Fallback para credenciais globais
        // Verificar Mercado Pago global primeiro
        $mpGlobal = static::resolveGlobalSettings();
        if ($mpGlobal['mpEnabled']) {
            return [
                'provider' => 'mercadopago',
                'enabled' => true,
                'config' => $mpGlobal,
                'source' => 'global',
            ];
        }

        // Verificar SumUp global
        $apiKey = trim((string) (Setting::get('sumup_api_key') ?: config('payments.sumup.api_key', '')));
        $merchantCode = trim((string) (Setting::get('sumup_merchant_code') ?: config('payments.sumup.merchant_code', '')));
        $sumupEnabled = $apiKey !== '' && $merchantCode !== '';

        if ($sumupEnabled) {
            return [
                'provider' => 'sumup',
                'enabled' => true,
                'config' => [
                    'sumupEnabled' => true,
                    'apiKey' => $apiKey,
                    'merchantCode' => $merchantCode,
                ],
                'source' => 'global',
            ];
        }

        // Nenhum gateway ativo
        return [
            'provider' => null,
            'enabled' => false,
            'config' => [],
            'source' => 'none',
        ];
    }
}
