<?php

namespace App\Http\Controllers\Panel\Admin;

use App\Http\Controllers\Controller;
use App\Models\SellerProduct;
use App\Models\SellerStore;
use App\Services\SalesAnalyticsService;
use Illuminate\Http\Request;

class SellerProductController extends Controller
{
    public function index(Request $request)
    {
        if (!SellerStore::tableAvailable() || !SellerProduct::tableAvailable()) {
            return redirect()
                ->route('panel.admin.dashboard')
                ->with('error', 'O modulo da loja virtual ainda nao foi instalado. Rode php artisan migrate.');
        }

        $search = trim((string) $request->query('q', ''));
        $status = trim((string) $request->query('status', ''));
        $type = trim((string) $request->query('type', ''));

        $stats = [
            'total' => SellerProduct::count(),
            'published' => SellerProduct::where('status', 'published')->count(),
            'draft' => SellerProduct::where('status', 'draft')->count(),
            'blocked' => SellerProduct::where('status', 'blocked')->count(),
        ];

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
            ->when($status !== '' && in_array($status, ['published', 'draft', 'blocked'], true), function ($query) use ($status) {
                $query->where('status', $status);
            })
            ->when($type !== '' && in_array($type, ['physical', 'digital', 'service'], true), function ($query) use ($type) {
                $query->where('type', $type);
            })
            ->latest('id')
            ->paginate(20)
            ->withQueryString();

        app(SalesAnalyticsService::class)->decorateSellerProducts($products);

        return view('panel.admin.marketplace.products.index', compact('products', 'search', 'status', 'type', 'stats'));
    }

    public function toggle(Request $request, SellerProduct $product)
    {
        if (!SellerStore::tableAvailable() || !SellerProduct::tableAvailable()) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => 'Modulo da loja virtual nao instalado.'], 500);
            }
            return redirect()
                ->route('panel.admin.dashboard')
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

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Status do produto atualizado com sucesso.',
                'product_id' => $product->id,
                'status' => $product->status,
                'status_label' => match ($product->status) {
                    'published' => 'Publicado',
                    'draft' => 'Rascunho',
                    'blocked' => 'Bloqueado',
                    default => ucfirst($product->status),
                },
                'published_at' => $product->published_at ? $product->published_at->format('d/m/Y H:i') : null,
            ]);
        }

        return back()->with('success', 'Status do produto atualizado com sucesso.');
    }
}
