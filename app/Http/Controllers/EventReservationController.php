<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\EventCoupon;
use App\Models\EventRegistration;
use App\Models\Order;
use App\Models\CouponRedemption;
use App\Models\Plan;
use App\Models\SumUpTransaction;
use App\Models\User;
use App\Services\CouponService;
use App\Services\EventCouponService;
use App\Services\OrderSettlementService;
use App\Support\MarketplaceFee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class EventReservationController extends Controller
{
    public function checkout(Event $event)
    {
        if (Auth::check() && !Auth::user()->hasVerifiedEmail()) {
            return redirect()->route('verification.notice')->with('error', 'Por favor, verifique seu e-mail antes de realizar compras.');
        }

        $this->abortIfDisabledOrUnpublished($event);

        if ($this->isEventClosed($event)) {
            return redirect()->route('events.show', $event)->with('error', 'Este evento já encerrou.');
        }

        $isPaid = (float) $event->effective_price > 0;
        $mpEnabled = false;
        $preferredGateway = null;
        $activeGateways = [];
        if ($isPaid) {
            $seller = $event->user ?: User::find($event->user_id);
            if (false && $seller && !$seller->canSellOnMarketplace()) {
                return redirect()
                    ->route('events.show', $event)
                    ->with('error', 'Este organizador não está habilitado para vender no marketplace.');
            }

            $activeGateways = \App\Models\GatewayAccount::resolveAllActiveGatewaysForSeller($seller ? (int) $seller->id : 0);

            if (empty($activeGateways)) {
                \Illuminate\Support\Facades\Log::warning('EventCheckout: organizador sem gateway configurado', [
                    'event_id' => $event->id,
                    'event_user_id' => $event->user_id,
                    'seller_id' => $seller ? $seller->id : null,
                    'seller_found' => $seller !== null,
                ]);
                return redirect()
                    ->route('events.show', $event)
                    ->with('error', 'Este evento não está disponível para compra: o organizador ainda não configurou um método de pagamento.');
            }

            // Manter compatibilidade com código existente
            $firstGateway = $activeGateways[0];
            $mpEnabled = $firstGateway['provider'] === 'mercadopago';
            $preferredGateway = $firstGateway['provider'];
        }

        $registration = null;
        if (Auth::check()) {
            $registration = EventRegistration::where('event_id', $event->id)
                ->where('user_id', Auth::id())
                ->first();
        }

        return view('events.checkout', compact('event', 'registration', 'mpEnabled', 'preferredGateway', 'activeGateways'));
    }

    public function reserve(Request $request, Event $event, CouponService $couponService, EventCouponService $eventCouponService, \App\Services\Payment\MercadoPagoService $mpService, \App\Services\Payment\SumUpService $sumUpService, OrderSettlementService $orderSettlementService)
    {
        $this->abortIfDisabledOrUnpublished($event);

        // Verificar suspensao de eventos (punicao por nao-entrega de resgate)
        $user = auth()->user();
        if ($user && !$user->isAdmin() && (int) ($user->events_suspension_remaining ?? 0) > 0) {
            $remaining = (int) $user->events_suspension_remaining;
            // Decrementar a suspensao (cada tentativa bloqueada consome 1)
            $user->decrement('events_suspension_remaining');
            $remaining--;
            $msg = "Voce esta suspenso de comprar ingressos por nao ter entregue um produto no prazo.";
            if ($remaining > 0) {
                $msg .= " Suspensao restante: {$remaining} evento(s).";
            } else {
                $msg .= " Esta foi sua ultima suspensao. Na proxima vez voce podera comprar normalmente.";
            }
            return redirect()->route('events.show', $event)->with('error', $msg);
        }

        if ($this->isEventClosed($event)) {
            return redirect()->route('events.show', $event)->with('error', 'Este evento já encerrou.');
        }

        if (($event->is_demo ?? false) === true) {
            return redirect()->route('events.show', $event)->with('error', 'Este é um evento de demonstração.');
        }

        $quantity = (int) $request->input('quantity', 1);
        $quantity = max(1, min(10, $quantity));

        $request->validate([
            'coupon_code' => 'nullable|string|max:40',
        ]);

        $isPaid = (float) $event->effective_price > 0;
        $regularUnitPrice = (float) $event->current_price;
        $currentPrice = (float) $event->effective_price;
        $couponCode = $eventCouponService->normalizeCode($request->input('coupon_code'));
        $originalTotalPreview = round($currentPrice * $quantity, 2);
        $potentialFreeEventCoupon = $isPaid
            ? $eventCouponService->findPotentialFreeCoupon($event, $couponCode, $originalTotalPreview)
            : null;
        $requiresPayment = $isPaid && !$potentialFreeEventCoupon;
        $seller = $event->user ?: User::find($event->user_id);
        $sellerId = $seller ? (int) $seller->id : (int) ($event->user_id ?? 0);
        
        // Detectar todos os gateways ativos para o vendedor
        $activeGateways = $requiresPayment
            ? \App\Models\GatewayAccount::resolveAllActiveGatewaysForSeller($sellerId)
            : [];
        $activeProviders = array_column($activeGateways, 'provider');

        // Determinar o gateway a usar
        $gatewayProvider = null;
        $gatewayConfig = null;
        $paymentsConfigured = false;

        if ($requiresPayment) {
            if (empty($activeGateways)) {
                return redirect()
                    ->route('events.show', $event)
                    ->with('error', 'Pagamento indisponível: o organizador ainda não configurou um método de pagamento.');
            }

            if (count($activeGateways) > 1) {
                // Múltiplos gateways: exigir campo gateway no request
                $selectedGateway = $request->input('gateway');
                if (!$selectedGateway) {
                    if ($request->ajax() || $request->expectsJson()) {
                        return response()->json([
                            'error' => 'O campo gateway é obrigatório quando há múltiplos gateways disponíveis.',
                        ], 422);
                    }

                    return back()->with('error', 'Selecione o método de pagamento.')->withInput();
                }

                if (!in_array($selectedGateway, $activeProviders, true)) {
                    \Log::warning('EventReservation: gateway invÃ¡lido informado pelo cliente', [
                        'order_id' => null,
                        'event_id' => $event->id,
                        'gateway_informado' => $selectedGateway,
                        'gateways_ativos' => $activeProviders,
                    ]);
                    if ($request->ajax() || $request->expectsJson()) {
                        return response()->json([
                            'error' => 'Gateway de pagamento inválido ou não disponível para este evento.',
                        ], 422);
                    }

                    return back()->with('error', 'Gateway de pagamento inválido ou não disponível para este evento.')->withInput();
                }

                $gatewayProvider = $selectedGateway;
            } else {
                // Apenas 1 gateway ativo: usar diretamente sem exigir o campo no request
                $gatewayProvider = $activeGateways[0]['provider'];

                // Se o cliente informou um gateway, validar que Ã© o ativo
                $selectedGateway = $request->input('gateway');
                if ($selectedGateway && !in_array($selectedGateway, $activeProviders, true)) {
                    \Log::warning('EventReservation: gateway invÃ¡lido informado pelo cliente', [
                        'order_id' => null,
                        'event_id' => $event->id,
                        'gateway_informado' => $selectedGateway,
                        'gateways_ativos' => $activeProviders,
                    ]);
                    if ($request->ajax() || $request->expectsJson()) {
                        return response()->json([
                            'error' => 'Gateway de pagamento inválido ou não disponível para este evento.',
                        ], 422);
                    }

                    return back()->with('error', 'Gateway de pagamento inválido ou não disponível para este evento.')->withInput();
                }
            }

            // Encontrar a config do gateway selecionado
            foreach ($activeGateways as $gw) {
                if ($gw['provider'] === $gatewayProvider) {
                    $gatewayConfig = $gw;
                    break;
                }
            }
            $paymentsConfigured = $gatewayConfig !== null;
        }

        // Manter compatibilidade com cÃ³digo existente
        $gateways = [
            'mpEnabled' => $gatewayProvider === 'mercadopago' && $paymentsConfigured,
            'preferredGateway' => $gatewayProvider,
            'mpPublicKey' => ($gatewayProvider === 'mercadopago' && $gatewayConfig) ? ($gatewayConfig['config']['mpPublicKey'] ?? '') : '',
        ];

        if ($requiresPayment) {
            if (false && $seller && !$seller->canSellOnMarketplace()) {
                return redirect()
                    ->route('events.show', $event)
                    ->with('error', 'Este organizador não está habilitado para vender no marketplace.');
            }

            if (!$paymentsConfigured) {
                return redirect()
                    ->route('events.show', $event)
                    ->with('error', 'Pagamento indisponível: o organizador ainda não configurou um método de pagamento.');
            }
        }

        $user = Auth::user();
        if (!$user) {
            $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|email|unique:users,email',
                'cpf' => $requiresPayment ? 'required|string' : 'nullable|string',
                'phone' => 'nullable|string|max:50',
                'password' => 'required|min:8|confirmed',
            ]);

            $user = User::create([
                'name' => $request->string('name')->toString(),
                'email' => $request->string('email')->toString(),
                'password' => Hash::make($request->string('password')->toString()),
                'doc' => $request->string('cpf')->toString(),
                'phone' => $request->string('phone')->toString(),
                'level' => 'iniciante',
            ]);

            // Vincula pacote inicial (cliente) para liberar o Painel do Membro imediatamente
            try {
                $defaultPlan = Plan::query()->where('slug', 'cliente')->first()
                    ?? Plan::query()->orderBy('price')->orderBy('id')->first();
                if ($defaultPlan) {
                    $user->plan_id = (int) $defaultPlan->id;
                    $user->plan_expires_at = null;
                    $user->save();
                }
            } catch (\Throwable $e) {
                // ignore (fallback: usuário escolhe plano no /premium)
            }

            Auth::login($user);
        }

        if ($event->capacity && !$event->hasCapacityFor($quantity)) {
            return back()->with('error', 'Evento lotado no momento.')->withInput();
        }

        $registration = null;
        $order = null;
        $alreadyRegistered = false;
        $completedWithoutGateway = false;
        $genericCouponCode = $requiresPayment ? $couponService->normalizeCode($request->input('coupon_code')) : '';
        $usesSingleRegistrationPerUser = $this->usesSingleEventRegistrationPerUser();

        try {
            DB::transaction(function () use ($event, $user, $sellerId, $quantity, $isPaid, $requiresPayment, $regularUnitPrice, $currentPrice, $couponCode, $genericCouponCode, $couponService, $eventCouponService, $orderSettlementService, &$registration, &$order, &$alreadyRegistered, &$completedWithoutGateway, $gatewayProvider, $usesSingleRegistrationPerUser) {
                $existingCountedRegistration = EventRegistration::where('event_id', $event->id)
                    ->where('user_id', $user->id)
                    ->whereIn('status', EventRegistration::COUNTED_STATUSES)
                    ->lockForUpdate()
                    ->first();

                if ($usesSingleRegistrationPerUser && $existingCountedRegistration) {
                    $registration = $existingCountedRegistration;
                    $order = $existingCountedRegistration->order_id
                        ? Order::whereKey($existingCountedRegistration->order_id)->lockForUpdate()->first()
                        : null;

                    if (!$isPaid && !$order) {
                        $legacyQuantity = max(1, (int) ($existingCountedRegistration->quantity ?: $quantity));
                        $order = $this->createFreeEventOrder(
                            $event,
                            (int) $user->id,
                            $sellerId,
                            $legacyQuantity,
                            $regularUnitPrice,
                            $currentPrice
                        );

                        $existingCountedRegistration->update([
                            'order_id' => $order->id,
                            'status' => EventRegistration::STATUS_PENDING,
                            'price' => 0,
                            'quantity' => $legacyQuantity,
                            'ticket_code' => $event->is_ticket_enabled
                                ? ($existingCountedRegistration->ticket_code ?: Str::uuid()->toString())
                                : null,
                        ]);

                        $registration = $existingCountedRegistration->fresh();
                        $orderSettlementService->settleAsPaid($order, [
                            'transaction_id' => 'FREE-EVENT-' . $order->id . '-' . now()->format('YmdHis'),
                            'payment_method' => 'free_checkout',
                            'queue_invoice_email' => false,
                            'send_notifications' => false,
                            'gateway_data' => [
                                'source' => 'free_event_checkout',
                                'automatic' => true,
                            ],
                        ]);
                        return;
                    }

                    $alreadyRegistered = true;
                    return;
                }
                // Permitir múltiplas reservas mesmo se já houver uma confirmada/paga.
                // A restrição única de (event_id, user_id) foi removida do banco.

                $freeEventCoupon = null;
                $freeCouponDiscountAmount = 0.0;
                if ($isPaid && !$requiresPayment) {
                    $freeCouponResult = $eventCouponService->validateFreeCouponLocked(
                        $event,
                        $couponCode,
                        round($currentPrice * $quantity, 2),
                        $quantity
                    );
                    $freeEventCoupon = $freeCouponResult['coupon'];
                    $freeCouponDiscountAmount = (float) $freeCouponResult['discount_amount'];
                }

                if (!$requiresPayment) {
                    $order = $this->createFreeEventOrder(
                        $event,
                        (int) $user->id,
                        $sellerId,
                        $quantity,
                        $regularUnitPrice,
                        $currentPrice
                    );

                    if ($freeEventCoupon) {
                        $this->attachEventCouponToFreeOrder($order, $freeEventCoupon, $freeCouponDiscountAmount, $quantity, $currentPrice);
                    }

                    if ($usesSingleRegistrationPerUser) {
                        $legacyRegistration = EventRegistration::where('event_id', $event->id)
                            ->where('user_id', $user->id)
                            ->lockForUpdate()
                            ->first();

                        if ($legacyRegistration) {
                            $legacyRegistration->update([
                                ...$this->eventRegistrationPayload([
                                    'order_id' => $order->id,
                                    'coupon_id' => $freeEventCoupon?->id,
                                    'status' => EventRegistration::STATUS_PENDING,
                                    'payment_status' => $freeEventCoupon ? EventRegistration::PAYMENT_FREE : EventRegistration::PAYMENT_PENDING,
                                    'price' => 0,
                                    'quantity' => $quantity,
                                    'ticket_code' => $event->is_ticket_enabled
                                        ? ($legacyRegistration->ticket_code ?: Str::uuid()->toString())
                                        : null,
                                ]),
                            ]);
                            $registration = $legacyRegistration->fresh();
                        } else {
                            $registration = EventRegistration::create($this->eventRegistrationPayload([
                                'event_id' => $event->id,
                                'user_id' => $user->id,
                                'order_id' => $order->id,
                                'coupon_id' => $freeEventCoupon?->id,
                                'status' => EventRegistration::STATUS_PENDING,
                                'payment_status' => $freeEventCoupon ? EventRegistration::PAYMENT_FREE : EventRegistration::PAYMENT_PENDING,
                                'price' => 0,
                                'quantity' => $quantity,
                                'ticket_code' => $event->is_ticket_enabled ? Str::uuid()->toString() : null,
                            ]));
                        }
                    } else {
                        EventRegistration::where('event_id', $event->id)
                            ->where('user_id', $user->id)
                            ->whereNotIn('status', EventRegistration::COUNTED_STATUSES)
                            ->delete();

                        for ($i = 0; $i < $quantity; $i++) {
                            $registration = EventRegistration::create($this->eventRegistrationPayload([
                                'event_id' => $event->id,
                                'user_id' => $user->id,
                                'order_id' => $order->id,
                                'coupon_id' => $freeEventCoupon?->id,
                                'status' => EventRegistration::STATUS_PENDING,
                                'payment_status' => $freeEventCoupon ? EventRegistration::PAYMENT_FREE : EventRegistration::PAYMENT_PENDING,
                                'price' => 0,
                                'quantity' => 1,
                                'ticket_code' => $event->is_ticket_enabled ? Str::uuid()->toString() : null,
                            ]));
                        }
                    }

                    if ($freeEventCoupon) {
                        $eventCouponService->consumeLocked($freeEventCoupon, $quantity);
                    }

                    // Notificar confirmação da vaga (Evento Gratuito)
                    $user->notify(new \App\Notifications\AppNotification([
                        'message' => 'Sua vaga no evento "' . $event->title . '" foi confirmada com sucesso!',
                        'type' => 'EventConfirmed',
                        'action_url' => route('events.show', $event),
                        'action_label' => 'Ver detalhes'
                    ]));

                    $orderSettlementService->settleAsPaid($order, [
                        'transaction_id' => 'FREE-EVENT-' . $order->id . '-' . now()->format('YmdHis'),
                        'payment_method' => 'free_checkout',
                        'queue_invoice_email' => false,
                        'send_notifications' => false,
                        'gateway_data' => [
                            'source' => 'free_event_checkout',
                            'automatic' => true,
                        ],
                    ]);

                    try {
                        (new \App\Services\PointsService())->award($user, 'attend_event', ['event_id' => $event->id]);
                    } catch (\Throwable $e) {
                        \Log::warning('Falha ao pontuar attend_event: ' . $e->getMessage());
                    }

                    return;
                }

                if ($event->capacity && !$event->hasCapacityFor($quantity)) {
                    throw new \RuntimeException('Evento lotado no momento.');
                }

                $existingPendingRegistration = EventRegistration::where('event_id', $event->id)
                    ->where('user_id', $user->id)
                    ->whereNotIn('status', EventRegistration::COUNTED_STATUSES)
                    ->lockForUpdate()
                    ->orderBy('id')
                    ->first();

                if ($existingPendingRegistration && $existingPendingRegistration->order_id) {
                    $existingOrder = Order::whereKey($existingPendingRegistration->order_id)->lockForUpdate()->first();
                    if ($existingOrder && $existingOrder->status !== 'paid') {
                        $order = $existingOrder;
                    }
                }

                $originalTotal = round($currentPrice * $quantity, 2);
                $discountAmount = 0.0;
                $coupon = null;

                if ($genericCouponCode !== '') {
                    $result = $couponService->validateAndCalculateLocked(
                        $genericCouponCode,
                        CouponService::CONTEXT_EVENT,
                        (int) $event->id,
                        (int) $user->id,
                        (float) $originalTotal,
                        $order ? (int) $order->id : null
                    );
                    $coupon = $result['coupon'];
                    $discountAmount = (float) $result['discount_amount'];
                }

                $finalTotal = max(0, round($originalTotal - $discountAmount, 2));
                $isFreeCouponCheckout = $coupon && $discountAmount > 0 && $finalTotal <= 0;
                $platformFeePercent = MarketplaceFee::deductionPercent($sellerId > 0 ? $sellerId : null);
                $platformFeeAmount = MarketplaceFee::deductionAmount($finalTotal, $sellerId > 0 ? $sellerId : null);

                if (!$order) {
                    $order = Order::create([
                        'user_id' => $user->id,
                        'seller_id' => $sellerId > 0 ? $sellerId : null,
                        'status' => 'pending',
                        'total_amount' => $finalTotal,
                        'fee_amount' => 0,
                        'platform_fee_amount' => $platformFeeAmount,
                        'currency' => 'BRL',
                        'gateway' => $isFreeCouponCheckout ? 'free' : $gatewayProvider,
                        'gateway_account_id' => null,
                        'metadata' => [
                            'context' => 'event',
                            'sale_type' => 'event',
                            'public_token' => Str::random(40),
                            'original_total_amount' => $originalTotal,
                            'regular_total_amount' => round($regularUnitPrice * $quantity, 2),
                            'platform_fee_percent' => $platformFeePercent,
                            'is_free_checkout' => $isFreeCouponCheckout,
                            'free_checkout_reason' => $isFreeCouponCheckout ? 'generic_coupon' : null,
                        ],
                    ]);
                } else {
                    $order->update([
                        'user_id' => $user->id,
                        'seller_id' => $sellerId > 0 ? $sellerId : null,
                        'status' => 'pending',
                        'total_amount' => $finalTotal,
                        'platform_fee_amount' => $platformFeeAmount,
                        'currency' => 'BRL',
                        'gateway' => $isFreeCouponCheckout ? 'free' : $gatewayProvider,
                        'metadata' => array_merge($order->metadata ?? [], [
                            'context' => 'event',
                            'sale_type' => 'event',
                            'original_total_amount' => $originalTotal,
                            'regular_total_amount' => round($regularUnitPrice * $quantity, 2),
                            'platform_fee_percent' => $platformFeePercent,
                            'is_free_checkout' => $isFreeCouponCheckout,
                            'free_checkout_reason' => $isFreeCouponCheckout ? 'generic_coupon' : null,
                        ]),
                    ]);

                    $order->items()->delete();
                    CouponRedemption::query()->where('order_id', $order->id)->where('status', 'reserved')->delete();
                }

                $itemData = [
                    'event_start_at' => optional($event->start_at)->toIso8601String(),
                    'event_end_at' => optional($event->end_at)->toIso8601String(),
                    'batch_label' => $event->current_batch_label,
                    'original_unit_price' => $currentPrice,
                    'regular_unit_price' => $regularUnitPrice,
                    'flash_sale_price' => $event->flash_sale_price !== null ? (float) $event->flash_sale_price : null,
                    'flash_sale_ends_at' => $event->flash_sale_ends_at ? $event->flash_sale_ends_at->toIso8601String() : null,
                    'discount_amount' => $discountAmount,
                ];

                if ($coupon && $discountAmount > 0) {
                    $itemData['coupon'] = [
                        'id' => (int) $coupon->id,
                        'code' => (string) $coupon->code,
                        'discount_type' => (string) $coupon->discount_type,
                        'discount_value' => (float) $coupon->discount_value,
                        'discount_amount' => round($discountAmount, 2),
                    ];
                }

                if ($discountAmount > 0 && $quantity > 1) {
                    foreach ($couponService->splitUnitPrices($finalTotal, $quantity) as $split) {
                        $order->items()->create([
                            'item_type' => 'event',
                            'item_id' => $event->id,
                            'title' => $event->title,
                            'price' => $split['unit_price'],
                            'quantity' => (int) $split['quantity'],
                            'data' => $itemData,
                        ]);
                    }
                } else {
                    $order->items()->create([
                        'item_type' => 'event',
                        'item_id' => $event->id,
                        'title' => $event->title,
                        'price' => $discountAmount > 0 ? $finalTotal : $currentPrice,
                        'quantity' => $quantity,
                        'data' => $itemData,
                    ]);
                }

                if ($coupon && $discountAmount > 0) {
                    $order->update([
                        'metadata' => array_merge($order->metadata ?? [], [
                            'coupon' => [
                                'id' => $coupon->id,
                                'code' => $coupon->code,
                                'discount_type' => $coupon->discount_type,
                                'discount_value' => (float) $coupon->discount_value,
                                'discount_amount' => $discountAmount,
                            ],
                        ]),
                    ]);

                    $couponService->reserveRedemption($coupon, (int) $user->id, (int) $order->id, $discountAmount);
                } else {
                    // Clear coupon metadata if any (e.g. reused order)
                    if (is_array($order->metadata) && array_key_exists('coupon', $order->metadata)) {
                        $meta = $order->metadata;
                        unset($meta['coupon']);
                        $order->update(['metadata' => $meta]);
                    }
                }

                $unitPrice = $discountAmount > 0 ? (float) ($finalTotal / $quantity) : (float) $currentPrice;
                $registrationPaymentStatus = $isFreeCouponCheckout
                    ? EventRegistration::PAYMENT_FREE
                    : EventRegistration::PAYMENT_PENDING;

                if ($usesSingleRegistrationPerUser) {
                    $legacyRegistration = $existingPendingRegistration
                        ?: EventRegistration::where('event_id', $event->id)
                            ->where('user_id', $user->id)
                            ->lockForUpdate()
                            ->first();

                    if ($legacyRegistration) {
                        $legacyRegistration->update($this->eventRegistrationPayload([
                            'order_id' => $order->id,
                            'coupon_id' => null,
                            'status' => EventRegistration::STATUS_PENDING,
                            'payment_status' => $registrationPaymentStatus,
                            'price' => $unitPrice,
                            'quantity' => $quantity,
                            'ticket_code' => $event->is_ticket_enabled
                                ? ($legacyRegistration->ticket_code ?: Str::uuid()->toString())
                                : null,
                        ]));
                        $registration = $legacyRegistration->fresh();
                    } else {
                        $registration = EventRegistration::create($this->eventRegistrationPayload([
                            'event_id' => $event->id,
                            'user_id' => $user->id,
                            'order_id' => $order->id,
                            'coupon_id' => null,
                            'status' => EventRegistration::STATUS_PENDING,
                            'payment_status' => $registrationPaymentStatus,
                            'price' => $unitPrice,
                            'quantity' => $quantity,
                            'ticket_code' => $event->is_ticket_enabled ? Str::uuid()->toString() : null,
                        ]));
                    }
                } else {
                    EventRegistration::where('event_id', $event->id)
                        ->where('user_id', $user->id)
                        ->whereNotIn('status', EventRegistration::COUNTED_STATUSES)
                        ->delete();

                    for ($i = 0; $i < $quantity; $i++) {
                        $registration = EventRegistration::create($this->eventRegistrationPayload([
                            'event_id' => $event->id,
                            'user_id' => $user->id,
                            'order_id' => $order->id,
                            'coupon_id' => null,
                            'status' => EventRegistration::STATUS_PENDING,
                            'payment_status' => $registrationPaymentStatus,
                            'price' => $unitPrice,
                            'quantity' => 1,
                            'ticket_code' => $event->is_ticket_enabled ? Str::uuid()->toString() : null,
                        ]));
                    }
                }

                if ($isFreeCouponCheckout) {
                    $user->notify(new \App\Notifications\AppNotification([
                        'message' => 'Sua vaga no evento "' . $event->title . '" foi confirmada com sucesso!',
                        'type' => 'EventConfirmed',
                        'action_url' => route('events.show', $event),
                        'action_label' => 'Ver detalhes',
                    ]));

                    $order = $orderSettlementService->settleAsPaid($order, [
                        'transaction_id' => 'FREE-COUPON-' . $order->id . '-' . now()->format('YmdHis'),
                        'payment_method' => 'free_coupon',
                        'queue_invoice_email' => false,
                        'send_notifications' => false,
                        'gateway_data' => [
                            'source' => 'generic_coupon_free_checkout',
                            'coupon_code' => $coupon->code,
                            'automatic' => true,
                        ],
                    ]);

                    try {
                        (new \App\Services\PointsService())->award($user, 'attend_event', ['event_id' => $event->id]);
                    } catch (\Throwable $e) {
                        \Log::warning('Falha ao pontuar attend_event: ' . $e->getMessage());
                    }

                    $registration = $registration?->fresh();
                    $completedWithoutGateway = true;
                    return;
                }
            });
        } catch (ValidationException $e) {
            $msg = collect($e->errors())->flatten()->first() ?? 'Não foi possível aplicar o cupom.';
            return back()->with('error', $msg)->withInput();
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage())->withInput();
        } catch (\Throwable $e) {
            \Log::error('Falha ao registrar reserva de evento', [
                'event_id' => $event->id,
                'user_id' => $user?->id,
                'message' => $e->getMessage(),
            ]);

            return back()->with('error', 'Nao foi possivel concluir a reserva agora. Tente novamente em instantes.')->withInput();
        }

        if ($alreadyRegistered) {
            return redirect()->route('events.show', $event)->with('success', 'Sua vaga já está confirmada.');
        }

        if ($completedWithoutGateway) {
            return redirect()->route('events.show', $event)->with('success', 'Vaga confirmada com sucesso!');
        }

        if ($registration && in_array($registration->status, EventRegistration::COUNTED_STATUSES, true)) {
            return redirect()->route('events.show', $event)->with('success', 'Sua vaga já está confirmada.');
        }

        if (!$requiresPayment) {
            // Gamificação: inscrição gratuita confirmada
            if ($registration && $user) {
                try {
                    (new \App\Services\PointsService())->award($user, 'attend_event', ['event_id' => $event->id]);
                } catch (\Throwable $e) {
                    \Log::warning('Falha ao pontuar attend_event: ' . $e->getMessage());
                }
            }

            return redirect()->route('events.show', $event)->with('success', 'Vaga confirmada com sucesso!');
        }

        // Roteamento para o gateway correto
        if ($gatewayProvider === 'sumup') {
            \Log::info('EventReservation: processando pagamento via SumUp', [
                'order_id' => $order->id,
                'event_id' => $event->id,
                'gateway' => 'sumup',
            ]);
            return $this->processSumUpPayment($order, $event, $gatewayConfig, $sumUpService);
        } elseif ($gatewayProvider === 'mercadopago') {
            \Log::info('EventReservation: processando pagamento via MercadoPago', [
                'order_id' => $order->id,
                'event_id' => $event->id,
                'gateway' => 'mercadopago',
            ]);
            return $this->processMercadoPagoPayment($order, $event, $gatewayConfig, $mpService);
        } else {
            \Log::error('Gateway desconhecido ou não configurado', [
                'order_id' => $order->id,
                'event_id' => $event->id,
                'gateway' => $gatewayProvider,
            ]);

            return back()->with('error', 'Método de pagamento não configurado. Entre em contato com o organizador.');
        }
    }

    /**
     * Exibe a página de seleção de gateway quando há múltiplos gateways ativos.
     * Renderiza checkout.transparent com Gateway Selector inline.
     */
    public function selectGateway(Order $order, Request $request)
    {
        $this->abortIfOrderNotAccessible($order, $request);

        // Verificar se o pedido ainda está pendente
        if ($order->status === 'paid') {
            return redirect()->route('events.payment.success', $order);
        }

        // Obter o evento do pedido
        $event = $this->getEventFromOrder($order);
        if (!$event) {
            return redirect()->route('panel.dashboard')->with('error', 'Pedido não encontrado.');
        }

        if ($redirect = $this->redirectZeroAmountOrderWithoutGateway($order, $event, 'event_select_gateway_zero_amount')) {
            return $redirect;
        }

        // Resolver gateways ativos para o vendedor
        $seller = $event->user ?: User::find($event->user_id);
        $sellerId = $seller ? (int) $seller->id : 0;
        $activeGateways = \App\Models\GatewayAccount::resolveAllActiveGatewaysForSeller($sellerId);

        // Se só tem 1 gateway, processar diretamente
        if (count($activeGateways) === 1) {
            return $this->processGateway($order, $request, $activeGateways[0]['provider']);
        }

        // Se não tem nenhum gateway, erro
        if (empty($activeGateways)) {
            return redirect()->route('events.show', $event)
                ->with('error', 'Nenhum gateway de pagamento disponível.');
        }

        // Preparar dados para o checkout.transparent com Gateway Selector
        $order->load('items', 'user');

        // Resolver public key do Mercado Pago
        $mpPublicKey = '';
        $preferenceId = '';
        $mpGatewayConfig = null;
        $sumupGatewayConfig = null;

        foreach ($activeGateways as $gw) {
            if ($gw['provider'] === 'mercadopago') {
                $mpGatewayConfig = $gw;
            } elseif ($gw['provider'] === 'sumup') {
                $sumupGatewayConfig = $gw;
            }
        }

        // Resolver MP public key
        if ($mpGatewayConfig) {
            if (!empty($mpGatewayConfig['config']['mpPublicKey'])) {
                $mpPublicKey = $mpGatewayConfig['config']['mpPublicKey'];
            }
            if (empty($mpPublicKey)) {
                $platformOwnerId = \App\Models\Setting::get('platform_owner_id', 2);
                $isPlatformOwner = $sellerId === (int) $platformOwnerId;
                if (!$isPlatformOwner) {
                    $sellerMpAccount = \App\Models\GatewayAccount::resolveForSeller($sellerId);
                    $mpPublicKey = $sellerMpAccount['mpPublicKey'] ?? '';
                }
            }
            if (empty($mpPublicKey)) {
                $mpEnv = (string) \App\Models\Setting::get('mercadopago_env', 'sandbox');
                $prefix = $mpEnv === 'production' ? 'mercadopago_prod_' : 'mercadopago_sandbox_';
                $mpPublicKey = \App\Models\Setting::get($prefix . 'public_key')
                    ?: config('payments.mercadopago.public_key')
                    ?: \App\Models\Setting::get('mp_public_key', '');
            }

            // Criar preference do MP
            try {
                $mpService = app(\App\Services\Payment\MercadoPagoService::class);
                $preference = $mpService->createPreference($order, [
                    'statement_descriptor' => 'UNN EVENTOS',
                    'back_urls' => [
                        'success' => route('events.payment.success', $order->id),
                        'failure' => route('events.payment.failure', $order->id),
                        'pending' => route('events.payment.pending', $order->id),
                    ],
                ]);
                $preferenceId = $preference['id'] ?? '';

                $order->update([
                    'metadata' => array_merge($order->metadata ?? [], [
                        'mercadopago_preference_id' => $preference['id'] ?? null,
                        'mercadopago_init_point' => $preference['init_point'] ?? null,
                    ]),
                ]);
            } catch (\Throwable $e) {
                \Log::warning('selectGateway: falha ao criar preference MP', [
                    'order_id' => $order->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        // Resolver dados do SumUp
        $sumupViewData = [];
        if ($sumupGatewayConfig) {
            try {
                $sumUpService = app(\App\Services\Payment\SumUpService::class);
                $apiKey = $sumupGatewayConfig['config']['apiKey'] ?? '';
                $merchantCode = $sumupGatewayConfig['config']['merchantCode'] ?? '';

                $checkout = $sumUpService->createCheckout($order, $merchantCode);

                $order->update([
                    'metadata' => array_merge($order->metadata ?? [], [
                        'sumup_checkout_id' => $checkout['id'] ?? null,
                    ]),
                ]);

                $methodCardRaw = \App\Models\Setting::get('sumup_method_card');
                $methodPixRaw  = \App\Models\Setting::get('sumup_method_pix');

                $sumupViewData = [
                    'sumupMethodCard'          => $methodCardRaw !== null ? (bool)(int)$methodCardRaw : true,
                    'sumupMethodPix'           => $methodPixRaw !== null ? (bool)(int)$methodPixRaw : true,
                    'sumupApiKey'              => $apiKey,
                    'sumupMaxInstallments'     => max(1, min(12, (int) (\App\Models\Setting::get('sumup_max_installments', 12)))),
                    'sumupNoInterestUpTo'      => max(1, min(12, (int) (\App\Models\Setting::get('sumup_installments_no_interest', 1)))),
                    'sumupInstallmentTax'      => max(0.0, (float) (\App\Models\Setting::get('sumup_installment_tax', 0))),
                    'sumupPassFeeToClient'     => (bool)(int)(\App\Models\Setting::get('sumup_pass_fee', 0)),
                    'sumupInterestType'        => \App\Models\Setting::get('sumup_interest_type', 'per_installment'),
                    'sumupPixExpirationMinutes' => (int) (\App\Models\Setting::get('sumup_pix_expiration_minutes', 10) ?: 10),
                    'checkoutId'               => $checkout['checkout_id'] ?? $checkout['id'] ?? '',
                ];
            } catch (\Throwable $e) {
                \Log::warning('selectGateway: falha ao criar checkout SumUp', [
                    'order_id' => $order->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return view('checkout.transparent', array_merge([
            'order'          => $order->fresh('items', 'user'),
            'preferenceId'   => $preferenceId,
            'publicKey'      => $mpPublicKey,
            'gateway'        => null,
            'activeGateways' => $activeGateways,
        ], $sumupViewData));
    }

    /**
     * Processa o pagamento pelo gateway selecionado pelo cliente.
     */
    public function processGateway(Order $order, Request $request, ?string $gatewayOverride = null)
    {
        $this->abortIfOrderNotAccessible($order, $request);

        if ($order->status === 'paid') {
            return redirect()->route('events.payment.success', $order);
        }

        $selectedGateway = $gatewayOverride ?: $request->input('gateway');
        if (!$selectedGateway || !in_array($selectedGateway, ['mercadopago', 'sumup'], true)) {
            return back()->with('error', 'Selecione um método de pagamento.');
        }

        // Obter o evento do pedido
        $event = $this->getEventFromOrder($order);
        if (!$event) {
            return redirect()->route('panel.dashboard')->with('error', 'Pedido não encontrado.');
        }

        if ($redirect = $this->redirectZeroAmountOrderWithoutGateway($order, $event, 'event_process_gateway_zero_amount')) {
            return $redirect;
        }

        // Resolver gateways ativos
        $seller = $event->user ?: User::find($event->user_id);
        $sellerId = $seller ? (int) $seller->id : 0;
        $activeGateways = \App\Models\GatewayAccount::resolveAllActiveGatewaysForSeller($sellerId);
        $activeProviders = array_column($activeGateways, 'provider');

        if (!in_array($selectedGateway, $activeProviders, true)) {
            return back()->with('error', 'Gateway de pagamento não disponível para este evento.');
        }

        // Encontrar a config do gateway selecionado
        $gatewayConfig = null;
        foreach ($activeGateways as $gw) {
            if ($gw['provider'] === $selectedGateway) {
                $gatewayConfig = $gw;
                break;
            }
        }

        // Atualizar o gateway no pedido
        $order->update(['gateway' => $selectedGateway]);

        \Log::info('EventReservation: processando pagamento via gateway selecionado', [
            'order_id' => $order->id,
            'event_id' => $event->id,
            'gateway'  => $selectedGateway,
        ]);

        // Rotear para o processador correto
        if ($selectedGateway === 'sumup') {
            $sumUpService = app(\App\Services\Payment\SumUpService::class);
            return $this->processSumUpPayment($order, $event, $gatewayConfig, $sumUpService);
        } else {
            $mpService = app(\App\Services\Payment\MercadoPagoService::class);
            return $this->processMercadoPagoPayment($order, $event, $gatewayConfig, $mpService);
        }
    }

    public function paymentSuccess(Order $order, Request $request)
    {
        $this->abortIfOrderNotAccessible($order, $request);

        $event = $this->getEventFromOrder($order);
        $registration = $event
            ? EventRegistration::where('order_id', $order->id)->where('event_id', $event->id)->first()
            : null;

        return view('events.payment.success', compact('order', 'event', 'registration'));
    }

    public function paymentPending(Order $order, Request $request)
    {
        $this->abortIfOrderNotAccessible($order, $request);

        $event = $this->getEventFromOrder($order);
        $registration = $event
            ? EventRegistration::where('order_id', $order->id)->where('event_id', $event->id)->first()
            : null;

        return view('events.payment.pending', compact('order', 'event', 'registration'));
    }

    public function paymentFailure(Order $order, Request $request)
    {
        $this->abortIfOrderNotAccessible($order, $request);

        $event = $this->getEventFromOrder($order);
        $registration = $event
            ? EventRegistration::where('order_id', $order->id)->where('event_id', $event->id)->first()
            : null;

        return view('events.payment.failure', compact('order', 'event', 'registration'));
    }

    private function abortIfDisabledOrUnpublished(Event $event): void
    {
        $isEnabled = \App\Models\Setting::get('feature_events', '1') === '1';
        if (!$isEnabled || !$event->published) {
            abort(404);
        }
    }

    private function isEventClosed(Event $event): bool
    {
        $now = now();

        $start = $event->start_at ? \Carbon\Carbon::parse($event->start_at) : null;
        $end = $event->end_at ? \Carbon\Carbon::parse($event->end_at) : null;

        if ($end) {
            return $end->lt($now);
        }

        // If there's no end_at, consider the event closed only after the day has passed.
        return $start ? $start->lt($now->copy()->startOfDay()) : false;
    }

    private function abortIfOrderNotAccessible(Order $order, Request $request): void
    {
        $token = (string) $request->query('token');
        $storedToken = (string) data_get($order->metadata, 'public_token');

        $canAccess = Auth::check() && Auth::id() === $order->user_id;
        if (!$canAccess && $token !== '' && $storedToken !== '') {
            $canAccess = hash_equals($storedToken, $token);
        }

        abort_unless($canAccess, 403);
    }

    private function getEventFromOrder(Order $order): ?Event
    {
        $item = $order->items()->where('item_type', 'event')->first();
        if (!$item) {
            return null;
        }

        return Event::find($item->item_id);
    }

    private function usesSingleEventRegistrationPerUser(): bool
    {
        static $usesSingleRegistration = null;

        if ($usesSingleRegistration !== null) {
            return $usesSingleRegistration;
        }

        if (!Schema::hasTable('event_registrations')) {
            return $usesSingleRegistration = false;
        }

        try {
            $index = DB::connection()->selectOne(
                "SHOW INDEX FROM event_registrations WHERE Key_name = 'event_registrations_event_id_user_id_unique'"
            );

            return $usesSingleRegistration = $index !== null;
        } catch (\Throwable $e) {
            \Log::warning('Falha ao detectar indice unico legado de inscricoes de evento', [
                'message' => $e->getMessage(),
            ]);
        }

        return $usesSingleRegistration = false;
    }

    private function createFreeEventOrder(Event $event, int $userId, int $sellerId, int $quantity, float $regularUnitPrice, float $effectiveUnitPrice): Order
    {
        $platformFeePercent = MarketplaceFee::deductionPercent($sellerId > 0 ? $sellerId : null);

        $order = Order::create([
            'user_id' => $userId,
            'seller_id' => $sellerId > 0 ? $sellerId : null,
            'status' => 'pending',
            'total_amount' => 0,
            'fee_amount' => 0,
            'platform_fee_amount' => 0,
            'currency' => 'BRL',
            'gateway' => 'free',
            'gateway_account_id' => null,
            'metadata' => [
                'context' => 'event',
                'sale_type' => 'event',
                'public_token' => Str::random(40),
                'original_total_amount' => 0,
                'regular_total_amount' => round($regularUnitPrice * $quantity, 2),
                'platform_fee_percent' => $platformFeePercent,
                'is_free_checkout' => true,
            ],
        ]);

        $order->items()->create([
            'item_type' => 'event',
            'item_id' => $event->id,
            'title' => $event->title,
            'price' => 0,
            'quantity' => max(1, $quantity),
            'data' => [
                'event_start_at' => optional($event->start_at)->toIso8601String(),
                'event_end_at' => optional($event->end_at)->toIso8601String(),
                'batch_label' => $event->current_batch_label,
                'original_unit_price' => $effectiveUnitPrice,
                'regular_unit_price' => $regularUnitPrice,
                'flash_sale_price' => $event->flash_sale_price !== null ? (float) $event->flash_sale_price : null,
                'flash_sale_ends_at' => $event->flash_sale_ends_at ? $event->flash_sale_ends_at->toIso8601String() : null,
                'discount_amount' => round(max(0, $effectiveUnitPrice) * max(1, $quantity), 2),
            ],
        ]);

        return $order;
    }

    private function attachEventCouponToFreeOrder(Order $order, EventCoupon $coupon, float $discountAmount, int $quantity, float $effectiveUnitPrice): void
    {
        $couponPayload = [
            'id' => (int) $coupon->id,
            'code' => (string) $coupon->code,
            'type' => (string) $coupon->type,
            'discount_value' => (float) $coupon->discount_value,
            'discount_amount' => round($discountAmount, 2),
            'uses' => max(1, (int) $quantity),
        ];

        $order->update([
            'metadata' => array_merge($order->metadata ?? [], [
                'event_coupon' => $couponPayload,
                'original_total_amount' => round($effectiveUnitPrice * max(1, (int) $quantity), 2),
                'is_free_checkout' => true,
            ]),
        ]);

        $order->items()->get()->each(function ($item) use ($couponPayload) {
            $item->update([
                'data' => array_merge($item->data ?? [], [
                    'event_coupon' => $couponPayload,
                ]),
            ]);
        });
    }

    private function eventRegistrationPayload(array $payload): array
    {
        foreach (['coupon_id', 'payment_status', 'joined_group_at'] as $column) {
            if (array_key_exists($column, $payload) && !Schema::hasColumn('event_registrations', $column)) {
                unset($payload[$column]);
            }
        }

        return $payload;
    }

    private function redirectZeroAmountOrderWithoutGateway(Order $order, Event $event, string $source)
    {
        $order->loadMissing('items', 'user');

        if (round((float) $order->total_amount, 2) > 0) {
            return null;
        }

        $metadata = is_array($order->metadata) ? $order->metadata : [];
        $couponCode = trim((string) data_get($metadata, 'coupon.code', ''));

        $order->forceFill([
            'gateway' => 'free',
            'fee_amount' => 0,
            'platform_fee_amount' => 0,
            'metadata' => array_merge($metadata, [
                'is_free_checkout' => true,
                'free_checkout_reason' => $metadata['free_checkout_reason'] ?? 'zero_amount_order',
            ]),
        ])->save();

        app(OrderSettlementService::class)->settleAsPaid($order, [
            'transaction_id' => 'FREE-ORDER-' . $order->id . '-' . now()->format('YmdHis'),
            'payment_method' => $couponCode !== '' ? 'free_coupon' : 'free_checkout',
            'queue_invoice_email' => false,
            'send_notifications' => false,
            'gateway_data' => array_filter([
                'source' => $source,
                'coupon_code' => $couponCode !== '' ? $couponCode : null,
                'automatic' => true,
            ], fn ($value) => $value !== null && $value !== ''),
        ]);

        return redirect()->route('events.show', $event)->with('success', 'Vaga confirmada com sucesso!');
    }

    /**
     * Processa pagamento via SumUp
     */
    private function processSumUpPayment(Order $order, Event $event, array $gatewayConfig, \App\Services\Payment\SumUpService $sumUpService)
    {
        try {
            $order->load('items', 'user');

            if ($redirect = $this->redirectZeroAmountOrderWithoutGateway($order, $event, 'sumup_zero_amount_bypass')) {
                return $redirect;
            }

            // Validar configuração do gateway
            $sumupConfig = $gatewayConfig['config'] ?? [];
            $apiKey      = $sumupConfig['apiKey'] ?? null;
            $merchantCode = $sumupConfig['merchantCode'] ?? null;

            // Se merchantCode não veio da conta do vendedor, buscar das settings globais
            if (empty($merchantCode)) {
                $merchantCode = trim((string) (\App\Models\Setting::get('sumup_merchant_code')
                    ?: config('payments.sumup.merchant_code', '')));
            }

            // Apenas a API Key é obrigatória — sem ela não é possível criar o checkout
            if (empty($apiKey)) {
                \Log::error('API Key SumUp não configurada', [
                    'event_id'       => $event->id,
                    'order_id'       => $order->id,
                    'seller_id'      => $event->user_id,
                    'gateway_config' => $gatewayConfig,
                ]);

                return back()->with('error', 'Não foi possível iniciar o pagamento via SumUp. O organizador ainda não configurou as credenciais do gateway.');
            }

            // Reutilizar o checkout já criado evita DUPLICATED_CHECKOUT ao reenviar a reserva.
            $existingCheckoutId = trim((string) data_get($order->metadata, 'sumup_checkout_id', ''));
            if ($existingCheckoutId === '') {
                $existingCheckoutId = trim((string) SumUpTransaction::query()
                    ->where('order_id', $order->id)
                    ->latest('id')
                    ->value('checkout_id'));
            }

            $checkout = $existingCheckoutId !== ''
                ? ['checkout_id' => $existingCheckoutId]
                : $sumUpService->createCheckout($order, [
                    'description' => 'Ingresso: ' . $event->title . ($order->user ? ' - ' . $order->user->name : ''),
                    'return_url' => route('events.payment.success', $order->id),
                ]);

            $checkoutId = trim((string) ($checkout['checkout_id'] ?? $checkout['id'] ?? $existingCheckoutId));

            // Salvar dados do checkout no pedido
            $order->update([
                'metadata' => array_merge($order->metadata ?? [], [
                    'sumup_checkout_id' => $checkoutId,
                    'sumup_checkout_url' => $checkout['checkout_url'] ?? null,
                ]),
            ]);

            // Recarregar para pegar total_amount atualizado caso a taxa tenha sido repassada
            $order = $order->fresh('items', 'user');

            // Resolver chave pública do SumUp
            $sumupPublicKey = $merchantCode;

            // Ler settings com fallback explícito para 1 (habilitado por padrão)
            $methodCardRaw = \App\Models\Setting::get('sumup_method_card');
            $methodPixRaw  = \App\Models\Setting::get('sumup_method_pix');
            $methodCard = $methodCardRaw !== null ? (bool)(int)$methodCardRaw : true;
            $methodPix  = $methodPixRaw  !== null ? (bool)(int)$methodPixRaw  : true;

            // Parcelamento e taxas
            $maxInstallments      = max(1, min(12, (int) (\App\Models\Setting::get('sumup_max_installments', 12))));
            $noInterestUpTo       = max(1, min(12, (int) (\App\Models\Setting::get('sumup_installments_no_interest', 1))));
            $installmentTax       = max(0.0, (float) (\App\Models\Setting::get('sumup_installment_tax', 0)));
            $passFeeToClient      = (bool)(int)(\App\Models\Setting::get('sumup_pass_fee', 0));

            return view('checkout.transparent', [
                'order'                    => $order,
                'checkoutId'               => $checkoutId,
                'publicKey'                => $sumupPublicKey,
                'gateway'                  => 'sumup',
                'sumupMethodCard'          => $methodCard,
                'sumupMethodPix'           => $methodPix,
                'sumupApiKey'              => $apiKey,
                'sumupMaxInstallments'     => $maxInstallments,
                'sumupNoInterestUpTo'      => $noInterestUpTo,
                'sumupInstallmentTax'      => $installmentTax,
                'sumupPassFeeToClient'     => $passFeeToClient,
                'sumupInterestType'        => \App\Models\Setting::get('sumup_interest_type', 'per_installment'),
                'sumupPixExpirationMinutes' => (int) (\App\Models\Setting::get('sumup_pix_expiration_minutes', 10) ?: 10),
            ]);
        } catch (\Throwable $e) {
            \Log::error('Falha ao iniciar pagamento SumUp de evento', [
                'event_id'        => $event->id,
                'order_id'        => $order?->id,
                'seller_id'       => $event->user_id,
                'exception_class' => get_class($e),
                'message'         => $e->getMessage(),
                'file'            => $e->getFile() . ':' . $e->getLine(),
                'trace'           => $e->getTraceAsString(),
            ]);

            return back()->with('error', 'Não foi possível iniciar o pagamento via SumUp. Tente novamente em instantes.');
        }
    }

    /**
     * Processa pagamento via Mercado Pago
     */
    private function processMercadoPagoPayment(Order $order, Event $event, array $gatewayConfig, \App\Services\Payment\MercadoPagoService $mpService)
    {
        try {
            $order->load('items', 'user');

            if ($redirect = $this->redirectZeroAmountOrderWithoutGateway($order, $event, 'mercadopago_zero_amount_bypass')) {
                return $redirect;
            }

            // MercadoPago — usa o token correto do vendedor via MercadoPagoService
            $preference = $mpService->createPreference($order, [
                'statement_descriptor' => 'UNN EVENTOS',
                'back_urls' => [
                    'success' => route('events.payment.success', $order->id),
                    'failure' => route('events.payment.failure', $order->id),
                    'pending' => route('events.payment.pending', $order->id),
                ],
            ]);

            $order->update([
                'metadata' => array_merge($order->metadata ?? [], [
                    'mercadopago_preference_id' => $preference['id'] ?? null,
                    'mercadopago_init_point' => $preference['init_point'] ?? null,
                    'mercadopago_sandbox_init_point' => $preference['sandbox_init_point'] ?? null,
                ]),
            ]);

            $sellerId = (int) ($event->seller_id ?: $event->user_id);
            $platformOwnerId = \App\Models\Setting::get('platform_owner_id', 2);
            $isPlatformOwner = $sellerId === (int) $platformOwnerId;

            // Resolver a public key do MP
            $mpPublicKey = null;

            // 1. Tentar da config do gateway que já foi resolvida
            if (!empty($gatewayConfig['config']['mpPublicKey'])) {
                $mpPublicKey = $gatewayConfig['config']['mpPublicKey'];
            }

            // 2. Fallback: resolver via GatewayAccount
            if (empty($mpPublicKey) && !$isPlatformOwner) {
                $sellerMpAccount = \App\Models\GatewayAccount::resolveForSeller($sellerId);
                $mpPublicKey = $sellerMpAccount['mpPublicKey'] ?? null;
            }

            // 3. Fallback final: settings globais
            if (empty($mpPublicKey)) {
                $mpEnv = (string) \App\Models\Setting::get('mercadopago_env', 'sandbox');
                $prefix = $mpEnv === 'production' ? 'mercadopago_prod_' : 'mercadopago_sandbox_';
                $mpPublicKey = \App\Models\Setting::get($prefix . 'public_key')
                    ?: config('payments.mercadopago.public_key')
                    ?: \App\Models\Setting::get('mp_public_key', '');
            }

            return view('checkout.transparent', [
                'order' => $order,
                'preferenceId' => $preference['id'] ?? '',
                'publicKey' => $mpPublicKey ?: '',
                'gateway' => 'mercadopago',
            ]);
        } catch (\Exception $e) {
            \Log::error('Falha ao iniciar pagamento de evento', [
                'event_id' => $event->id,
                'order_id' => $order?->id,
                'message' => $e->getMessage(),
            ]);

            $friendlyMessage = 'Não foi possível iniciar o pagamento agora.';
            $rawMessage = (string) $e->getMessage();

            if (str_contains($rawMessage, 'PA_UNAUTHORIZED_RESULT_FROM_POLICIES') || str_contains($rawMessage, 'PolicyAgent')) {
                $friendlyMessage = 'Mercado Pago indisponível no momento. Tente novamente em instantes.';
            } elseif (str_contains($rawMessage, 'MercadoPago Preference Error')) {
                $friendlyMessage = 'Não foi possível gerar a sessão de pagamento no Mercado Pago.';
            }

            return back()->with('error', $friendlyMessage);
        }
    }
}
