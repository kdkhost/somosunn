<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Support\MarketplaceFee;
use Illuminate\Support\Facades\Auth;

class MarketplaceController extends Controller
{
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
        $mpAccessToken = (string) (config('payments.mercadopago.access_token') ?? '');
        $mpPublicKey = (string) (config('payments.mercadopago.public_key') ?? '');

        $paymentsConfigured = $mpAccessToken !== '' && $mpPublicKey !== '';
        $webhookUrl = route('api.webhooks.mercadopago');

        return view('panel.marketplace.payments', compact('paymentsConfigured', 'webhookUrl'));
    }

    public function editPayment()
    {
        $gateway = \App\Models\GatewayAccount::firstOrNew(['user_id' => Auth::id(), 'provider' => 'mercadopago']);
        return view('panel.marketplace.connect', compact('gateway'));
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

