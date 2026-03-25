<?php

namespace App\Http\Controllers;

use App\Models\SellerProduct;
use App\Models\SellerStore;
use App\Services\Marketplace\SellerStoreService;

class SellerStorefrontController extends Controller
{
    public function show(string $storeSlug, SellerStoreService $storeService)
    {
        $store = SellerStore::query()
            ->with('user')
            ->where('slug', $storeSlug)
            ->firstOrFail();

        abort_unless($storeService->isPubliclyAvailable($store), 404);

        $payload = $storeService->storefrontPayload($store);

        return view('storefront.show', array_merge([
            'store' => $store,
        ], $payload));
    }

    public function product(string $storeSlug, string $productSlug, SellerStoreService $storeService)
    {
        $store = SellerStore::query()
            ->with('user')
            ->where('slug', $storeSlug)
            ->firstOrFail();

        abort_unless($storeService->isPubliclyAvailable($store), 404);

        $product = SellerProduct::query()
            ->with(['store.user', 'media'])
            ->where('seller_store_id', $store->id)
            ->where('slug', $productSlug)
            ->firstOrFail();

        abort_unless($product->isPublished(), 404);

        return view('storefront.product', compact('store', 'product'));
    }
}
