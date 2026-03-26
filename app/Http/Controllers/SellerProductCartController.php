<?php

namespace App\Http\Controllers;

use App\Models\SellerProduct;
use App\Services\Marketplace\SellerProductCartService;
use App\Services\Marketplace\SellerStoreService;
use Illuminate\Http\Request;

class SellerProductCartController extends Controller
{
    public function show(SellerProductCartService $cartService)
    {
        $totals = $cartService->totals();

        return view('storefront.cart', $totals);
    }

    public function store(Request $request, SellerProduct $product, SellerProductCartService $cartService, SellerStoreService $storeService)
    {
        $product->loadMissing('store.user');
        abort_unless($product->isPublished() && $storeService->isPubliclyAvailable($product->store), 404);

        $quantity = max(1, (int) $request->input('quantity', 1));
        $replace = $request->boolean('replace');
        $result = $cartService->add($product, $quantity, $replace);

        if ($result['status'] === 'conflict') {
            session()->flash('cart_replace_candidate', [
                'product_id' => $product->id,
                'title' => $product->title,
                'add_url' => route('seller-products.cart.add', $product),
            ]);

            return redirect()
                ->route('seller-products.cart.show')
                ->with('warning', 'Seu carrinho atual contem itens de outro vendedor. Confirme a substituicao para continuar.');
        }

        if ($result['status'] === 'unavailable') {
            return back()->with('error', 'Esse produto nao esta disponivel para compra direta na loja virtual.');
        }

        if ($request->boolean('buy_now')) {
            return redirect()->route('seller-products.checkout.show');
        }

        return redirect()->route('seller-products.cart.show')->with('success', 'Produto adicionado ao carrinho.');
    }

    public function update(Request $request, SellerProductCartService $cartService)
    {
        $cartService->updateQuantities((array) $request->input('quantities', []));

        return back()->with('success', 'Carrinho atualizado com sucesso.');
    }

    public function clear(SellerProductCartService $cartService)
    {
        $cartService->clear();

        return back()->with('success', 'Carrinho limpo com sucesso.');
    }
}
