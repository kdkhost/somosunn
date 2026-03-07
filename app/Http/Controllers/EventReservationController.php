<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\EventRegistration;
use App\Models\Order;
use App\Models\CouponRedemption;
use App\Models\Plan;
use App\Models\User;
use App\Services\CouponService;
use App\Support\MarketplaceFee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class EventReservationController extends Controller
{
    public function checkout(Event $event)
    {
        $this->abortIfDisabledOrUnpublished($event);

        if ($this->isEventClosed($event)) {
            return redirect()->route('events.show', $event)->with('error', 'Este evento já encerrou.');
        }

        $isPaid = (float) $event->effective_price > 0;
        $mpEnabled        = false;
        $psEnabled        = false;
        $preferredGateway = null;
        if ($isPaid) {
            $seller = $event->user ?: User::find($event->user_id);
            if ($seller && !$seller->canSellOnMarketplace()) {
                return redirect()
                    ->route('events.show', $event)
                    ->with('error', 'Este organizador não está habilitado para vender no marketplace.');
            }

            // Verificar gateways configurados pelo vendedor (tabela gateway_accounts)
            $gateways = $seller ? \App\Models\GatewayAccount::resolveForSeller((int) $seller->id)
                : ['mpEnabled' => false, 'psEnabled' => false, 'preferredGateway' => null, 'useGlobalCredentials' => false];
            $mpEnabled        = $gateways['mpEnabled'];
            $psEnabled        = $gateways['psEnabled'];
            $preferredGateway = $gateways['preferredGateway'];

            // Lógica igual ao painel: permite compra se credenciais globais do MercadoPago estão configuradas
            // Buscar credenciais globais primeiro no banco, depois fallback para config/.env
            $mpAccessToken = (string) (\App\Models\Setting::get('mercadopago_access_token') ?: config('payments.mercadopago.access_token'));
            $mpPublicKey = (string) (\App\Models\Setting::get('mercadopago_public_key') ?: config('payments.mercadopago.public_key'));
            $psToken = (string) (\App\Models\Setting::get('pagseguro_token') ?: config('payments.pagseguro.token'));
            $paymentsConfigured = ($mpAccessToken !== '' && $mpPublicKey !== '') || ($psToken !== '');

            if (!$mpEnabled && !$psEnabled && empty($gateways['useGlobalCredentials']) && !$paymentsConfigured) {
                \Illuminate\Support\Facades\Log::warning('EventCheckout: organizador sem gateway configurado', [
                    'event_id'    => $event->id,
                    'event_user_id' => $event->user_id,
                    'seller_id'   => $seller ? $seller->id : null,
                    'seller_found' => $seller !== null,
                    'mp_enabled'  => $mpEnabled,
                    'ps_enabled'  => $psEnabled,
                    'use_global'  => $gateways['useGlobalCredentials'] ?? false,
                    'mp_global_configured' => $paymentsConfigured,
                ]);
                return redirect()
                    ->route('events.show', $event)
                    ->with('error', 'Este evento não está disponível para compra: o organizador ainda não configurou um método de pagamento.');
            }
        }

        $registration = null;
        if (Auth::check()) {
            $registration = EventRegistration::where('event_id', $event->id)
                ->where('user_id', Auth::id())
                ->first();
        }

        return view('events.checkout', compact('event', 'registration', 'mpEnabled', 'psEnabled', 'preferredGateway'));
    }

    public function reserve(Request $request, Event $event, CouponService $couponService, \App\Services\Payment\MercadoPagoService $mpService)
    {
        $this->abortIfDisabledOrUnpublished($event);

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
        $seller = $event->user ?: User::find($event->user_id);
        $sellerId = $seller ? (int) $seller->id : (int) ($event->user_id ?? 0);

        // Determinar gateways disponíveis e gateway selecionado pelo comprador
        $paymentsConfigured = false;
        $gatewayProvider = 'mercadopago';
        if ($isPaid && $sellerId) {
            $gateways = \App\Models\GatewayAccount::resolveForSeller($sellerId);
            $paymentsConfigured = $gateways['mpEnabled'] || $gateways['psEnabled'];
            $defaultGateway = $gateways['preferredGateway'] ?? ($gateways['mpEnabled'] ? 'mercadopago' : 'pagseguro');
            $requestedGateway = $request->input('gateway_provider', $defaultGateway);
            if ($requestedGateway === 'pagseguro' && $gateways['psEnabled']) {
                $gatewayProvider = 'pagseguro';
            } elseif ($gateways['mpEnabled']) {
                $gatewayProvider = 'mercadopago';
            } elseif ($gateways['psEnabled']) {
                $gatewayProvider = 'pagseguro';
            }
        }

        if ($isPaid) {
            if ($seller && !$seller->canSellOnMarketplace()) {
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
                'cpf' => $isPaid ? 'required|string' : 'nullable|string',
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
        $regularUnitPrice = (float) $event->current_price;
        $currentPrice = (float) $event->effective_price;
        $couponCode = $isPaid ? $couponService->normalizeCode($request->input('coupon_code')) : '';

        try {
            DB::transaction(function () use ($event, $user, $sellerId, $quantity, $isPaid, $regularUnitPrice, $currentPrice, $couponCode, $couponService, &$registration, &$order) {
                $registration = EventRegistration::where('event_id', $event->id)
                    ->where('user_id', $user->id)
                    ->lockForUpdate()
                    ->first();

                if ($registration && in_array($registration->status, EventRegistration::COUNTED_STATUSES, true)) {
                    return;
                }

                if (!$isPaid) {
                    if ($event->capacity && !$event->hasCapacityFor($quantity)) {
                        throw new \RuntimeException('Evento lotado no momento.');
                    }

                    if (!$registration) {
                        $registration = EventRegistration::create([
                            'event_id' => $event->id,
                            'user_id' => $user->id,
                            'status' => EventRegistration::STATUS_CONFIRMED,
                            'price' => 0,
                            'quantity' => $quantity,
                        ]);
                    } else {
                        $registration->fill([
                            'status' => EventRegistration::STATUS_CONFIRMED,
                            'price' => 0,
                            'quantity' => $quantity,
                            'order_id' => null,
                        ])->save();
                    }

                    // Notificar confirmação da vaga (Evento Gratuito)
                    $user->notify(new \App\Notifications\AppNotification([
                        'message' => 'Sua vaga no evento "' . $event->title . '" foi confirmada com sucesso!',
                        'type' => 'EventConfirmed',
                        'action_url' => route('events.show', $event),
                        'action_label' => 'Ver detalhes'
                    ]));

                    return;
                }

                if ($event->capacity && !$event->hasCapacityFor($quantity)) {
                    throw new \RuntimeException('Evento lotado no momento.');
                }

                if ($registration && $registration->order_id) {
                    $existingOrder = Order::whereKey($registration->order_id)->lockForUpdate()->first();
                    if ($existingOrder && $existingOrder->status !== 'paid') {
                        $order = $existingOrder;
                    }
                }

                $originalTotal = round($currentPrice * $quantity, 2);
                $discountAmount = 0.0;
                $coupon = null;

                if ($couponCode !== '') {
                    $result = $couponService->validateAndCalculateLocked(
                        $couponCode,
                        CouponService::CONTEXT_EVENT,
                        (int) $event->id,
                        (int) $user->id,
                        (float) $originalTotal
                    );
                    $coupon = $result['coupon'];
                    $discountAmount = (float) $result['discount_amount'];
                }

                $finalTotal = max(0, round($originalTotal - $discountAmount, 2));
                $platformFeePercent = MarketplaceFee::percent();
                $platformFeeAmount = MarketplaceFee::amount($finalTotal);

                if (!$order) {
                    $order = Order::create([
                        'user_id' => $user->id,
                        'seller_id' => $sellerId > 0 ? $sellerId : null,
                        'status' => 'pending',
                        'total_amount' => $finalTotal,
                        'fee_amount' => 0,
                        'platform_fee_amount' => $platformFeeAmount,
                        'currency' => 'BRL',
                        'gateway' => $gatewayProvider,
                        'gateway_account_id' => null,
                        'metadata' => [
                            'context' => 'event',
                            'sale_type' => 'event',
                            'public_token' => Str::random(40),
                            'original_total_amount' => $originalTotal,
                            'regular_total_amount' => round($regularUnitPrice * $quantity, 2),
                            'platform_fee_percent' => $platformFeePercent,
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
                        'gateway' => $gatewayProvider,
                        'metadata' => array_merge($order->metadata ?? [], [
                            'context' => 'event',
                            'sale_type' => 'event',
                            'original_total_amount' => $originalTotal,
                            'regular_total_amount' => round($regularUnitPrice * $quantity, 2),
                            'platform_fee_percent' => $platformFeePercent,
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

                if (!$registration) {
                    $registration = EventRegistration::create([
                        'event_id' => $event->id,
                        'user_id' => $user->id,
                        'order_id' => $order->id,
                        'status' => EventRegistration::STATUS_PENDING,
                        'price' => $currentPrice,
                        'quantity' => $quantity,
                    ]);
                } else {
                    $registration->fill([
                        'order_id' => $order->id,
                        'status' => EventRegistration::STATUS_PENDING,
                        'price' => $currentPrice,
                        'quantity' => $quantity,
                    ])->save();
                }
            });
        } catch (ValidationException $e) {
            $msg = collect($e->errors())->flatten()->first() ?? 'Não foi possível aplicar o cupom.';
            return back()->with('error', $msg)->withInput();
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage())->withInput();
        }

        if ($registration && in_array($registration->status, EventRegistration::COUNTED_STATUSES, true)) {
            return redirect()->route('events.show', $event)->with('success', 'Sua vaga já está confirmada.');
        }

        if (!$isPaid) {
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

        if (!$paymentsConfigured) {
            return redirect()->route('events.show', $event)->with('error', 'Pagamento indisponível: o organizador ainda não configurou um método de pagamento.');
        }

        try {
            $order->load('items', 'user');

            if ($gatewayProvider === 'pagseguro') {
                return view('checkout.pagseguro_transparent', [
                    'order' => $order,
                    'publicKey' => config('payments.pagseguro.public_key'),
                    'pixAvailable' => $psService->isPixAvailable($order),
                ]);
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

            $initPoint = $preference['init_point'] ?? $preference['sandbox_init_point'] ?? null;
            if (!$initPoint) {
                return redirect()->route('events.show', $event)->with('error', 'Pagamento indisponível no momento.');
            }

            return redirect()->away($initPoint);
        } catch (\Exception $e) {
            return back()->with('error', 'Erro ao iniciar pagamento: ' . $e->getMessage());
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
}
