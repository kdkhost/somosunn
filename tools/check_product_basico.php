<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\SellerProduct;
use App\Models\SellerStore;

$store = SellerStore::where('slug', 'kdkhost')->first();
if (!$store) { echo "Loja kdkhost nao encontrada\n"; exit; }

$product = SellerProduct::where('seller_store_id', $store->id)->where('slug', 'basico')->first();
if (!$product) { echo "Produto basico nao encontrado\n"; exit; }

echo "=== PRODUTO BÁSICO ===\n";
echo "ID: {$product->id}\n";
echo "Slug: {$product->slug}\n";
echo "Status: {$product->status}\n";
echo "Type: {$product->type}\n";
echo "Sales Channel: {$product->sales_channel}\n";
echo "Price: {$product->price}\n";
echo "Sale Price: {$product->sale_price}\n";
echo "Effective Price: {$product->effective_price}\n";
echo "\n";
echo "supportsInternalCheckout (VENDA): " . ($product->supportsInternalCheckout() ? 'SIM' : 'NAO') . "\n";
echo "supportsPointsRedemption (TROCA): " . ($product->supportsPointsRedemption() ? 'SIM' : 'NAO') . "\n";
echo "supportsExternalCheckout (EXTERNO): " . ($product->supportsExternalCheckout() ? 'SIM' : 'NAO') . "\n";
echo "\n";
echo "redeemableItem: " . ($product->redeemableItem ? 'SIM (id=' . $product->redeemableItem->id . ')' : 'NAO') . "\n";

echo "\n=== CANAIS DISPONIVEIS ===\n";
$channels = SellerProduct::SALES_CHANNELS;
foreach ($channels as $key => $label) {
    echo "$key => $label\n";
}
