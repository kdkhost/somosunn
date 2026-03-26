<?php

namespace App\Services\Marketplace;

use App\Models\RedeemableItem;
use App\Models\SellerProduct;
use App\Services\PointsExchangeService;
use Illuminate\Support\Facades\Schema;

class SellerProductChannelSyncService
{
    public function __construct(private readonly PointsExchangeService $exchangeService)
    {
    }

    public function sync(SellerProduct $product): void
    {
        if (!Schema::hasTable('redeemable_items')) {
            return;
        }

        $product->loadMissing('store.user', 'redeemableItem');

        if (!$product->isPublished() || !$product->supportsPointsRedemption()) {
            $this->deactivateRedemptionItem($product);

            return;
        }

        $referenceValue = max(0.01, $product->pointsReferenceValue());
        $providerName = trim((string) ($product->store->brand_name ?: optional($product->store->user)->name ?: 'Vendedor'));

        $payload = [
            'name' => $product->title,
            'description' => $product->description,
            'image' => $product->cover_path,
            'points_cost' => $this->exchangeService->moneyToPoints($referenceValue),
            'stock' => $product->isPhysical()
                ? max(-1, (int) ($product->stock ?? 0))
                : max(-1, (int) ($product->stock ?? -1)),
            'is_active' => true,
            'provider_type' => 'seller',
            'provider_user_id' => $product->user_id,
            'provider_name' => $providerName,
            'item_type' => $product->isPhysical() ? 'physical' : 'digital',
            'fulfillment_instructions' => $product->digital_instructions,
            'reference_value' => $referenceValue,
            'delivery_lead_days' => $product->isPhysical() ? 7 : 1,
            'seller_product_id' => $product->id,
        ];

        $redeemableItem = $product->redeemableItem;

        if ($redeemableItem) {
            $redeemableItem->update($payload);

            return;
        }

        RedeemableItem::create($payload);
    }

    public function deactivateRedemptionItem(SellerProduct $product): void
    {
        if (!Schema::hasTable('redeemable_items')) {
            return;
        }

        $product->loadMissing('redeemableItem');

        if (!$product->redeemableItem) {
            return;
        }

        $product->redeemableItem->update([
            'is_active' => false,
            'seller_product_id' => $product->exists ? $product->id : null,
        ]);
    }

    public function detachBeforeProductDeletion(SellerProduct $product): void
    {
        if (!Schema::hasTable('redeemable_items')) {
            return;
        }

        $product->loadMissing('redeemableItem');

        if (!$product->redeemableItem) {
            return;
        }

        $product->redeemableItem->update([
            'is_active' => false,
            'seller_product_id' => null,
        ]);
    }
}
