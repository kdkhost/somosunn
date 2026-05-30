<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\CancellationRequest;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CancellationRequestController extends Controller
{
    public function create(Request $request, $order_id, $item_id = null)
    {
        $order = Order::findOrFail($order_id);

        if ($order->user_id !== Auth::id()) {
            return back()->with('error', 'Voce nao tem permissao para solicitar cancelamento deste pedido.');
        }

        if (!in_array($order->status, ['paid', 'pending'])) {
            return back()->with('error', 'Este pedido nao pode ser cancelado.');
        }

        $orderItem = $item_id ? OrderItem::findOrFail($item_id) : null;

        if ($orderItem && $orderItem->order_id !== $order->id) {
            return back()->with('error', 'Item nao pertence a este pedido.');
        }

        return view('panel.cancellation-requests.create', compact('order', 'orderItem'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'order_id' => 'required|exists:orders,id',
            'order_item_id' => 'nullable|exists:order_items,id',
            'reason' => 'required|string|max:1000',
        ]);

        $order = Order::findOrFail($request->order_id);

        if ($order->user_id !== Auth::id()) {
            return back()->with('error', 'Voce nao tem permissao para solicitar cancelamento deste pedido.');
        }

        CancellationRequest::create([
            'order_id' => $request->order_id,
            'order_item_id' => $request->order_item_id,
            'user_id' => Auth::id(),
            'reason' => $request->reason,
            'status' => 'pending',
        ]);

        return redirect()->route('panel.cancellation-requests.index')
            ->with('success', 'Solicitacao de cancelamento enviada com sucesso.');
    }

    public function index()
    {
        $requests = CancellationRequest::where('user_id', Auth::id())
            ->with(['order', 'orderItem'])
            ->latest()
            ->paginate(10);

        return view('panel.cancellation-requests.index', compact('requests'));
    }
}
