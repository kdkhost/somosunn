<?php
// Atualiza produto 'basico' para ter os DOIS canais (venda e troca)
// Para que a nova página possa ser testada com ambos os botões

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\SellerProduct;
use App\Models\SellerStore;

$store = SellerStore::where('slug', 'kdkhost')->first();
if (!$store) { echo "Loja kdkhost nao encontrada\n"; exit(1); }

$product = SellerProduct::where('seller_store_id', $store->id)->where('slug', 'basico')->first();
if (!$product) { echo "Produto basico nao encontrado\n"; exit(1); }

echo "ANTES:\n";
echo "  sales_channel: {$product->sales_channel}\n";
echo "  price: {$product->price}\n";
echo "  sale_price: {$product->sale_price}\n";

// Atualizar canal para 'store_and_points' (ambos)
$product->update(['sales_channel' => 'store_and_points']);

$product->refresh();

echo "\nDEPOIS:\n";
echo "  sales_channel: {$product->sales_channel}\n";
echo "  supportsInternalCheckout: " . ($product->supportsInternalCheckout() ? 'SIM' : 'NAO') . "\n";
echo "  supportsPointsRedemption: " . ($product->supportsPointsRedemption() ? 'SIM' : 'NAO') . "\n";

echo "\nOK! Produto agora aceita dinheiro E pontos.\n";
