<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MarketplaceController extends Controller
{
    public function index()
    {
        $userId = (int) Auth::id();

        $paidTotal = (float) Order::where('seller_id', $userId)->where('status', 'paid')->sum('total_amount');
        $paidCount = (int) Order::where('seller_id', $userId)->where('status', 'paid')->count();
        $pendingCount = (int) Order::where('seller_id', $userId)->where('status', 'pending')->count();

        $mpAccessToken = (string) (config('payments.mercadopago.access_token') ?? '');
        $mpPublicKey = (string) (config('payments.mercadopago.public_key') ?? '');

        $paymentsConfigured = $mpAccessToken !== '' && $mpPublicKey !== '';

        return view('admin.marketplace.index', compact('paidTotal', 'paidCount', 'pendingCount', 'paymentsConfigured'));
    }

    public function payments()
    {
        $mpAccessToken = (string) (config('payments.mercadopago.access_token') ?? '');
        $mpPublicKey = (string) (config('payments.mercadopago.public_key') ?? '');

        $paymentsConfigured = $mpAccessToken !== '' && $mpPublicKey !== '';
        $webhookUrl = url('/webhook/mercadopago');

        return view('admin.marketplace.payments', compact('paymentsConfigured', 'webhookUrl'));
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
