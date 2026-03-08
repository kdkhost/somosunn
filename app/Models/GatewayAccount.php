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
     * Resolve os gateways disponiveis para um vendedor.
     *
     * Prioridade:
     * 1. Credenciais conectadas do vendedor em gateway_accounts.
     * 2. Credenciais globais da plataforma salvas na tabela settings.
     *
     * O fallback global so acontece quando o vendedor nao tem nenhum gateway
     * realmente utilizavel para o checkout atual.
     *
     * @return array{
     *   mpEnabled: bool,
     *   psEnabled: bool,
     *   preferredGateway: string|null,
     *   mpPublicKey: string,
     *   psPublicKey: string,
     *   useGlobalCredentials: bool,
     *   source: string
     * }
     */
    public static function resolveForSeller(int $sellerId): array
    {
        $accounts = collect();

        if ($sellerId > 0) {
            $accounts = self::query()
                ->where('user_id', $sellerId)
                ->where('enabled', true)
                ->get()
                ->keyBy('provider');
        }

        $sellerGateways = static::resolveSellerAccounts($accounts);
        if ($sellerGateways['mpEnabled'] || $sellerGateways['psEnabled']) {
            return $sellerGateways;
        }

        if ($sellerId > 0 && $accounts->isNotEmpty()) {
            \Illuminate\Support\Facades\Log::warning('GatewayAccount::resolveForSeller - fallback para credenciais globais', [
                'seller_id' => $sellerId,
                'records' => $accounts->map(fn (GatewayAccount $account) => [
                    'id' => $account->id,
                    'provider' => $account->provider,
                    'enabled' => (bool) $account->enabled,
                    'has_public_key' => trim((string) ($account->public_key ?? '')) !== '',
                    'has_token' => trim((string) ($account->access_token ?? '')) !== '',
                ])->values()->toArray(),
            ]);
        }

        return static::resolveGlobalSettings();
    }

    private static function resolveSellerAccounts(Collection $accounts): array
    {
        /** @var GatewayAccount|null $mercadoPago */
        $mercadoPago = $accounts->get('mercadopago');
        /** @var GatewayAccount|null $pagSeguro */
        $pagSeguro = $accounts->get('pagseguro');

        $mpPublicKey = trim((string) ($mercadoPago->public_key ?? ''));
        $mpToken = trim((string) ($mercadoPago->access_token ?? ''));
        $psToken = trim((string) ($pagSeguro->access_token ?? ''));

        $mpEnabled = $mpPublicKey !== '' && $mpToken !== '';
        $psEnabled = $psToken !== '';

        $preferred = null;
        foreach (['mercadopago' => $mercadoPago, 'pagseguro' => $pagSeguro] as $provider => $account) {
            if (!$account) {
                continue;
            }

            if (($provider === 'mercadopago' && !$mpEnabled) || ($provider === 'pagseguro' && !$psEnabled)) {
                continue;
            }

            if (!empty($account->extra['is_preferred'])) {
                $preferred = $provider;
                break;
            }
        }

        if ($preferred === null) {
            $enabledProviders = array_values(array_filter([
                $mpEnabled ? 'mercadopago' : null,
                $psEnabled ? 'pagseguro' : null,
            ]));

            if (count($enabledProviders) === 1) {
                $preferred = $enabledProviders[0];
            }
        }

        return [
            'mpEnabled' => $mpEnabled,
            'psEnabled' => $psEnabled,
            'preferredGateway' => $preferred,
            'mpPublicKey' => $mpEnabled ? $mpPublicKey : '',
            'psPublicKey' => '',
            'useGlobalCredentials' => false,
            'source' => 'seller',
        ];
    }

    private static function resolveGlobalSettings(): array
    {
        $mpEnv = (string) Setting::get('mercadopago_env', 'sandbox');
        $mpPrefix = $mpEnv === 'production' ? 'mercadopago_prod_' : 'mercadopago_sandbox_';

        $mpPublicKey = trim((string) (Setting::get($mpPrefix . 'public_key') ?: Setting::get('mercadopago_public_key', '')));
        $mpToken = trim((string) (Setting::get($mpPrefix . 'access_token') ?: Setting::get('mercadopago_access_token', '')));

        $psEnv = (string) Setting::get('pagseguro_env', 'sandbox');
        $psPrefix = $psEnv === 'production' ? 'pagseguro_prod_' : 'pagseguro_sandbox_';
        $psToken = trim((string) (Setting::get($psPrefix . 'token') ?: Setting::get('pagseguro_token', '')));

        $mpEnabled = $mpPublicKey !== '' && $mpToken !== '';
        $psEnabled = $psToken !== '';

        $preferred = null;
        if ($mpEnabled xor $psEnabled) {
            $preferred = $mpEnabled ? 'mercadopago' : 'pagseguro';
        }

        return [
            'mpEnabled' => $mpEnabled,
            'psEnabled' => $psEnabled,
            'preferredGateway' => $preferred,
            'mpPublicKey' => $mpEnabled ? $mpPublicKey : '',
            'psPublicKey' => '',
            'useGlobalCredentials' => true,
            'source' => 'global',
        ];
    }
}
