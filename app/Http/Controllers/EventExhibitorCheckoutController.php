<?php

namespace App\Http\Controllers;

use App\Http\Requests\EventExhibitor\EventExhibitorCheckoutRequest;
use App\Models\Event;
use App\Models\EventExhibitorRegistration;
use App\Models\GatewayAccount;
use App\Models\Order;
use App\Models\Plan;
use App\Models\Setting;
use App\Models\User;
use App\Services\EventCouponService;
use App\Services\EventExhibitorService;
use App\Services\OrderSettlementService;
use App\Services\Payment\MercadoPagoService;
use App\Services\Payment\SumUpService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class EventExhibitorCheckoutController extends Controller
{
    public function __construct(private readonly EventExhibitorService $exhibitorService)
    {
    }

    public function show(Event $event)
    {
        $this->abortIfUnavailable($event, false);

        return view('events.exhibitor.checkout', [
            'event' => $event,
            'status' => $event->exhibitorSalesStatus(),
            'currentBatch' => $this->exhibitorService->currentBatch($event),
            'remainingSlots' => $event->remaining_exhibitor_slots,
            'activeGateways' => $this->activeGateways($event),
        ]);
    }

    public function checkout(
        EventExhibitorCheckoutRequest $request,
        Event $event,
        EventCouponService $eventCouponService,
        MercadoPagoService $mpService,
        SumUpService $sumUpService,
        OrderSettlementService $settlementService
    ) {
        $this->abortIfUnavailable($event, true);

        $user = $this->resolveBuyer($request);
        $activeGateways = $this->activeGateways($event);
        $gatewayProvider = $this->resolveGatewayProvider($request, $activeGateways);
        $validatedPayload = $request->validated();

        $couponData = null;
        $quantity = max(1, min(20, (int) ($validatedPayload['quantity'] ?? 1)));
        $couponCode = $eventCouponService->normalizeCode($validatedPayload['coupon_code'] ?? null);
        $currentBatch = $this->exhibitorService->currentBatch($event);
        $grossSubtotal = round((float) (($currentBatch['price'] ?? 0) * $quantity), 2);

        if ($couponCode !== '' && $grossSubtotal > 0) {
            try {
                $couponData = $eventCouponService->validateCouponLocked($event, $couponCode, $grossSubtotal, $quantity);
            } catch (\Throwable $e) {
                return back()->withInput()->withErrors(['coupon_code' => $e->getMessage()]);
            }
        }

        if ($gatewayProvider === null && (float) ($event->currentExhibitorPriceFor() ?? 0) > 0) {
            return back()
                ->withInput()
                ->with('error', 'Pagamento indisponivel: o organizador ainda nao configurou um metodo de pagamento.');
        }

        try {
            $reservation = $this->exhibitorService->createReservation(
                $event,
                $user,
                $validatedPayload,
                $gatewayProvider,
                $couponData
            );
        } catch (\Throwable $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        /** @var Order $order */
        $order = $reservation['order'];

        if ((float) $order->total_amount <= 0) {
            $couponCode = trim((string) data_get($order->metadata, 'event_coupon.code', ''));
            $settlementService->settleAsPaid($order, [
                'transaction_id' => 'FREE-EXHIBITOR-' . $order->id . '-' . now()->format('YmdHis'),
                'payment_method' => $couponCode !== '' ? 'free_coupon' : 'free_checkout',
                'queue_invoice_email' => false,
                'send_notifications' => false,
                'gateway_data' => [
                    'source' => 'free_event_exhibitor_checkout',
                    'coupon_code' => $couponCode !== '' ? $couponCode : null,
                    'automatic' => true,
                ],
            ]);

            return redirect()->route('events.exhibitor.success', [
                'event' => $event,
                'order' => $order,
                'token' => data_get($order->metadata, 'public_token'),
            ]);
        }

        return $this->renderGatewayCheckout($order, $event, $activeGateways, $gatewayProvider, $mpService, $sumUpService);
    }

    public function success(Event $event, Order $order, Request $request)
    {
        $this->abortIfOrderNotAccessible($order, $request);
        $registration = $this->registrationFor($event, $order);

        return view('events.exhibitor.success', compact('event', 'order', 'registration'));
    }

    public function pending(Event $event, Order $order, Request $request)
    {
        $this->abortIfOrderNotAccessible($order, $request);
        $registration = $this->registrationFor($event, $order);

        return view('events.exhibitor.pending', compact('event', 'order', 'registration'));
    }

    public function failure(Event $event, Order $order, Request $request)
    {
        $this->abortIfOrderNotAccessible($order, $request);
        $registration = $this->registrationFor($event, $order);

        return view('events.exhibitor.failure', compact('event', 'order', 'registration'));
    }

    private function renderGatewayCheckout(
        Order $order,
        Event $event,
        array $activeGateways,
        ?string $gatewayProvider,
        MercadoPagoService $mpService,
        SumUpService $sumUpService
    ) {
        $gatewayConfig = $this->gatewayConfig($activeGateways, $gatewayProvider);
        if (!$gatewayConfig) {
            return redirect()->route('events.exhibitor.show', $event)
                ->with('error', 'Gateway de pagamento indisponivel para este evento.');
        }

        if ($gatewayProvider === 'sumup') {
            try {
                $existingCheckoutId = trim((string) data_get($order->metadata, 'sumup_checkout_id', ''));
                $checkout = $existingCheckoutId !== ''
                    ? ['checkout_id' => $existingCheckoutId]
                    : $sumUpService->createCheckout($order, [
                        'description' => 'Area expositor: ' . $event->title,
                        'return_url' => route('events.exhibitor.success', [
                            'event' => $event,
                            'order' => $order,
                            'token' => data_get($order->metadata, 'public_token'),
                        ]),
                    ]);

                $order->update([
                    'metadata' => array_merge($order->metadata ?? [], [
                        'sumup_checkout_id' => $checkout['checkout_id'] ?? $existingCheckoutId,
                    ]),
                ]);

                $methodCardRaw = Setting::get('sumup_method_card');
                $methodPixRaw = Setting::get('sumup_method_pix');

                return view('checkout.transparent', [
                    'order' => $order->fresh(['items', 'user']),
                    'preferenceId' => '',
                    'publicKey' => $gatewayConfig['config']['merchantCode'] ?? '',
                    'gateway' => 'sumup',
                    'checkoutId' => $checkout['checkout_id'] ?? '',
                    'sumupMethodCard' => $methodCardRaw !== null ? (bool) (int) $methodCardRaw : true,
                    'sumupMethodPix' => $methodPixRaw !== null ? (bool) (int) $methodPixRaw : true,
                    'sumupApiKey' => $gatewayConfig['config']['apiKey'] ?? '',
                    'sumupMaxInstallments' => max(1, min(12, (int) (Setting::get('sumup_max_installments', 12)))),
                    'sumupNoInterestUpTo' => max(1, min(12, (int) (Setting::get('sumup_installments_no_interest', 1)))),
                    'sumupInstallmentTax' => max(0.0, (float) (Setting::get('sumup_installment_tax', 0))),
                    'sumupPassFeeToClient' => (bool) (int) (Setting::get('sumup_pass_fee', 0)),
                    'sumupInterestType' => Setting::get('sumup_interest_type', 'per_installment'),
                    'sumupPixExpirationMinutes' => (int) (Setting::get('sumup_pix_expiration_minutes', 10) ?: 10),
                ]);
            } catch (\Throwable $e) {
                return redirect()->route('events.exhibitor.show', $event)
                    ->with('error', 'Nao foi possivel iniciar o pagamento via SumUp. Tente novamente em instantes.');
            }
        }

        try {
            $preference = $mpService->createPreference($order, [
                'statement_descriptor' => 'UNN EVENTOS',
                'back_urls' => [
                    'success' => route('events.exhibitor.success', [
                        'event' => $event,
                        'order' => $order,
                        'token' => data_get($order->metadata, 'public_token'),
                    ]),
                    'failure' => route('events.exhibitor.failure', [
                        'event' => $event,
                        'order' => $order,
                        'token' => data_get($order->metadata, 'public_token'),
                    ]),
                    'pending' => route('events.exhibitor.pending', [
                        'event' => $event,
                        'order' => $order,
                        'token' => data_get($order->metadata, 'public_token'),
                    ]),
                ],
            ]);

            $order->update([
                'metadata' => array_merge($order->metadata ?? [], [
                    'mercadopago_preference_id' => $preference['id'] ?? null,
                    'mercadopago_init_point' => $preference['init_point'] ?? null,
                    'mercadopago_sandbox_init_point' => $preference['sandbox_init_point'] ?? null,
                ]),
            ]);

            return view('checkout.transparent', [
                'order' => $order->fresh(['items', 'user']),
                'preferenceId' => $preference['id'] ?? '',
                'publicKey' => $this->mercadoPagoPublicKey($event, $gatewayConfig),
                'gateway' => 'mercadopago',
            ]);
        } catch (\Throwable $e) {
            return redirect()->route('events.exhibitor.show', $event)
                ->with('error', 'Nao foi possivel iniciar o pagamento via Mercado Pago. Tente novamente em instantes.');
        }
    }

    private function resolveBuyer(EventExhibitorCheckoutRequest $request): User
    {
        if (Auth::check()) {
            $user = Auth::user();
            $updates = [];
            if (!$user->phone && $request->filled('phone')) {
                $updates['phone'] = $request->input('phone');
            }
            if (!$user->doc && $request->filled('document')) {
                $updates['doc'] = $request->input('document');
            }
            if (!empty($updates)) {
                $user->update($updates);
            }

            return $user;
        }

        $user = User::create([
            'name' => $request->string('name')->toString(),
            'email' => $request->string('email')->toString(),
            'password' => Hash::make($request->string('password')->toString()),
            'doc' => $request->string('document')->toString(),
            'phone' => $request->string('phone')->toString(),
            'level' => 'iniciante',
        ]);

        try {
            $defaultPlan = Plan::query()->where('slug', 'cliente')->first()
                ?? Plan::query()->orderBy('price')->orderBy('id')->first();
            if ($defaultPlan) {
                $user->plan_id = (int) $defaultPlan->id;
                $user->plan_expires_at = null;
                $user->save();
            }
        } catch (\Throwable $e) {
            // Mantem o cadastro funcional mesmo sem plano inicial disponivel.
        }

        Auth::login($user);

        return $user;
    }

    private function activeGateways(Event $event): array
    {
        $sellerId = (int) ($event->user_id ?? 0);

        return GatewayAccount::resolveAllActiveGatewaysForSeller($sellerId);
    }

    private function resolveGatewayProvider(EventExhibitorCheckoutRequest $request, array $activeGateways): ?string
    {
        $providers = array_column($activeGateways, 'provider');
        if (empty($providers)) {
            return null;
        }

        if (count($providers) === 1) {
            return $providers[0];
        }

        $selected = (string) $request->input('gateway', '');

        return in_array($selected, $providers, true) ? $selected : null;
    }

    private function gatewayConfig(array $activeGateways, ?string $provider): ?array
    {
        foreach ($activeGateways as $gateway) {
            if (($gateway['provider'] ?? null) === $provider) {
                return $gateway;
            }
        }

        return null;
    }

    private function mercadoPagoPublicKey(Event $event, array $gatewayConfig): string
    {
        if (!empty($gatewayConfig['config']['mpPublicKey'])) {
            return (string) $gatewayConfig['config']['mpPublicKey'];
        }

        $sellerId = (int) ($event->user_id ?? 0);
        $sellerMpAccount = $sellerId > 0 ? GatewayAccount::resolveForSeller($sellerId) : [];
        if (!empty($sellerMpAccount['mpPublicKey'])) {
            return (string) $sellerMpAccount['mpPublicKey'];
        }

        $mpEnv = (string) Setting::get('mercadopago_env', 'sandbox');
        $prefix = $mpEnv === 'production' ? 'mercadopago_prod_' : 'mercadopago_sandbox_';

        return (string) (
            Setting::get($prefix . 'public_key')
            ?: config('payments.mercadopago.public_key')
            ?: Setting::get('mp_public_key', '')
        );
    }

    private function abortIfUnavailable(Event $event, bool $post): void
    {
        $isEnabled = Setting::get('feature_events', '1') === '1';
        abort_unless($isEnabled && $event->published && $event->isEvent(), 404);

        if (!$event->canSellExhibitorArea()) {
            if ($post) {
                abort(422, 'Areas para expositores indisponiveis neste evento.');
            }
            abort(404);
        }
    }

    private function abortIfOrderNotAccessible(Order $order, Request $request): void
    {
        $token = (string) $request->query('token');
        $storedToken = (string) data_get($order->metadata, 'public_token');

        $canAccess = Auth::check() && (int) Auth::id() === (int) $order->user_id;
        if (!$canAccess && $token !== '' && $storedToken !== '') {
            $canAccess = hash_equals($storedToken, $token);
        }

        abort_unless($canAccess, 403);
    }

    private function registrationFor(Event $event, Order $order): ?EventExhibitorRegistration
    {
        return EventExhibitorRegistration::query()
            ->where('event_id', (int) $event->id)
            ->where('order_id', (int) $order->id)
            ->first();
    }
}
