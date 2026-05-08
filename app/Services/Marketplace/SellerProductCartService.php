<?php

namespace App\Services\Marketplace;

use App\Models\SellerProduct;
use App\Models\Setting;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Session;

class SellerProductCartService
{
    private const SESSION_KEY = 'seller_product_cart_v1';
    private const TABLE_NAME = 'seller_product_cart_items';

    /**
     * Verifica se a tabela persistente está disponível.
     */
    private function hasPersistentStorage(): bool
    {
        try {
            return Schema::hasTable(self::TABLE_NAME);
        } catch (\Throwable) {
            return false;
        }
    }

    /**
     * Retorna a janela de expiração configurada (horas). Padrão 24h.
     */
    private function expirationHours(): int
    {
        $hours = (int) (Setting::get('cart_expiration_hours', 24) ?? 24);
        return max(1, $hours);
    }

    /**
     * Identificador do carrinho atual: user_id se logado, session_id caso contrário.
     */
    private function cartOwner(): array
    {
        $userId = Auth::id();
        if ($userId) {
            return ['user_id' => (int) $userId, 'session_id' => null];
        }

        return ['user_id' => null, 'session_id' => Session::getId()];
    }

    /**
     * Retorna o carrinho no formato legacy (usado por outros métodos).
     */
    public function getCart(): array
    {
        // Se tiver tabela persistente, ler dela
        if ($this->hasPersistentStorage()) {
            $this->syncSessionToDatabase();
            $this->purgeExpired();

            $owner = $this->cartOwner();
            $query = DB::table(self::TABLE_NAME);
            if ($owner['user_id']) {
                $query->where('user_id', $owner['user_id']);
            } else {
                $query->where('session_id', $owner['session_id']);
            }

            $rows = $query->get();
            if ($rows->isEmpty()) {
                return [];
            }

            $first = $rows->first();
            $items = [];
            foreach ($rows as $row) {
                $items[(int) $row->product_id] = [
                    'product_id' => (int) $row->product_id,
                    'quantity' => (int) $row->quantity,
                ];
            }

            return [
                'seller_id' => (int) $first->seller_id,
                'store_id' => (int) $first->store_id,
                'items' => $items,
            ];
        }

        // Fallback para session
        $cart = Session::get(self::SESSION_KEY, []);
        return is_array($cart) ? $cart : [];
    }

    /**
     * Migra carrinho de session para banco quando usuário loga.
     */
    private function syncSessionToDatabase(): void
    {
        if (!$this->hasPersistentStorage() || !Auth::check()) {
            return;
        }

        $sessionCart = Session::get(self::SESSION_KEY, []);
        if (!is_array($sessionCart) || empty($sessionCart['items'] ?? null)) {
            return;
        }

        $userId = (int) Auth::id();
        $sellerId = (int) ($sessionCart['seller_id'] ?? 0);
        $storeId = (int) ($sessionCart['store_id'] ?? 0);

        $expiresAt = Carbon::now()->addHours($this->expirationHours());

        foreach ($sessionCart['items'] as $item) {
            $productId = (int) ($item['product_id'] ?? 0);
            $quantity = max(1, (int) ($item['quantity'] ?? 1));
            if (!$productId) continue;

            $existing = DB::table(self::TABLE_NAME)
                ->where('user_id', $userId)
                ->where('product_id', $productId)
                ->first();

            if ($existing) {
                DB::table(self::TABLE_NAME)->where('id', $existing->id)->update([
                    'quantity' => $quantity,
                    'expires_at' => $expiresAt,
                    'updated_at' => now(),
                ]);
            } else {
                DB::table(self::TABLE_NAME)->insert([
                    'user_id' => $userId,
                    'session_id' => null,
                    'product_id' => $productId,
                    'seller_id' => $sellerId,
                    'store_id' => $storeId,
                    'quantity' => $quantity,
                    'expires_at' => $expiresAt,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        // Limpar session depois de migrar
        Session::forget(self::SESSION_KEY);
    }

    /**
     * Remove itens expirados do carrinho.
     */
    public function purgeExpired(): int
    {
        if (!$this->hasPersistentStorage()) {
            return 0;
        }

        return DB::table(self::TABLE_NAME)
            ->where('expires_at', '<', now())
            ->delete();
    }

    public function clear(): void
    {
        Session::forget(self::SESSION_KEY);

        if ($this->hasPersistentStorage()) {
            $owner = $this->cartOwner();
            $query = DB::table(self::TABLE_NAME);
            if ($owner['user_id']) {
                $query->where('user_id', $owner['user_id'])->delete();
            } elseif ($owner['session_id']) {
                $query->where('session_id', $owner['session_id'])->delete();
            }
        }
    }

    public function add(SellerProduct $product, int $quantity = 1, bool $replace = false): array
    {
        $quantity = max(1, $quantity);

        if (!$product->supportsInternalCheckout()) {
            return [
                'status' => 'unavailable',
                'cart' => $this->getCart(),
            ];
        }

        $cart = $this->getCart();
        $currentSellerId = (int) ($cart['seller_id'] ?? 0);
        $sellerId = (int) $product->user_id;

        if ($currentSellerId > 0 && $currentSellerId !== $sellerId && !$replace) {
            return [
                'status' => 'conflict',
                'cart' => $cart,
            ];
        }

        // Se mudou de vendedor e é replace, limpar
        if ($currentSellerId !== $sellerId && $replace) {
            $this->clear();
            $cart = [
                'seller_id' => $sellerId,
                'store_id' => (int) $product->seller_store_id,
                'items' => [],
            ];
        } elseif ($currentSellerId !== $sellerId) {
            // Primeiro item
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

        $finalQuantity = min($maxQuantity, $existingQuantity + $quantity);

        $items[$product->id] = [
            'product_id' => (int) $product->id,
            'quantity' => $finalQuantity,
        ];

        $cart['items'] = $items;
        unset($cart['shipping']);

        // Persistir: banco (se logado/tabela existe) OU session
        if ($this->hasPersistentStorage()) {
            $owner = $this->cartOwner();
            $expiresAt = Carbon::now()->addHours($this->expirationHours());

            $query = DB::table(self::TABLE_NAME);
            if ($owner['user_id']) {
                $existing = $query->where('user_id', $owner['user_id'])
                    ->where('product_id', $product->id)->first();
            } else {
                $existing = $query->where('session_id', $owner['session_id'])
                    ->where('product_id', $product->id)->first();
            }

            if ($existing) {
                DB::table(self::TABLE_NAME)->where('id', $existing->id)->update([
                    'quantity' => $finalQuantity,
                    'seller_id' => $sellerId,
                    'store_id' => (int) $product->seller_store_id,
                    'expires_at' => $expiresAt,
                    'updated_at' => now(),
                ]);
            } else {
                DB::table(self::TABLE_NAME)->insert([
                    'user_id' => $owner['user_id'],
                    'session_id' => $owner['session_id'],
                    'product_id' => (int) $product->id,
                    'seller_id' => $sellerId,
                    'store_id' => (int) $product->seller_store_id,
                    'quantity' => $finalQuantity,
                    'expires_at' => $expiresAt,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        } else {
            Session::put(self::SESSION_KEY, $cart);
        }

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

        // Persistir
        if ($this->hasPersistentStorage()) {
            $owner = $this->cartOwner();
            $expiresAt = Carbon::now()->addHours($this->expirationHours());

            foreach ($nextItems as $productId => $item) {
                $query = DB::table(self::TABLE_NAME);
                if ($owner['user_id']) {
                    $query->where('user_id', $owner['user_id'])->where('product_id', $productId);
                } else {
                    $query->where('session_id', $owner['session_id'])->where('product_id', $productId);
                }
                $query->update([
                    'quantity' => $item['quantity'],
                    'expires_at' => $expiresAt,
                    'updated_at' => now(),
                ]);
            }

            // Remover items que não estão em $nextItems
            $keepIds = array_keys($nextItems);
            $removeQuery = DB::table(self::TABLE_NAME);
            if ($owner['user_id']) {
                $removeQuery->where('user_id', $owner['user_id']);
            } else {
                $removeQuery->where('session_id', $owner['session_id']);
            }
            $removeQuery->whereNotIn('product_id', $keepIds)->delete();
        } else {
            $cart['items'] = $nextItems;
            unset($cart['shipping']);
            Session::put(self::SESSION_KEY, $cart);
        }

        return array_merge($cart, ['items' => $nextItems]);
    }

    public function items(): Collection
    {
        if (!SellerProduct::tableAvailable()) {
            $this->clear();
            return collect();
        }

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
                if (!$product || !$product->isPublished() || !$product->supportsInternalCheckout()) {
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
