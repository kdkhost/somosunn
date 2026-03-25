<?php

namespace App\Services\Marketplace;

use App\Models\SellerProduct;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Session;

class SellerProductCartService
{
    private const SESSION_KEY = 'seller_product_cart_v1';

    public function getCart(): array
    {
        $cart = Session::get(self::SESSION_KEY, []);

        return is_array($cart) ? $cart : [];
    }

    public function clear(): void
    {
        Session::forget(self::SESSION_KEY);
    }

    public function add(SellerProduct $product, int $quantity = 1, bool $replace = false): array
    {
        $quantity = max(1, $quantity);
        $cart = $this->getCart();
        $currentSellerId = (int) ($cart['seller_id'] ?? 0);
        $sellerId = (int) $product->user_id;

        if ($currentSellerId > 0 && $currentSellerId !== $sellerId && !$replace) {
            return [
                'status' => 'conflict',
                'cart' => $cart,
            ];
        }

        if ($currentSellerId !== $sellerId) {
            $cart = [
                'seller_id' => $sellerId,
                'store_id' => (int) $product->seller_store_id,
                'items' => [],
            ];
        }

        $items = is_array($cart['items'] ?? null) ? $cart['items'] : [];
        $existing = $items[$product->id] ?? null;
        $existingQuantity = (int) ($existing['quantity'] ?? 0);
        $maxQuantity = $product->isDigital()
            ? 1
            : max(1, (int) ($product->stock ?? 999999));

        $items[$product->id] = [
            'product_id' => (int) $product->id,
            'quantity' => min($maxQuantity, $existingQuantity + $quantity),
        ];

        $cart['items'] = $items;
        unset($cart['shipping']);

        Session::put(self::SESSION_KEY, $cart);

        return [
            'status' => 'added',
            'cart' => $cart,
        ];
    }

    public function updateQuantities(array $quantities): array
    {
        $cart = $this->getCart();
        $items = is_array($cart['items'] ?? null) ? $cart['items'] : [];

        if ($items === []) {
            return [];
        }

        $products = SellerProduct::query()
            ->whereIn('id', array_keys($items))
            ->get()
            ->keyBy('id');

        $nextItems = [];
        foreach ($items as $productId => $item) {
            $product = $products->get((int) $productId);
            if (!$product) {
                continue;
            }

            $requested = max(0, (int) ($quantities[$productId] ?? $item['quantity'] ?? 1));
            if ($requested <= 0) {
                continue;
            }

            $maxQuantity = $product->isDigital()
                ? 1
                : max(1, (int) ($product->stock ?? 999999));

            $nextItems[$productId] = [
                'product_id' => (int) $productId,
                'quantity' => min($requested, $maxQuantity),
            ];
        }

        if ($nextItems === []) {
            $this->clear();

            return [];
        }

        $cart['items'] = $nextItems;
        unset($cart['shipping']);
        Session::put(self::SESSION_KEY, $cart);

        return $cart;
    }

    public function items(): Collection
    {
        $cart = $this->getCart();
        $items = is_array($cart['items'] ?? null) ? $cart['items'] : [];

        if ($items === []) {
            return collect();
        }

        $products = SellerProduct::query()
            ->with(['store.user', 'media'])
            ->whereIn('id', array_keys($items))
            ->get()
            ->keyBy('id');

        $resolvedItems = collect($items)
            ->map(function (array $item, $productId) use ($products) {
                $product = $products->get((int) $productId);
                if (!$product || !$product->isPublished()) {
                    return null;
                }

                if (!$product->store || !app(SellerStoreService::class)->isPubliclyAvailable($product->store)) {
                    return null;
                }

                $requestedQuantity = max(1, (int) ($item['quantity'] ?? 1));
                $maxQuantity = $product->isDigital()
                    ? 1
                    : max(1, (int) ($product->stock ?? 999999));
                $quantity = min($requestedQuantity, $maxQuantity);
                $unitPrice = (float) $product->effective_price;

                return [
                    'product' => $product,
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'subtotal' => round($unitPrice * $quantity, 2),
                ];
            })
            ->filter()
            ->values();

        $nextItems = [];
        foreach ($resolvedItems as $row) {
            $nextItems[(int) $row['product']->id] = [
                'product_id' => (int) $row['product']->id,
                'quantity' => (int) $row['quantity'],
            ];
        }

        if ($nextItems === []) {
            $this->clear();
        } elseif ($nextItems !== $items) {
            $cart['store_id'] = (int) $resolvedItems->first()['product']->seller_store_id;
            $cart['seller_id'] = (int) $resolvedItems->first()['product']->user_id;
            $cart['items'] = $nextItems;
            unset($cart['shipping']);
            Session::put(self::SESSION_KEY, $cart);
        }

        return $resolvedItems;
    }

    public function totals(): array
    {
        $items = $this->items();
        $subtotal = round((float) $items->sum('subtotal'), 2);
        $shippingAmount = round((float) data_get($this->getCart(), 'shipping.amount', 0), 2);

        return [
            'items' => $items,
            'subtotal' => $subtotal,
            'shipping_amount' => $shippingAmount,
            'total' => round($subtotal + $shippingAmount, 2),
            'has_physical' => $items->contains(fn(array $row) => $row['product']->isPhysical()),
        ];
    }
}
