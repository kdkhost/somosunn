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
}
