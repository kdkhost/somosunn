<?php

namespace App\Services\Marketplace;

use App\Models\Order;
use App\Models\SellerProduct;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SellerProductFulfillmentService
{
    public function fulfillPaidOrder(Order $order): void
    {
        $order->loadMissing('items');

        if (!$order->items->contains(fn($item) => (string) $item->item_type === 'seller_product')) {
            return;
        }

        $metadata = is_array($order->metadata) ? $order->metadata : [];
        if (data_get($metadata, 'seller_products.fulfilled_at')) {
            return;
        }

        $shortages = [];

        DB::transaction(function () use ($order, &$metadata, &$shortages) {
            foreach ($order->items->where('item_type', 'seller_product') as $item) {
                $product = SellerProduct::query()->lockForUpdate()->find($item->item_id);
                if (!$product || !$product->isPhysical() || $product->stock === null) {
                    continue;
                }

                $quantity = max(1, (int) ($item->quantity ?? 1));
                $available = max(0, (int) ($product->stock ?? 0));

                if ($available < $quantity) {
                    $shortages[] = [
                        'item_id' => (int) $item->id,
                        'product_id' => (int) $product->id,
                        'requested' => $quantity,
                        'available' => $available,
                    ];
                }

                $product->stock = max(0, $available - $quantity);
                $product->save();
            }

            $metadata['seller_products'] = is_array($metadata['seller_products'] ?? null) ? $metadata['seller_products'] : [];
            $metadata['seller_products']['fulfilled_at'] = now()->toIso8601String();
            if ($shortages !== []) {
                $metadata['seller_products']['stock_shortages'] = $shortages;
            }

            $order->forceFill(['metadata' => $metadata])->save();
        });

        if ($shortages !== []) {
            Log::warning('Seller product stock shortage detected after payment approval.', [
                'order_id' => $order->id,
                'shortages' => $shortages,
            ]);
        }
    }
}
