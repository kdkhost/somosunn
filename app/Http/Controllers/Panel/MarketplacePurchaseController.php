<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Support\Facades\Storage;

class MarketplacePurchaseController extends Controller
{
    public function index()
    {
        $orders = Order::query()
            ->with(['seller:id,name,email', 'items', 'shipment'])
            ->where('user_id', auth()->id())
            ->whereIn('status', ['pending', 'paid', 'refunded', 'failed'])
            ->latest('id')
            ->paginate(20);

        return view('panel.purchases.index', compact('orders'));
    }

    public function downloadDigital(Order $order, OrderItem $item)
    {
        abort_unless((int) $order->user_id === (int) auth()->id(), 403);
        abort_unless((string) $order->status === 'paid', 403);
        abort_unless((int) $item->order_id === (int) $order->id, 404);
        abort_unless((string) $item->item_type === 'seller_product', 404);

        $delivery = data_get($item->data, 'digital_delivery', []);
        $type = (string) ($delivery['type'] ?? '');

        if ($type === 'url' && !blank($delivery['url'] ?? null)) {
            return redirect()->away((string) $delivery['url']);
        }

        if ($type === 'file' && !blank($delivery['file_path'] ?? null) && Storage::disk('local')->exists((string) $delivery['file_path'])) {
            $downloadName = (string) ($delivery['file_name'] ?? ('arquivo-digital-' . $item->id));

            return Storage::disk('local')->download((string) $delivery['file_path'], $downloadName);
        }

        abort(404);
    }
}
