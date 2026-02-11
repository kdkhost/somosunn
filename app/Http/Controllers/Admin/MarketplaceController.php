<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GatewayAccount;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MarketplaceController extends Controller
{
    public function index()
    {
        $userId = (int) Auth::id();

        $gateway = GatewayAccount::where('user_id', $userId)
            ->where('provider', 'mercadopago')
            ->first();

        $paidTotal = (float) Order::where('seller_id', $userId)->where('status', 'paid')->sum('total_amount');
        $paidCount = (int) Order::where('seller_id', $userId)->where('status', 'paid')->count();
        $pendingCount = (int) Order::where('seller_id', $userId)->where('status', 'pending')->count();

        return view('admin.marketplace.index', compact('gateway', 'paidTotal', 'paidCount', 'pendingCount'));
    }

    public function payments()
    {
        $gateway = GatewayAccount::firstOrNew([
            'user_id' => (int) Auth::id(),
            'provider' => 'mercadopago',
        ]);

        return view('admin.marketplace.payments', compact('gateway'));
    }

    public function updatePayments(Request $request)
    {
        $existing = GatewayAccount::where('user_id', (int) Auth::id())
            ->where('provider', 'mercadopago')
            ->first();

        $rules = [
            'public_key' => 'required|string',
            'access_token' => ($existing && (string) $existing->access_token !== '')
                ? 'nullable|string'
                : 'required|string',
        ];

        $validated = $request->validate($rules);

        $data = [
            'public_key' => $validated['public_key'],
            'enabled' => true,
        ];

        $accessToken = trim((string) ($validated['access_token'] ?? ''));
        if ($accessToken !== '') {
            $data['access_token'] = $accessToken;
        }

        GatewayAccount::updateOrCreate(
            ['user_id' => (int) Auth::id(), 'provider' => 'mercadopago'],
            $data
        );

        return redirect()
            ->route('admin.marketplace.payments')
            ->with('success', 'Credenciais do MercadoPago salvas com sucesso.');
    }

    public function sales()
    {
        $userId = (int) Auth::id();

        $orders = Order::with(['user:id,name,email', 'items'])
            ->where('seller_id', $userId)
            ->latest('id')
            ->paginate(20);

        $paidTotal = (float) Order::where('seller_id', $userId)->where('status', 'paid')->sum('total_amount');
        $paidCount = (int) Order::where('seller_id', $userId)->where('status', 'paid')->count();

        return view('admin.marketplace.sales', compact('orders', 'paidTotal', 'paidCount'));
    }
}

