<?php

namespace App\Http\Controllers\Panel\Admin;

use App\Http\Controllers\Controller;
use App\Models\SellerProduct;
use Illuminate\Http\Request;

class SellerProductController extends Controller
{
    public function index(Request $request)
    {
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

        return view('panel.admin.marketplace.products.index', compact('products', 'search'));
    }

    public function toggle(Request $request, SellerProduct $product)
    {
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
