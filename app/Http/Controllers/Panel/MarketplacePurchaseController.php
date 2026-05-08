<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Setting;
use App\Services\Payment\MercadoPagoService;
use App\Services\Payment\SumUpService;
use Carbon\Carbon;
use Illuminate\Http\Request;
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

        // Computar deadline de cada pedido pending para UI
        $orders->getCollection()->transform(function (Order $o) {
            $o->payment_deadline = $this->computeDeadline($o);
            $o->is_expired = $o->status === 'pending' && $o->payment_deadline && $o->payment_deadline->isPast();
            $o->can_retry = $o->status === 'pending' && !$o->is_expired;
            return $o;
        });

        return view('panel.purchases.index', compact('orders'));
    }

    /**
     * Retomar pagamento de um pedido pending.
     * Redireciona para o checkout do gateway com os dados do pedido.
     */
    public function retry(Order $order, Request $request)
    {
        abort_unless((int) $order->user_id === (int) auth()->id(), 403);

        if ($order->status !== 'pending') {
            return redirect()->route('panel.purchases.index')
                ->with('error', 'Este pedido não está aguardando pagamento.');
        }

        // Verificar expiração
        $deadline = $this->computeDeadline($order);
        if ($deadline && $deadline->isPast()) {
            // Expirou - cancelar
            $this->cancelExpiredOrder($order);
            return redirect()->route('panel.purchases.index')
                ->with('error', 'O prazo para pagamento deste pedido expirou. O pedido foi cancelado.');
        }

        try {
            if ($order->gateway === 'sumup') {
                return $this->retrySumUp($order);
            }

            // MercadoPago (default)
            return $this->retryMercadoPago($order);
        } catch (\Throwable $e) {
            \Log::error("Retry payment failed for Order #{$order->id}: " . $e->getMessage());
            return redirect()->route('panel.purchases.index')
                ->with('error', 'Não foi possível retomar o pagamento. Tente novamente ou entre em contato com o suporte.');
        }
    }

    private function retryMercadoPago(Order $order)
    {
        $mpService = app(MercadoPagoService::class);

        // Tentar recuperar init_point salvo
        $initPoint = data_get($order->metadata, 'mercadopago_init_point');

        if (!$initPoint) {
            // Gerar nova preferência
            $order->load('items', 'user');
            $preference = $mpService->createPreference($order, [
                'statement_descriptor' => 'UNN',
            ]);

            $initPoint = $preference['init_point'] ?? null;

            $order->update([
                'metadata' => array_merge($order->metadata ?? [], [
                    'mercadopago_preference_id' => $preference['id'] ?? null,
                    'mercadopago_init_point' => $initPoint,
                ]),
            ]);
        }

        if (!$initPoint) {
            return redirect()->route('panel.purchases.index')
                ->with('error', 'Não foi possível gerar o link de pagamento. Tente novamente.');
        }

        return redirect()->away($initPoint);
    }

    private function retrySumUp(Order $order)
    {
        // Recuperar URL de checkout salvo
        $checkoutUrl = data_get($order->metadata, 'sumup_checkout_url');

        if (!$checkoutUrl) {
            // Tentar recriar
            try {
                $sumUpService = app(SumUpService::class);
                $checkout = $sumUpService->createCheckout($order, [
                    'description' => 'Pedido #' . $order->id,
                    'return_url'  => route('checkout.success', $order->id),
                ]);

                $checkoutUrl = $checkout['checkout_url'] ?? null;

                $order->update([
                    'metadata' => array_merge($order->metadata ?? [], [
                        'sumup_checkout_id'  => $checkout['id'] ?? null,
                        'sumup_checkout_url' => $checkoutUrl,
                    ]),
                ]);
            } catch (\Throwable $e) {
                return redirect()->route('panel.purchases.index')
                    ->with('error', 'Erro ao retomar pagamento SumUp: ' . $e->getMessage());
            }
        }

        if (!$checkoutUrl) {
            return redirect()->route('panel.purchases.index')
                ->with('error', 'Não foi possível gerar o link de pagamento SumUp.');
        }

        return redirect()->away($checkoutUrl);
    }

    /**
     * Calcula a data-limite para pagamento deste pedido.
     */
    private function computeDeadline(Order $order): ?Carbon
    {
        if (!$order->created_at) {
            return null;
        }

        $paymentMethod = (string) ($order->payment_method ?? '');
        $gateway = (string) ($order->gateway ?? '');

        if (stripos($paymentMethod, 'pix') !== false) {
            $minutes = $gateway === 'sumup'
                ? (int) (Setting::get('sumup_pix_expiration_minutes') ?? 10)
                : (int) (Setting::get('mercadopago_pix_expiration_minutes') ?? Setting::get('pix_expiration_minutes') ?? 10);
            return $order->created_at->copy()->addMinutes(max(1, $minutes));
        }

        if (stripos($paymentMethod, 'card') !== false
            || stripos($paymentMethod, 'credit') !== false
            || stripos($paymentMethod, 'debit') !== false) {
            return $order->created_at->copy()->addHours(24);
        }

        if (stripos($paymentMethod, 'ticket') !== false || stripos($paymentMethod, 'boleto') !== false) {
            return $order->created_at->copy()->addDays(3);
        }

        // Default 48h
        return $order->created_at->copy()->addHours(48);
    }

    private function cancelExpiredOrder(Order $order): void
    {
        try {
            if ($order->gateway === 'mercadopago' && $order->transaction_id) {
                app(MercadoPagoService::class)->cancelPayment($order);
            } elseif ($order->gateway === 'sumup') {
                $checkoutId = data_get($order->metadata, 'sumup_checkout_id');
                if ($checkoutId) {
                    $service = app(SumUpService::class);
                    if (method_exists($service, 'cancelCheckout')) {
                        $service->cancelCheckout($checkoutId);
                    }
                }
            }
        } catch (\Throwable $e) {
            \Log::warning("Gateway cancel failed for expired Order #{$order->id}: " . $e->getMessage());
        }

        $order->update([
            'status' => 'cancelled',
            'cancelled_at' => now(),
            'metadata' => array_merge($order->metadata ?? [], [
                'cancelled_reason' => 'Auto-cancel: payment window expired on retry',
            ]),
        ]);
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
