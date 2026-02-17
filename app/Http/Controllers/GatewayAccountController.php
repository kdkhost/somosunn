<?php

namespace App\Http\Controllers;

use App\Models\GatewayAccount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class GatewayAccountController extends Controller
{
    public function index()
    {
        $gateway = GatewayAccount::firstOrNew(['user_id' => Auth::id(), 'provider' => 'mercadopago']);
        return view('settings.payment', compact('gateway'));
    }

    public function update(Request $request)
    {
        $provider = $request->input('provider', 'mercadopago');

        if ($provider === 'pagseguro') {
            $validated = $request->validate([
                'email' => 'required|email',
                'token' => 'required|string',
            ]);

            $data = [
                'client_id' => $validated['email'], // Using client_id to store email for consistency or extra field
                'access_token' => $validated['token'],
                'enabled' => true,
            ];
            // Or prioritize extra fields if preferred, but usually access_token is fine for token
        } else {
            // Mercado Pago Default
            $validated = $request->validate([
                'public_key' => 'required|string',
                'access_token' => 'required|string',
            ]);

            $data = [
                'public_key' => $validated['public_key'],
                'access_token' => $validated['access_token'],
                'enabled' => true,
            ];
        }

        GatewayAccount::updateOrCreate(
            ['user_id' => Auth::id(), 'provider' => $provider],
            $data
        );

        return back()->with('success', 'Configurações de ' . ucfirst($provider) . ' salvas com sucesso.');
    }

    public function connect()
    {
        $appId = config('payments.mercadopago.client_id');
        $redirectUri = config('payments.mercadopago.redirect_uri');
        $state = Str::random(40);

        session(['mp_oauth_state' => $state]);

        $url = "https://auth.mercadopago.com.br/authorization?client_id={$appId}&response_type=code&platform_id=mp&state={$state}&redirect_uri={$redirectUri}";

        return redirect($url);
    }

    public function callback(Request $request)
    {
        $state = session('mp_oauth_state');

        if (!$state || $request->input('state') !== $state) {
            return redirect()->route('settings')->with('error', 'Estado inválido na autenticação. Tente novamente.');
        }

        $code = $request->input('code');
        if (!$code) {
            return redirect()->route('settings')->with('error', 'Código de autorização não recebido.');
        }

        try {
            $response = Http::post('https://api.mercadopago.com/oauth/token', [
                'client_secret' => config('payments.mercadopago.client_secret'),
                'client_id' => config('payments.mercadopago.client_id'),
                'grant_type' => 'authorization_code',
                'code' => $code,
                'redirect_uri' => config('payments.mercadopago.redirect_uri'),
            ]);

            if ($response->successful()) {
                $data = $response->json();

                GatewayAccount::updateOrCreate(
                    ['user_id' => Auth::id(), 'provider' => 'mercadopago'],
                    [
                        'public_key' => $data['public_key'],
                        'access_token' => $data['access_token'],
                        'refresh_token' => $data['refresh_token'],
                        'token_expires_in' => $data['expires_in'],
                        'user_id_mp' => $data['user_id'],
                        'enabled' => true,
                        'extra' => $data // Salva todo o payload JSON para debug/futuro
                    ]
                );

                return redirect()->route('settings')->with('success', 'Conta do Mercado Pago conectada com sucesso!');
            }

            Log::error('Erro OAuth Mercado Pago: ' . $response->body());
            return redirect()->route('settings')->with('error', 'Houve um erro ao conectar com o Mercado Pago.');

        } catch (\Exception $e) {
            Log::error('Exceção OAuth MP: ' . $e->getMessage());
            return redirect()->route('settings')->with('error', 'Erro interno ao processar conexão.');
        }
    }
}
