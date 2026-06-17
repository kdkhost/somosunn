<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\SellerProduct;
use App\Models\SellerStore;
use App\Support\MarketplaceFee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MarketplaceController extends Controller
{
    public function index()
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

        $mpAccessToken = (string) (config('payments.mercadopago.access_token') ?? '');
        $mpPublicKey = (string) (config('payments.mercadopago.public_key') ?? '');

        $paymentsConfigured = $mpAccessToken !== '' && $mpPublicKey !== '';

        return view('admin.marketplace.index', compact('paidTotal', 'discountTotal', 'chargedTotal', 'platformFeeTotal', 'netTotal', 'paidCount', 'pendingCount', 'paymentsConfigured', 'platformFeePercent', 'storefrontModuleInstalled'));
    }

    public function payments()
    {
        $mpAccessToken = (string) (config('payments.mercadopago.access_token') ?? '');
        $mpPublicKey = (string) (config('payments.mercadopago.public_key') ?? '');

        $mpConfigured = $mpAccessToken !== '' && $mpPublicKey !== '';
        $mpEnabled = (int) (\App\Models\Setting::get('mercadopago_enabled', 1)) === 1;

        // SumUp
        $sumupEnabled = (int) (\App\Models\Setting::get('sumup_enabled', 0)) === 1;
        $sumupApiKey = (string) (\App\Models\Setting::get('sumup_api_key', '') ?? '');
        $sumupMerchantCode = (string) (\App\Models\Setting::get('sumup_merchant_code', '') ?? '');
        $sumupConfigured = $sumupApiKey !== '' && $sumupMerchantCode !== '';

        // Métodos ativos por gateway
        $mpMethods = [];
        if ((int) \App\Models\Setting::get('mercadopago_method_credit_card', 1) === 1) $mpMethods[] = 'Cartão de crédito';
        if ((int) \App\Models\Setting::get('mercadopago_method_debit_card', 0) === 1) $mpMethods[] = 'Cartão de débito';
        if ((int) \App\Models\Setting::get('mercadopago_method_pix', 1) === 1) $mpMethods[] = 'Pix';
        if ((int) \App\Models\Setting::get('mercadopago_method_ticket', 0) === 1) $mpMethods[] = 'Boleto';

        $sumupMethods = [];
        if ((int) \App\Models\Setting::get('sumup_method_card', 1) === 1) $sumupMethods[] = 'Cartão';
        if ((int) \App\Models\Setting::get('sumup_method_pix', 1) === 1) $sumupMethods[] = 'Pix';

        $mpWebhookUrl = route('api.webhooks.mercadopago');
        $sumupWebhookUrl = route('api.webhooks.sumup');

        // Compatibilidade
        $paymentsConfigured = $mpConfigured;
        $webhookUrl = $mpWebhookUrl;

        return view('admin.marketplace.payments', compact(
            'mpConfigured',
            'mpEnabled',
            'mpMethods',
            'mpWebhookUrl',
            'sumupConfigured',
            'sumupEnabled',
            'sumupMethods',
            'sumupWebhookUrl',
            // Legado
            'paymentsConfigured',
            'webhookUrl',
        ));
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

        return view('admin.marketplace.sales', compact('orders', 'paidTotal', 'discountTotal', 'chargedTotal', 'platformFeeTotal', 'netTotal', 'paidCount'));
    }

    public function stores(Request $request)
    {
        if (!SellerStore::tableAvailable()) {
            return redirect()
                ->route('admin.dashboard')
                ->with('error', 'O modulo da loja virtual ainda nao foi instalado. Rode php artisan migrate.');
        }

        $search = trim((string) $request->query('q', ''));

        $stores = SellerStore::query()
            ->with('user:id,name,email,plan_id,plan_expires_at')
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($nested) use ($search) {
                    $nested->where('brand_name', 'like', '%' . $search . '%')
                        ->orWhere('slug', 'like', '%' . $search . '%')
                        ->orWhereHas('user', function ($users) use ($search) {
                            $users->where('name', 'like', '%' . $search . '%')
                                ->orWhere('email', 'like', '%' . $search . '%');
                        });
                });
            })
            ->latest('id')
            ->paginate(20)
            ->withQueryString();

        return view('admin.marketplace.stores.index', compact('stores', 'search'));
    }

    public function toggleStore(Request $request, SellerStore $store)
    {
        if (!SellerStore::tableAvailable()) {
            return redirect()
                ->route('admin.dashboard')
                ->with('error', 'O modulo da loja virtual ainda nao foi instalado. Rode php artisan migrate.');
        }

        $request->validate([
            'is_blocked' => ['required', 'boolean'],
        ]);

        $store->is_blocked = $request->boolean('is_blocked');
        if ($store->is_blocked) {
            $store->is_published = false;
        }
        $store->save();

        return back()->with('success', 'Status da loja atualizado com sucesso.');
    }

    public function catalog(Request $request)
    {
        if (!SellerStore::tableAvailable() || !SellerProduct::tableAvailable()) {
            return redirect()
                ->route('admin.dashboard')
                ->with('error', 'O modulo da loja virtual ainda nao foi instalado. Rode php artisan migrate.');
        }

        $search = trim((string) $request->query('q', ''));

        $products = SellerProduct::query()
            ->with(['user:id,name,email', 'store:id,brand_name,slug'])
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($nested) use ($search) {
                    $nested->where('title', 'like', '%' . $search . '%')
                        ->orWhere('sku', 'like', '%' . $search . '%')
                        ->orWhereHas('store', function ($stores) use ($search) {
                            $stores->where('brand_name', 'like', '%' . $search . '%')
                                ->orWhere('slug', 'like', '%' . $search . '%');
                        });
                });
            })
            ->latest('id')
            ->paginate(20)
            ->withQueryString();

        return view('admin.marketplace.catalog.index', compact('products', 'search'));
    }

    public function toggleCatalogProduct(Request $request, SellerProduct $product)
    {
        if (!SellerStore::tableAvailable() || !SellerProduct::tableAvailable()) {
            return redirect()
                ->route('admin.dashboard')
                ->with('error', 'O modulo da loja virtual ainda nao foi instalado. Rode php artisan migrate.');
        }

        $request->validate([
            'status' => ['required', 'in:draft,published,blocked'],
        ]);

        $product->status = $request->input('status');
        if ($product->status === 'published' && !$product->published_at) {
            $product->published_at = now();
        }
        if ($product->status !== 'published') {
            $product->published_at = null;
        }
        $product->save();

        return back()->with('success', 'Status do produto atualizado com sucesso.');
    }
}
