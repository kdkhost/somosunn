<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GatewayAccount extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'provider', // mercadopago, pagseguro
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
     * Resolve os gateways disponíveis para um vendedor.
     *
     * Regras:
     * - Retorna apenas contas ativas com access_token preenchido.
     * - Se o vendedor marcou um gateway como preferido (extra.is_preferred = true),
     *   apenas esse é retornado (o cliente não tem escolha).
     * - Se apenas um gateway estiver configurado, ele é o "preferido" implícito.
     *
     * @return array{mpEnabled: bool, psEnabled: bool, preferredGateway: string|null}
     */
    public static function resolveForSeller(int $sellerId): array
    {
        $accounts = self::where('user_id', $sellerId)
            ->where('enabled', true)
            ->whereNotNull('access_token')
            ->where('access_token', '!=', '')
            ->get()
            ->keyBy('provider');

        if ($accounts->isEmpty()) {
            // Log para diagnóstico — mostra o que existe na tabela para esse seller
            $allAccounts = self::where('user_id', $sellerId)->get();
            \Illuminate\Support\Facades\Log::warning('GatewayAccount::resolveForSeller — nenhuma conta ativa encontrada', [
                'seller_id'     => $sellerId,
                'total_records' => $allAccounts->count(),
                'records'       => $allAccounts->map(fn($a) => [
                    'id'           => $a->id,
                    'provider'     => $a->provider,
                    'enabled'      => $a->enabled,
                    'has_token'    => !empty($a->getAttributes()['access_token']),
                    'token_length' => strlen((string) $a->getAttributes()['access_token']),
                ])->toArray(),
            ]);
        }

        // Verificar se algum gateway está marcado como preferido
        $preferred = null;
        foreach ($accounts as $provider => $account) {
            if (!empty($account->extra['is_preferred'])) {
                $preferred = $provider;
                break;
            }
        }

        // Se houver preferência explícita, filtrar apenas esse gateway
        if ($preferred) {
            $accounts = $accounts->only([$preferred]);
        }

        // Preferido implícito: único gateway configurado
        $impliedPreferred = ($accounts->count() === 1) ? $accounts->keys()->first() : null;

        return [
            'mpEnabled'        => $accounts->has('mercadopago'),
            'psEnabled'        => $accounts->has('pagseguro'),
            'preferredGateway' => $preferred ?? $impliedPreferred,
        ];
    }
}
