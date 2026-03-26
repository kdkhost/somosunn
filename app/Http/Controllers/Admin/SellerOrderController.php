<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\SellerStore;
use Illuminate\Http\Request;

class SellerOrderController extends Controller
{
    public function index()
    {
        if (!SellerStore::tableAvailable()) {
            return redirect()
                ->route('admin.marketplace.index')
                ->with('error', 'O modulo da loja virtual ainda nao foi instalado. Rode php artisan migrate.');
        }

        $orders = Order::query()
            ->with(['user:id,name,email,phone', 'items', 'shipment'])
            ->where('seller_id', auth()->id())
            ->whereHas('items', function ($query) {
                $query->where('item_type', 'seller_product');
            })
            ->latest('id')
            ->paginate(20);

        return view('admin.marketplace.orders.index', compact('orders'));
    }

    public function updateShipment(Request $request, Order $order)
    {
        if (!SellerStore::tableAvailable()) {
            return redirect()
                ->route('admin.marketplace.index')
                ->with('error', 'O modulo da loja virtual ainda nao foi instalado. Rode php artisan migrate.');
        }

        abort_unless((int) $order->seller_id === (int) auth()->id(), 403);
        abort_unless($order->items()->where('item_type', 'seller_product')->exists(), 404);

        $data = $request->validate([
            'status' => ['required', 'in:pending,processing,shipped,delivered,cancelled'],
            'tracking_code' => ['nullable', 'string', 'max:120'],
        ]);

        if (!$order->shipment) {
            return back()->with('error', 'Este pedido nao possui entrega fisica vinculada.');
        }

        $shipment = $order->shipment;
        $shipment->status = $data['status'];
        $shipment->tracking_code = $data['tracking_code'] ?? null;

        if ($shipment->status === 'shipped' && !$shipment->shipped_at) {
            $shipment->shipped_at = now();
        }

        if ($shipment->status === 'delivered' && !$shipment->delivered_at) {
            $shipment->delivered_at = now();
        }

        $shipment->save();

        return back()->with('success', 'Status logistico atualizado com sucesso.');
    }
}
