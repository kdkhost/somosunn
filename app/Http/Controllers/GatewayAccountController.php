<?php

namespace App\Http\Controllers;

use App\Models\GatewayAccount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class GatewayAccountController extends Controller
{
    public function index()
    {
        $gateway = GatewayAccount::firstOrNew(['user_id' => Auth::id(), 'provider' => 'mercadopago']);
        return view('settings.payment', compact('gateway'));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'public_key' => 'required|string',
            'access_token' => 'required|string',
        ]);

        $gateway = GatewayAccount::updateOrCreate(
            ['user_id' => Auth::id(), 'provider' => 'mercadopago'],
            [
                'public_key' => $validated['public_key'],
                'access_token' => $validated['access_token'],
                'enabled' => true,
            ]
        );

        return back()->with('success', 'Configurações de pagamento salvas.');
    }
}
