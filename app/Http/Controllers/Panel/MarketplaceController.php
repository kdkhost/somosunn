<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\SellerProduct;
use App\Models\SellerStore;
use App\Services\Payment\MercadoPagoService;
use App\Support\MarketplaceFee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MarketplaceController extends Controller
{
    public function index(MercadoPagoService $mpService)
    {
        $userId = (int) Auth::id();
        $storefrontModuleInstalled = SellerStore::tableAvailable() && SellerProduct::tableAvailable();

        $paidOrders = Order::with('items')->where('seller_id', $userId)->financialPaid()->get();
        $paidTotal = (float) $paidOrders->sum(fn (Order $order) => $order->gross_amount);
        $discountTotal = (float) $paidOrders->sum(fn (Order $order) => $order->financial_discount_amount);
        $chargedTotal = (float) $paidOrders->sum(fn (Order $order) => (float) $order->total_amount);
        $platformFeeTotal = (float) $paidOrders->sum(fn (Order $order) => (float) $order->platform_fee_amount);
        $netTotal = (float) max(0, $chargedTotal - $platformFeeTotal);
        $paidCount = (int) $paidOrders->count();
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
            'discountTotal',
            'chargedTotal',
            'platformFeeTotal',
            'netTotal',
            'paidCount',
            'pendingCount',
            'paymentsConfigured',
            'platformFeePercent',
            'balance',
            'storefrontModuleInstalled'
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

        return view('panel.marketplace.connect', compact('mercadopago'));
    }

    public function sales()
    {
        $userId = (int) Auth::id();

        $orders = Order::with(['user:id,name,email', 'items'])
            ->where('seller_id', $userId)
            ->latest('id')
            ->paginate(20);

        $paidOrders = Order::with('items')->where('seller_id', $userId)->financialPaid()->get();
        $paidTotal = (float) $paidOrders->sum(fn (Order $order) => $order->gross_amount);
        $discountTotal = (float) $paidOrders->sum(fn (Order $order) => $order->financial_discount_amount);
        $chargedTotal = (float) $paidOrders->sum(fn (Order $order) => (float) $order->total_amount);
        $platformFeeTotal = (float) $paidOrders->sum(fn (Order $order) => (float) $order->platform_fee_amount);
        $netTotal = (float) max(0, $chargedTotal - $platformFeeTotal);
        $paidCount = (int) $paidOrders->count();

        return view('panel.marketplace.sales', compact('orders', 'paidTotal', 'discountTotal', 'chargedTotal', 'platformFeeTotal', 'netTotal', 'paidCount'));
    }

    public function testCredentials(Request $request)
    {
        try {
            $service = new MercadoPagoService();
            $service->validateCredentials(auth()->id());

            $message = 'Conexao testada com sucesso! Credenciais validas.';

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => $message,
                ]);
            }

            return redirect()->back()->with('success', $message);
        } catch (\Exception $e) {
            $message = 'Falha na conexao: ' . $e->getMessage();

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $message,
                ], 422);
            }

            return redirect()->back()->with('error', $message);
        }
    }
}
