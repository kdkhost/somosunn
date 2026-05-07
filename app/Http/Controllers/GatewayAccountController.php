<?php

namespace App\Http\Controllers;

use App\Models\GatewayAccount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class GatewayAccountController extends Controller
{
    public function index()
    {
        $gateway = GatewayAccount::firstOrNew(['user_id' => Auth::id(), 'provider' => 'mercadopago']);
        $sumupAccount = GatewayAccount::where('user_id', Auth::id())
            ->where('provider', 'sumup')
            ->first();

        return view('settings.payment', compact('gateway', 'sumupAccount'));
    }

    public function update(Request $request)
    {
        $provider = $request->input('provider', 'mercadopago');

        $validated = $request->validate([
            'provider' => 'nullable|in:mercadopago,sumup',
            'public_key' => 'nullable|string',
            'access_token' => 'nullable|string',
            'max_installments' => 'nullable|integer|min:1|max:12',
            'pass_fee' => 'nullable|boolean',
            'methods' => 'nullable|array',
            'sumup_access_token' => 'required_if:provider,sumup|nullable|string',
            'sumup_merchant_code' => 'required_if:provider,sumup|nullable|string|max:80',
        ], [
            'provider.in' => 'Selecione um gateway de pagamento válido.',
            'sumup_access_token.required_if' => 'Informe a API Key da SumUp para ativar este gateway.',
            'sumup_merchant_code.required_if' => 'Informe o Merchant Code da SumUp para ativar este gateway.',
        ]);

        if ($provider === 'sumup') {
            $sumupAccount = GatewayAccount::firstOrNew([
                'user_id' => Auth::id(),
                'provider' => 'sumup',
            ]);

            $extra = (array) ($sumupAccount->extra ?? []);
            $extra['merchant_code'] = strtoupper(trim((string) $validated['sumup_merchant_code']));

            $sumupAccount->fill([
                'access_token' => trim((string) $validated['sumup_access_token']),
                'enabled' => true,
                'extra' => $extra,
            ]);
            $sumupAccount->save();

            return back()->with('success', 'Credenciais da SumUp salvas com sucesso.');
        }

        $data = [];

        if ($request->has('public_key')) {
            $data['public_key'] = $validated['public_key'];
        }

        if ($request->has('access_token')) {
            $data['access_token'] = $validated['access_token'];
        }

        $account = GatewayAccount::where('user_id', Auth::id())
            ->where('provider', 'mercadopago')
            ->first();

        $extra = $account ? (array) $account->extra : [];
        $extra['max_installments'] = (int) ($validated['max_installments'] ?? 12);
        $extra['pass_fee'] = (bool) ($validated['pass_fee'] ?? false);
        $extra['enabled_methods'] = $validated['methods'] ?? ['credit_card', 'pix', 'ticket'];

        $data['extra'] = $extra;

        GatewayAccount::updateOrCreate(
            ['user_id' => Auth::id(), 'provider' => $provider],
            $data
        );

        if ($provider === 'mercadopago' && Auth::user()->isAdmin()) {
            $mpEnv = \App\Models\Setting::get('mercadopago_env', 'sandbox');
            $prefix = $mpEnv === 'production' ? 'mercadopago_prod_' : 'mercadopago_sandbox_';

            if (!empty($data['public_key'])) {
                \App\Models\Setting::updateOrCreate(['key' => $prefix . 'public_key'], ['value' => $data['public_key']]);
            }

            if (!empty($data['access_token'])) {
                \App\Models\Setting::updateOrCreate(['key' => $prefix . 'access_token'], ['value' => $data['access_token']]);
            }
        }

        return back()->with('success', 'Configuracoes de ' . ucfirst($provider) . ' salvas com sucesso.');
    }

    public function connect(Request $request)
    {
        $appId = config('payments.mercadopago.client_id');
        $redirectUri = config('payments.mercadopago.redirect_uri');
        $state = Str::random(40);
        $popup = $request->boolean('popup');
        $returnRoute = $request->input('return_route', 'panel.marketplace.payments');

        $codeVerifier = rtrim(strtr(base64_encode(random_bytes(32)), '+/', '-_'), '=');
        $codeChallenge = rtrim(strtr(base64_encode(hash('sha256', $codeVerifier, true)), '+/', '-_'), '=');

        session([
            'mp_oauth_state' => $state,
            'mp_oauth_code_verifier' => $codeVerifier,
            'mp_oauth_popup' => $popup,
            'mp_oauth_return_route' => $returnRoute,
        ]);

        $params = http_build_query([
            'client_id' => $appId,
            'response_type' => 'code',
            'platform_id' => 'mp',
            'state' => $state,
            'redirect_uri' => $redirectUri,
            'code_challenge' => $codeChallenge,
            'code_challenge_method' => 'S256',
        ]);

        return redirect('https://auth.mercadopago.com.br/authorization?' . $params);
    }

    public function callback(Request $request)
    {
        $state = session('mp_oauth_state');
        $popup = (bool) session('mp_oauth_popup', false);
        $returnRoute = (string) session('mp_oauth_return_route', 'panel.marketplace.payments');

        if (!$state || $request->input('state') !== $state) {
            return $this->oauthResultResponse(
                false,
                'Estado invalido na autenticacao. Tente novamente.',
                $popup,
                $returnRoute
            );
        }

        $code = $request->input('code');
        if (!$code) {
            return $this->oauthResultResponse(
                false,
                'Codigo de autorizacao nao recebido.',
                $popup,
                $returnRoute
            );
        }

        $clientId = config('payments.mercadopago.client_id');
        $clientSecret = config('payments.mercadopago.client_secret');
        $redirectUri = config('payments.mercadopago.redirect_uri');

        Log::info('OAuth MP callback iniciado', [
            'user_id' => Auth::id(),
            'client_id' => $clientId,
            'has_secret' => !empty($clientSecret),
            'redirect_uri' => $redirectUri,
            'code_length' => strlen($code),
            'popup' => $popup,
        ]);

        if (empty($clientId) || empty($clientSecret)) {
            Log::error('OAuth MP: client_id ou client_secret nao configurados no .env');

            return $this->oauthResultResponse(
                false,
                'Configuracao OAuth incompleta no servidor. Contate o administrador.',
                $popup,
                $returnRoute
            );
        }

        $codeVerifier = session('mp_oauth_code_verifier');

        try {
            $payload = [
                'client_secret' => $clientSecret,
                'client_id' => $clientId,
                'grant_type' => 'authorization_code',
                'code' => $code,
                'redirect_uri' => $redirectUri,
            ];

            if (!empty($codeVerifier)) {
                $payload['code_verifier'] = $codeVerifier;
            }

            $response = Http::post('https://api.mercadopago.com/oauth/token', $payload);

            Log::info('OAuth MP resposta', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            if ($response->successful()) {
                $data = $response->json();

                GatewayAccount::updateOrCreate(
                    ['user_id' => Auth::id(), 'provider' => 'mercadopago'],
                    [
                        'public_key' => $data['public_key'] ?? null,
                        'access_token' => $data['access_token'],
                        'refresh_token' => $data['refresh_token'] ?? null,
                        'token_expires_in' => $data['expires_in'] ?? null,
                        'user_id_mp' => $data['user_id'] ?? null,
                        'enabled' => true,
                        'extra' => array_merge($data, ['marketplace_enabled' => true]),
                    ]
                );

                return $this->oauthResultResponse(
                    true,
                    'Conta do Mercado Pago conectada com sucesso!',
                    $popup,
                    $returnRoute,
                    [
                        'public_key' => $data['public_key'] ?? null,
                        'user_id_mp' => $data['user_id'] ?? null,
                    ]
                );
            }

            Log::error('Erro OAuth Mercado Pago', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            $mpError = $response->json('error_description') ?? $response->json('message') ?? 'Erro desconhecido';

            return $this->oauthResultResponse(
                false,
                'Erro ao conectar: ' . $mpError,
                $popup,
                $returnRoute
            );
        } catch (\Exception $e) {
            Log::error('Excecao OAuth MP: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);

            return $this->oauthResultResponse(
                false,
                'Erro interno: ' . $e->getMessage(),
                $popup,
                $returnRoute
            );
        }
    }

    private function oauthResultResponse(
        bool $success,
        string $message,
        bool $popup,
        string $returnRoute,
        array $payload = []
    ) {
        session()->forget([
            'mp_oauth_state',
            'mp_oauth_code_verifier',
            'mp_oauth_popup',
            'mp_oauth_return_route',
        ]);

        $safeRoute = \Illuminate\Support\Facades\Route::has($returnRoute)
            ? $returnRoute
            : 'panel.marketplace.payments';

        if ($popup) {
            return response()->view('gateway.mercadopago.popup-result', [
                'success' => $success,
                'message' => $message,
                'payload' => $payload,
                'redirectUrl' => route($safeRoute),
            ]);
        }

        return redirect()->route($safeRoute)->with($success ? 'success' : 'error', $message);
    }
}
