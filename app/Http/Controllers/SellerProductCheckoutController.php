<?php

namespace App\Http\Controllers;

use App\Models\GatewayAccount;
use App\Models\Order;
use App\Models\OrderShipment;
use App\Models\SellerStore;
use App\Services\Marketplace\CorreiosShippingService;
use App\Services\Marketplace\SellerProductCartService;
use App\Services\OrderSettlementService;
use App\Services\Payment\MercadoPagoService;
use App\Support\MarketplaceFee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SellerProductCheckoutController extends Controller
{
    use \App\Traits\SumUpIntegration;
    public function show(Request $request, SellerProductCartService $cartService, CorreiosShippingService $shippingService)
    {
        if (!Auth::check()) {
            return redirect()->guest(route('login'))->with('error', 'Faca login para finalizar a compra.');
        }

        $totals = $cartService->totals();
        if ($totals['items']->isEmpty()) {
            return redirect()->route('seller-products.cart.show')->with('error', 'Seu carrinho esta vazio.');
        }

        $sellerStore = SellerStore::query()->with('user')->findOrFail((int) $totals['items']->first()['product']->seller_store_id);
        abort_unless(app(\App\Services\Marketplace\SellerStoreService::class)->isPubliclyAvailable($sellerStore), 404);

        $shippingAddress = [
            'recipient_name' => old('recipient_name', Auth::user()->name),
            'recipient_email' => old('recipient_email', Auth::user()->email),
            'recipient_phone' => old('recipient_phone', Auth::user()->phone),
            'postal_code' => old('postal_code', Auth::user()->cep),
            'address_line' => old('address_line', Auth::user()->address),
            'number' => old('number', Auth::user()->number),
            'complement' => old('complement', Auth::user()->complement),
            'neighborhood' => old('neighborhood', Auth::user()->neighborhood),
            'city' => old('city', Auth::user()->city),
            'state' => old('state', Auth::user()->state),
        ];

        $quotes = [];
        $shippingError = null;
        if ($totals['has_physical'] && filled($shippingAddress['postal_code'])) {
            try {
                $quotes = $shippingService->quote($sellerStore, $totals['items'], $shippingAddress);
            } catch (\Throwable $e) {
                $shippingError = $e->getMessage();
            }
        }

        $gateways = GatewayAccount::resolveForSeller((int) $sellerStore->user_id);

        // Verificar se SumUp está disponível para marketplace
        $subtotal = round((float) $totals['subtotal'], 2);
        $sumupAvailable = $this->shouldShowSumUp($subtotal, 'marketplace', $this->getUserType());

        return view('storefront.checkout', [
            'sellerStore' => $sellerStore,
            'totals' => $totals,
            'quotes' => $quotes,
            'shippingError' => $shippingError,
            'shippingAddress' => $shippingAddress,
            'mpEnabled' => (bool) ($gateways['mpEnabled'] ?? false),
            'sumupAvailable' => (bool) $sumupAvailable,
        ]);
    }

    public function process(
        Request $request,
        SellerProductCartService $cartService,
        CorreiosShippingService $shippingService,
        MercadoPagoService $mpService,
        OrderSettlementService $orderSettlementService
    ) {
        if (!Auth::check()) {
            return redirect()->guest(route('login'))->with('error', 'Faca login para finalizar a compra.');
        }

        $totals = $cartService->totals();
        if ($totals['items']->isEmpty()) {
            return redirect()->route('seller-products.cart.show')->with('error', 'Seu carrinho esta vazio.');
        }

        $sellerStore = SellerStore::query()->with('user')->findOrFail((int) $totals['items']->first()['product']->seller_store_id);
        abort_unless(app(\App\Services\Marketplace\SellerStoreService::class)->isPubliclyAvailable($sellerStore), 404);

        $data = $request->validate([
            'gateway_provider' => ['nullable', 'in:mercadopago,sumup'],
            'recipient_name' => ['nullable', 'string', 'max:120'],
            'recipient_email' => ['nullable', 'email', 'max:120'],
            'recipient_phone' => ['nullable', 'string', 'max:40'],
            'postal_code' => ['nullable', 'string', 'max:20'],
            'address_line' => ['nullable', 'string', 'max:200'],
            'number' => ['nullable', 'string', 'max:40'],
            'complement' => ['nullable', 'string', 'max:120'],
            'neighborhood' => ['nullable', 'string', 'max:120'],
            'city' => ['nullable', 'string', 'max:120'],
            'state' => ['nullable', 'string', 'max:10'],
            'shipping_service_code' => ['nullable', 'string', 'max:40'],
        ]);

        $shippingQuote = null;
        if ($totals['has_physical']) {
            foreach (['recipient_name', 'recipient_email', 'postal_code', 'address_line', 'city', 'state'] as $field) {
                if (blank($data[$field] ?? null)) {
                    return back()->withErrors([$field => 'Preencha o endereco de entrega para itens fisicos.'])->withInput();
                }
            }

            $quotes = $shippingService->quote($sellerStore, $totals['items'], $data);
            $shippingQuote = collect($quotes)->firstWhere('service_code', (string) ($data['shipping_service_code'] ?? ''));
            if (!$shippingQuote) {
                return back()->withErrors(['shipping_service_code' => 'Selecione uma opcao valida de frete dos Correios.'])->withInput();
            }
        }

        $subtotal = round((float) $totals['subtotal'], 2);
        $shippingAmount = round((float) data_get($shippingQuote, 'amount', 0), 2);
        $total = round($subtotal + $shippingAmount, 2);

        $gateways = GatewayAccount::resolveForSeller((int) $sellerStore->user_id);
        $chosenGateway = $data['gateway_provider'] ?? 'mercadopago';

        // Se escolheu SumUp, validar disponibilidade
        if ($chosenGateway === 'sumup') {
            if (!$this->shouldShowSumUp($total, 'marketplace', $this->getUserType())) {
                return back()->with('error', 'SumUp não disponível para este pedido.')->withInput();
            }
        } elseif ($total > 0 && !($gateways['mpEnabled'] ?? false)) {
            // Se MP não disponível, tentar fallback para SumUp
            if ($this->shouldShowSumUp($total, 'marketplace', $this->getUserType())) {
                $chosenGateway = 'sumup';
            } else {
                return back()->with('error', 'Mercado Pago nao configurado pelo vendedor.')->withInput();
            }
        }

        $order = null;
        DB::transaction(function () use (&$order, $totals, $subtotal, $shippingAmount, $total, $shippingQuote, $data, $sellerStore, $chosenGateway) {
            $platformFeeAmount = MarketplaceFee::amount($subtotal);
            $platformFeePercent = MarketplaceFee::percent();

            foreach ($totals['items'] as $row) {
                $product = $row['product']->fresh(['store.user']);
                if (!$product || !$product->isPublished() || !$product->store || !app(\App\Services\Marketplace\SellerStoreService::class)->isPubliclyAvailable($product->store)) {
                    abort(422, 'Um dos produtos nao esta mais disponivel para compra.');
                }

                if ($product->isPhysical()) {
                    $availableStock = max(0, (int) ($product->stock ?? 0));
                    if ($availableStock < (int) $row['quantity']) {
                        abort(422, 'Estoque insuficiente para concluir o pedido.');
                    }
                }
            }

            $order = Order::query()->create([
                'user_id' => auth()->id(),
                'seller_id' => $sellerStore->user_id,
                'status' => 'pending',
                'total_amount' => $total,
                'fee_amount' => 0,
                'platform_fee_amount' => $platformFeeAmount,
                'currency' => 'BRL',
                'gateway' => $total <= 0 ? 'free' : $chosenGateway,
                'metadata' => [
                    'context' => 'marketplace',
                    'sale_type' => 'seller_product',
                    'public_token' => Str::random(40),
                    'platform_fee_base_amount' => $subtotal,
                    'platform_fee_percent' => $platformFeePercent,
                    'shipping_amount' => $shippingAmount,
                    'store' => [
                        'id' => $sellerStore->id,
                        'slug' => $sellerStore->slug,
                        'brand_name' => $sellerStore->brand_name,
                    ],
                ],
            ]);

            foreach ($totals['items'] as $row) {
                $product = $row['product'];
                $deliverySnapshot = null;
                if ($product->isDigital()) {
                    $deliverySnapshot = [
                        'type' => $product->digital_delivery_type,
                        'url' => $product->digital_url,
                        'file_path' => $product->digital_file_path,
                        'file_name' => $product->digital_file_name,
                        'instructions' => $product->digital_instructions,
                    ];
                }

                $order->items()->create([
                    'item_type' => 'seller_product',
                    'item_id' => $product->id,
                    'title' => $product->title,
                    'price' => $row['unit_price'],
                    'quantity' => $row['quantity'],
                    'data' => [
                        'type' => $product->type,
                        'sales_channel' => $product->sales_channel,
                        'sku' => $product->sku,
                        'store_id' => $product->seller_store_id,
                        'store_slug' => $sellerStore->slug,
                        'product_slug' => $product->slug,
                        'cover_url' => $product->cover_url,
                        'digital_delivery' => $deliverySnapshot,
                        'dimensions' => [
                            'weight_grams' => $product->weight_grams,
                            'height_cm' => $product->height_cm,
                            'width_cm' => $product->width_cm,
                            'length_cm' => $product->length_cm,
                        ],
                    ],
                ]);
            }

            if ($shippingQuote) {
                $order->items()->create([
                    'item_type' => 'shipping',
                    'item_id' => 0,
                    'title' => 'Frete Correios - ' . (string) $shippingQuote['service_name'],
                    'price' => $shippingAmount,
                    'quantity' => 1,
                    'data' => [
                        'service_code' => $shippingQuote['service_code'],
                        'service_name' => $shippingQuote['service_name'],
                        'delivery_days' => $shippingQuote['delivery_days'],
                    ],
                ]);

                OrderShipment::query()->create([
                    'order_id' => $order->id,
                    'status' => 'pending',
                    'service_code' => $shippingQuote['service_code'],
                    'service_name' => $shippingQuote['service_name'],
                    'shipping_amount' => $shippingAmount,
                    'delivery_days' => $shippingQuote['delivery_days'],
                    'recipient_name' => $data['recipient_name'] ?? null,
                    'recipient_email' => $data['recipient_email'] ?? null,
                    'recipient_phone' => $data['recipient_phone'] ?? null,
                    'postal_code' => $data['postal_code'] ?? '',
                    'address_line' => $data['address_line'] ?? null,
                    'number' => $data['number'] ?? null,
                    'complement' => $data['complement'] ?? null,
                    'neighborhood' => $data['neighborhood'] ?? null,
                    'city' => $data['city'] ?? null,
                    'state' => $data['state'] ?? null,
                    'quote_payload' => $shippingQuote['payload'] ?? [],
                ]);
            }
        });

        // NÃO limpar carrinho aqui - só quando pagamento for aprovado
        // Se o usuário voltar atrás, o carrinho continua com os itens
        // O carrinho expira automaticamente após 24h

        if ($total <= 0) {
            $orderSettlementService->settleAsPaid($order, [
                'transaction_id' => 'FREE-SELLER-PRODUCT-' . $order->id . '-' . now()->format('YmdHis'),
                'payment_method' => 'free_checkout',
                'queue_invoice_email' => false,
                'send_notifications' => false,
                'gateway_data' => [
                    'source' => 'free_seller_product_checkout',
                    'automatic' => true,
                ],
            ]);

            // Pedido free: limpar carrinho imediatamente
            $cartService->clear();

            return redirect()->route('checkout.success', $order);
        }

        // Processar via SumUp se escolhido
        if ($chosenGateway === 'sumup') {
            return $this->processSumUpCheckout($order, $sellerStore);
        }

        $preference = $mpService->createPreference($order, [
            'statement_descriptor' => 'UNN LOJA',
        ]);

        $order->update([
            'metadata' => array_merge($order->metadata ?? [], [
                'mercadopago_preference_id' => $preference['id'] ?? null,
                'mercadopago_init_point' => $preference['init_point'] ?? null,
                'mercadopago_sandbox_init_point' => $preference['sandbox_init_point'] ?? null,
            ]),
        ]);

        return view('checkout.transparent', [
            'order' => $order->fresh('items', 'user'),
            'preferenceId' => $preference['id'] ?? '',
            'publicKey' => $gateways['mpPublicKey'] ?: config('payments.mercadopago.public_key') ?: \App\Models\Setting::get('mp_public_key'),
        ]);
    }

    /**
     * Processa checkout do marketplace via SumUp
     */
    protected function processSumUpCheckout(Order $order, SellerStore $sellerStore)
    {
        try {
            $sumUpService = app(\App\Services\Payment\SumUpService::class);

            $apiKey = trim((string) (\App\Models\Setting::get('sumup_api_key')
                ?: config('payments.sumup.api_key', '')));

            if (empty($apiKey)) {
                return back()->with('error', 'SumUp não configurado. Falta API Key.');
            }

            $checkout = $sumUpService->createCheckout($order, [
                'description' => 'Pedido #' . $order->id . ' - ' . $sellerStore->brand_name,
                'return_url'  => route('checkout.success', $order->id),
            ]);

            $checkoutId = $checkout['checkout_id'] ?? $checkout['id'] ?? null;

            $order->update([
                'metadata' => array_merge($order->metadata ?? [], [
                    'sumup_checkout_id'  => $checkoutId,
                    'sumup_checkout_url' => $checkout['checkout_url'] ?? data_get($checkout, 'raw.checkout_url'),
                ]),
            ]);

            $merchantCode = trim((string) (\App\Models\Setting::get('sumup_merchant_code')
                ?: config('payments.sumup.merchant_code', '')));

            $methodCardRaw = \App\Models\Setting::get('sumup_method_card');
            $methodPixRaw  = \App\Models\Setting::get('sumup_method_pix');
            $methodCard = $methodCardRaw !== null ? (bool)(int)$methodCardRaw : true;
            $methodPix  = $methodPixRaw  !== null ? (bool)(int)$methodPixRaw  : true;

            $maxInstallments = max(1, min(12, (int) (\App\Models\Setting::get('sumup_max_installments', 12))));
            $noInterestUpTo  = max(1, min(12, (int) (\App\Models\Setting::get('sumup_installments_no_interest', 1))));
            $installmentTax  = max(0.0, (float) (\App\Models\Setting::get('sumup_installment_tax', 0)));
            $passFeeToClient = (bool)(int)(\App\Models\Setting::get('sumup_pass_fee', 0));

            return view('checkout.transparent', [
                'order'                       => $order->fresh('items', 'user'),
                'preferenceId'                => '',
                'publicKey'                   => '',
                'gateway'                     => 'sumup',
                'checkoutId'                  => $checkoutId ?? '',
                'sumupMerchantCode'           => $merchantCode,
                'sumupMethodCard'             => $methodCard,
                'sumupMethodPix'              => $methodPix,
                'sumupMaxInstallments'        => $maxInstallments,
                'sumupInstallmentsNoInterest' => $noInterestUpTo,
                'sumupInstallmentTax'         => $installmentTax,
                'sumupPassFeeToClient'        => $passFeeToClient,
            ]);
        } catch (\Throwable $e) {
            \Log::error('SumUp marketplace checkout failed: ' . $e->getMessage(), [
                'order_id' => $order->id,
                'trace' => $e->getTraceAsString(),
            ]);
            return back()->with('error', 'Erro ao processar pagamento via SumUp: ' . $e->getMessage());
        }
    }
}
