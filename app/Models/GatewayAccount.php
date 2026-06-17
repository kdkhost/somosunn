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
        if (!static::isMercadoPagoEnabledByConfig()) {
            return [
                'mpEnabled' => false,
                'mpPublicKey' => '',
                'source' => 'disabled',
            ];
        }

        // Se o "vendedor" e admin/superadmin, ele e a propria plataforma.
        // Nesse caso o toggle global (mercadopago_enabled) deve controlar o gateway,
        // mesmo que haja uma gateway_account cadastrada em nome dele.
        if ($sellerId > 0 && static::isPlatformOwner($sellerId)) {
            return static::resolveGlobalSettings();
        }

        // 1. Primeiro tenta credenciais proprias do vendedor em gateway_accounts.
        //    Mesmo que o admin desative o MP globalmente, o vendedor comum que configurou
        //    sua propria conta pode continuar vendendo atraves dela.
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

        // 2. Fallback para credenciais globais da plataforma (apenas se habilitadas).
        return static::resolveGlobalSettings();
    }

    /**
     * Verifica se o user_id corresponde ao dono da plataforma (admin/superadmin).
     * Vendedor admin = plataforma -> toggle global decide seus gateways.
     */
    protected static function isPlatformOwner(int $userId): bool
    {
        static $cache = [];
        if (isset($cache[$userId])) {
            return $cache[$userId];
        }
        try {
            $user = \App\Models\User::find($userId);
            return $cache[$userId] = (bool) ($user && method_exists($user, 'isAdmin') && $user->isAdmin());
        } catch (\Throwable $e) {
            return $cache[$userId] = false;
        }
    }

    /**
     * Resolve credenciais SumUp para um vendedor (compatibilidade retroativa).
     */
    public static function resolveForSellerSumUp(int $sellerId): array
    {
        if (!static::isSumUpEnabledByConfig()) {
            return [
                'sumupEnabled' => false,
                'apiKey' => '',
                'merchantCode' => '',
                'source' => 'disabled',
            ];
        }

        // Se o vendedor e admin, toggle global SumUp decide.
        if ($sellerId > 0 && static::isPlatformOwner($sellerId)) {
            $sumupToggle = (int) Setting::get('sumup_enabled', 0) === 1;
            if (!$sumupToggle) {
                return [
                    'sumupEnabled' => false,
                    'apiKey'       => '',
                    'merchantCode' => '',
                    'source'       => 'disabled',
                ];
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

        // 1. Credenciais proprias do vendedor sempre tem prioridade.
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
        $mpToggle = static::isMercadoPagoEnabledByConfig()
            && (int) Setting::get('mercadopago_enabled', 0) === 1;

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
        if (!static::isMercadoPagoEnabledByConfig()) {
            return null;
        }

        // Se o vendedor e admin, toggle global decide.
        if ($sellerId > 0 && static::isPlatformOwner($sellerId)) {
            if (!(int) Setting::get('mercadopago_enabled', 0)) {
                return null;
            }
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

        // Vendedor comum: primeiro tenta credenciais proprias (independente do toggle global).
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

        // Sem credenciais proprias -> tenta globais (respeitando toggle)
        if (!(int) Setting::get('mercadopago_enabled', 0)) {
            return null;
        }
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
        if (!static::isSumUpEnabledByConfig()) {
            return null;
        }

        // Se o vendedor e admin, toggle global decide
        if ($sellerId > 0 && static::isPlatformOwner($sellerId)) {
            if (!(int) Setting::get('sumup_enabled', 0)) {
                return null;
            }
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

        // Vendedor comum: tenta credenciais proprias
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

        // Sem credenciais proprias -> globais (respeitando toggle)
        if (!(int) Setting::get('sumup_enabled', 0)) {
            return null;
        }
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

    private static function isMercadoPagoEnabledByConfig(): bool
    {
        return (bool) config('payments.mercadopago.enabled', true);
    }

    private static function isSumUpEnabledByConfig(): bool
    {
        return (bool) config('payments.sumup.enabled', true);
    }
}
