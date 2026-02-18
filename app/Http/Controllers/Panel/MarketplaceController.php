<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Support\MarketplaceFee;
use Illuminate\Support\Facades\Auth;

class MarketplaceController extends Controller
{
    public function index(\App\Services\Payment\MercadoPagoService $mpService)
    {
        $userId = (int) Auth::id();

        $paidTotal = (float) Order::where('seller_id', $userId)->where('status', 'paid')->sum('total_amount');
        $platformFeeTotal = (float) Order::where('seller_id', $userId)->where('status', 'paid')->sum('platform_fee_amount');
        $netTotal = (float) max(0, $paidTotal - $platformFeeTotal);
        $paidCount = (int) Order::where('seller_id', $userId)->where('status', 'paid')->count();
        $pendingCount = (int) Order::where('seller_id', $userId)->where('status', 'pending')->count();
        $platformFeePercent = MarketplaceFee::percent();

        $mpAccount = \App\Models\GatewayAccount::where('user_id', $userId)
            ->where('provider', 'mercadopago')
            ->first();

        $paymentsConfigured = $mpAccount && !empty($mpAccount->access_token);

        $balance = ['total_amount' => 0, 'available_balance' => 0, 'unavailable_balance' => 0];
        if ($paymentsConfigured) {
            try {
                $balanceData = $mpService->getBalance($userId);
                $balance = [
                    'total_amount' => data_get($balanceData, 'total_amount', 0),
                    'available_balance' => data_get($balanceData, 'available_balance', 0),
                    'unavailable_balance' => data_get($balanceData, 'unavailable_balance', 0),
                ];
            } catch (\Exception $e) {
                \Log::warning('Erro ao buscar saldo MP: ' . $e->getMessage());
            }
        }

        return view('panel.marketplace.index', compact(
            'paidTotal',
            'platformFeeTotal',
            'netTotal',
            'paidCount',
            'pendingCount',
            'paymentsConfigured',
            'platformFeePercent',
            'balance'
        ));
    }

    public function payments()
    {
        $userId = Auth::id();
        $account = \App\Models\GatewayAccount::where('user_id', $userId)
            ->where('provider', 'mercadopago')
            ->first();

        $paymentsConfigured = $account && !empty($account->access_token) && !empty($account->public_key);
        $webhookUrl = route('api.webhooks.mercadopago', ['seller_id' => $userId]);

        return view('panel.marketplace.payments', compact('paymentsConfigured', 'webhookUrl', 'account'));
    }

    public function editPayment()
    {
        $mercadopago = \App\Models\GatewayAccount::firstOrNew(['user_id' => Auth::id(), 'provider' => 'mercadopago']);
        $pagseguro = \App\Models\GatewayAccount::firstOrNew(['user_id' => Auth::id(), 'provider' => 'pagseguro']);

        return view('panel.marketplace.connect', compact('mercadopago', 'pagseguro'));
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

