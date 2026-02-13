<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\GatewayAccount;
use App\Models\Order;
use App\Support\MarketplaceFee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;

class MarketplaceController extends Controller
{
    public function gateway()
    {
        $userId = (int) Auth::id();
        $mercadoPagoAccount = GatewayAccount::firstOrNew([
            'user_id' => $userId,
            'provider' => 'mercadopago',
        ]);
        $pagSeguroAccount = GatewayAccount::firstOrNew([
            'user_id' => $userId,
            'provider' => 'pagseguro',
        ]);
        return view('panel.marketplace.gateway', compact('mercadoPagoAccount', 'pagSeguroAccount'));
    }
    public function index()
    {
        $userId = (int) Auth::id();

        $paidTotal = (float) Order::where('seller_id', $userId)->where('status', 'paid')->sum('total_amount');
        $platformFeeTotal = (float) Order::where('seller_id', $userId)->where('status', 'paid')->sum('platform_fee_amount');
        $netTotal = (float) max(0, $paidTotal - $platformFeeTotal);
        $paidCount = (int) Order::where('seller_id', $userId)->where('status', 'paid')->count();
        $pendingCount = (int) Order::where('seller_id', $userId)->where('status', 'pending')->count();
        $platformFeePercent = MarketplaceFee::percent();

        $mpAccessToken = (string) (config('payments.mercadopago.access_token') ?? '');
        $mpPublicKey = (string) (config('payments.mercadopago.public_key') ?? '');

        $paymentsConfigured = $mpAccessToken !== '' && $mpPublicKey !== '';

        return view('panel.marketplace.index', compact(
            'paidTotal',
            'platformFeeTotal',
            'netTotal',
            'paidCount',
            'pendingCount',
            'paymentsConfigured',
            'platformFeePercent'
        ));
    }

    public function payments()
    {
        $userId = (int) Auth::id();
        $mercadoPagoAccount = GatewayAccount::firstOrNew([
            'user_id' => $userId,
            'provider' => 'mercadopago',
        ]);
        $pagSeguroAccount = GatewayAccount::firstOrNew([
            'user_id' => $userId,
            'provider' => 'pagseguro',
        ]);

        $paymentsConfigured = (bool) (
            $mercadoPagoAccount->enabled
            && trim((string) ($mercadoPagoAccount->public_key ?? '')) !== ''
            && trim((string) ($mercadoPagoAccount->access_token ?? '')) !== ''
        );

        $webhookUrl = route('webhook.mercadopago', ['seller_id' => $userId]);
        $pagSeguroWebhookUrl = route('api.webhooks.pagseguro');

        return view('panel.marketplace.payments', compact(
            'paymentsConfigured',
            'webhookUrl',
            'pagSeguroWebhookUrl',
            'mercadoPagoAccount',
            'pagSeguroAccount'
        ));
    }

    public function updatePayments(Request $request)
    {
        $request->validate([
            'mp_public_key' => 'nullable|string|max:255',
            'mp_access_token' => 'nullable|string|max:255',
            'mp_env' => 'nullable|in:sandbox,production',
            'mp_enabled' => 'nullable|boolean',
            'ps_email' => 'nullable|email|max:255',
            'ps_access_token' => 'nullable|string|max:255',
            'ps_env' => 'nullable|in:sandbox,production',
            'ps_enabled' => 'nullable|boolean',
        ]);

        $userId = (int) Auth::id();

        $mercadoPagoAccount = GatewayAccount::firstOrNew([
            'user_id' => $userId,
            'provider' => 'mercadopago',
        ]);
        $pagSeguroAccount = GatewayAccount::firstOrNew([
            'user_id' => $userId,
            'provider' => 'pagseguro',
        ]);

        $mpEnabled = $request->boolean('mp_enabled');
        $mpPublicKeyInput = trim((string) $request->input('mp_public_key', ''));
        $mpAccessTokenInput = trim((string) $request->input('mp_access_token', ''));
        $mpPublicKey = $mpPublicKeyInput !== '' ? $mpPublicKeyInput : trim((string) ($mercadoPagoAccount->public_key ?? ''));
        $mpAccessToken = $mpAccessTokenInput !== '' ? $mpAccessTokenInput : trim((string) ($mercadoPagoAccount->access_token ?? ''));

        if ($mpEnabled && ($mpPublicKey === '' || $mpAccessToken === '')) {
            return back()->withInput()->with('error', 'Para ativar MercadoPago, informe Public Key e Access Token.');
        }

        $mpPayload = [
            'public_key' => $mpPublicKey !== '' ? $mpPublicKey : null,
            'enabled' => $mpEnabled,
            'extra' => array_merge(
                is_array($mercadoPagoAccount->extra) ? $mercadoPagoAccount->extra : [],
                ['env' => (string) $request->input('mp_env', 'production')]
            ),
        ];
        if ($mpAccessTokenInput !== '') {
            $mpPayload['access_token'] = $mpAccessTokenInput;
        }

        GatewayAccount::updateOrCreate(
            ['user_id' => $userId, 'provider' => 'mercadopago'],
            $mpPayload
        );

        $psEnabled = $request->boolean('ps_enabled');
        $psEmailInput = trim((string) $request->input('ps_email', ''));
        $psAccessTokenInput = trim((string) $request->input('ps_access_token', ''));
        $psEmail = $psEmailInput !== '' ? $psEmailInput : trim((string) ($pagSeguroAccount->client_id ?? ''));
        $psAccessToken = $psAccessTokenInput !== '' ? $psAccessTokenInput : trim((string) ($pagSeguroAccount->access_token ?? ''));

        if ($psEnabled && ($psEmail === '' || $psAccessToken === '')) {
            return back()->withInput()->with('error', 'Para ativar PagSeguro, informe e-mail e token de acesso.');
        }

        $psPayload = [
            'client_id' => $psEmail !== '' ? $psEmail : null,
            'enabled' => $psEnabled,
            'extra' => array_merge(
                is_array($pagSeguroAccount->extra) ? $pagSeguroAccount->extra : [],
                ['env' => (string) $request->input('ps_env', 'production')]
            ),
        ];
        if ($psAccessTokenInput !== '') {
            $psPayload['access_token'] = $psAccessTokenInput;
        }

        GatewayAccount::updateOrCreate(
            ['user_id' => $userId, 'provider' => 'pagseguro'],
            $psPayload
        );

        return back()->with('success', 'Configurações de pagamento salvas com sucesso.');
    }

    public function testPaymentsConnection(Request $request)
    {
        $request->validate([
            'provider' => 'required|in:mercadopago,pagseguro',
            'access_token' => 'nullable|string|max:255',
            'public_key' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'env' => 'nullable|in:sandbox,production',
        ]);

        $provider = (string) $request->input('provider');
        $userId = (int) Auth::id();

        $account = GatewayAccount::query()
            ->where('user_id', $userId)
            ->where('provider', $provider)
            ->first();

        $token = trim((string) $request->input('access_token', ''));
        if ($token === '' && $account) {
            $token = trim((string) ($account->access_token ?? ''));
        }

        if ($token === '') {
            return response()->json([
                'ok' => false,
                'message' => 'Informe o Access Token para testar a conexão.',
            ], 422);
        }

        try {
            if ($provider === 'mercadopago') {
                $response = Http::timeout(15)
                    ->withToken($token)
                    ->acceptJson()
                    ->get('https://api.mercadopago.com/users/me');

                if ($response->successful()) {
                    $nickname = (string) ($response->json('nickname') ?? '');
                    return response()->json([
                        'ok' => true,
                        'message' => $nickname !== ''
                            ? "Conexão MercadoPago OK ({$nickname})."
                            : 'Conexão MercadoPago validada com sucesso.',
                    ]);
                }

                return response()->json([
                    'ok' => false,
                    'message' => 'Falha ao validar MercadoPago. Verifique token e ambiente.',
                    'status' => $response->status(),
                ], 422);
            }

            $env = trim((string) $request->input('env', data_get($account?->extra, 'env', 'production')));
            $baseUrl = $env === 'sandbox'
                ? 'https://sandbox.api.pagseguro.com'
                : 'https://api.pagseguro.com';

            $response = Http::timeout(15)
                ->withToken($token)
                ->acceptJson()
                ->get("{$baseUrl}/orders", ['page' => 1, 'per_page' => 1]);

            if ($response->successful()) {
                return response()->json([
                    'ok' => true,
                    'message' => 'Conexão PagSeguro validada com sucesso.',
                ]);
            }

            return response()->json([
                'ok' => false,
                'message' => 'Falha ao validar PagSeguro. Verifique token, e-mail e ambiente.',
                'status' => $response->status(),
            ], 422);
        } catch (\Throwable $e) {
            return response()->json([
                'ok' => false,
                'message' => 'Erro ao testar conexão: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function sales()
    {
        $userId = (int) Auth::id();

        $orders = Order::with(['user:id,name,email', 'items'])
            ->where('seller_id', $userId)
            ->latest('id')
            ->paginate(20);

        $paidTotal = (float) Order::where('seller_id', $userId)->where('status', 'paid')->sum('total_amount');
        $platformFeeTotal = (float) Order::where('seller_id', $userId)->where('status', 'paid')->sum('platform_fee_amount');
        $netTotal = (float) max(0, $paidTotal - $platformFeeTotal);
        $paidCount = (int) Order::where('seller_id', $userId)->where('status', 'paid')->count();

        return view('panel.marketplace.sales', compact('orders', 'paidTotal', 'platformFeeTotal', 'netTotal', 'paidCount'));
    }
}
