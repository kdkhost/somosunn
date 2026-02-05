<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\EventRegistration;
use App\Models\GatewayAccount;
use App\Models\Order;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class EventReservationController extends Controller
{
    public function checkout(Event $event)
    {
        $this->abortIfDisabledOrUnpublished($event);

        $registration = null;
        if (Auth::check()) {
            $registration = EventRegistration::where('event_id', $event->id)
                ->where('user_id', Auth::id())
                ->first();
        }

        return view('events.checkout', compact('event', 'registration'));
    }

    public function reserve(Request $request, Event $event)
    {
        $this->abortIfDisabledOrUnpublished($event);

        if (($event->is_demo ?? false) === true) {
            return redirect()->route('events.show', $event)->with('error', 'Este é um evento de demonstração.');
        }

        $quantity = (int) $request->input('quantity', 1);
        $quantity = max(1, min(10, $quantity));

        $isPaid = (float) $event->current_price > 0;

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
                'level' => 'Iniciante',
            ]);

            Auth::login($user);
        }

        if ($event->capacity && !$event->hasCapacityFor($quantity)) {
            return back()->with('error', 'Evento lotado no momento.')->withInput();
        }

        $registration = null;
        $order = null;
        $currentPrice = (float) $event->current_price;

        DB::transaction(function () use ($event, $user, $quantity, $isPaid, $currentPrice, &$registration, &$order) {
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

            if (!$order) {
                $order = Order::create([
                    'user_id' => $user->id,
                    'seller_id' => null,
                    'status' => 'pending',
                    'total_amount' => $currentPrice * $quantity,
                    'fee_amount' => 0,
                    'platform_fee_amount' => 0,
                    'currency' => 'BRL',
                    'gateway' => 'mercadopago',
                    'gateway_account_id' => null,
                    'metadata' => [
                        'context' => 'event',
                        'public_token' => Str::random(40),
                    ],
                ]);

                $order->items()->create([
                    'item_type' => 'event',
                    'item_id' => $event->id,
                    'title' => $event->title,
                    'price' => $currentPrice,
                    'quantity' => $quantity,
                    'data' => [
                        'event_start_at' => optional($event->start_at)->toIso8601String(),
                        'event_end_at' => optional($event->end_at)->toIso8601String(),
                        'batch_label' => $event->current_batch_label,
                    ],
                ]);
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

        if ($registration && in_array($registration->status, EventRegistration::COUNTED_STATUSES, true)) {
            return redirect()->route('events.show', $event)->with('success', 'Sua vaga já está confirmada.');
        }

        if (!$isPaid) {
            return redirect()->route('events.show', $event)->with('success', 'Vaga confirmada com sucesso!');
        }

        $gatewayAccount = $this->resolveEventsGatewayAccount();
        if (!$gatewayAccount) {
            return redirect()->route('events.show', $event)->with('error', 'Pagamento indisponível: configure o MercadoPago no painel.');
        }

        $token = data_get($order->metadata, 'public_token');
        $sellerId = $gatewayAccount->exists ? (string) $gatewayAccount->user_id : 'platform';

        $backUrls = [
            'success' => route('events.payment.success', ['order' => $order->id, 'token' => $token]),
            'failure' => route('events.payment.failure', ['order' => $order->id, 'token' => $token]),
            'pending' => route('events.payment.pending', ['order' => $order->id, 'token' => $token]),
        ];

        $preferenceData = [
            'items' => $order->items->map(fn ($item) => [
                'title' => $item->title,
                'quantity' => (int) $item->quantity,
                'currency_id' => 'BRL',
                'unit_price' => (float) $item->price,
            ])->values()->all(),
            'payer' => [
                'name' => $order->user->name,
                'email' => $order->user->email,
            ],
            'back_urls' => $backUrls,
            'auto_return' => 'approved',
            'external_reference' => (string) $order->id,
            'statement_descriptor' => 'UNN EVENTOS',
            'notification_url' => route('webhook.mercadopago', ['seller_id' => $sellerId]),
        ];

        $response = Http::withToken($gatewayAccount->access_token)
            ->post('https://api.mercadopago.com/checkout/preferences', $preferenceData);

        if ($response->failed()) {
            return redirect()->route('events.show', $event)->with('error', 'Falha ao iniciar pagamento. Tente novamente.');
        }

        $pref = $response->json();
        $order->update([
            'seller_id' => $gatewayAccount->user_id,
            'gateway' => 'mercadopago',
            'gateway_account_id' => $gatewayAccount->exists ? $gatewayAccount->id : null,
            'metadata' => array_merge($order->metadata ?? [], [
                'mercadopago_preference_id' => $pref['id'] ?? null,
                'mercadopago_init_point' => $pref['init_point'] ?? null,
                'mercadopago_sandbox_init_point' => $pref['sandbox_init_point'] ?? null,
            ]),
        ]);

        $initPoint = $pref['init_point'] ?? null;
        $sandboxInitPoint = $pref['sandbox_init_point'] ?? null;

        $accessToken = (string) $gatewayAccount->access_token;
        $useSandbox = str_starts_with($accessToken, 'TEST') || config('app.env') !== 'production';
        if ($useSandbox && $sandboxInitPoint) {
            $initPoint = $sandboxInitPoint;
        }
        if (!$initPoint) {
            $initPoint = $sandboxInitPoint;
        }
        if (!$initPoint) {
            return redirect()->route('events.show', $event)->with('error', 'Pagamento indisponível no momento.');
        }

        return redirect()->away($initPoint);
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

    private function resolveEventsGatewayAccount(): ?GatewayAccount
    {
        $account = GatewayAccount::where('provider', 'mercadopago')
            ->where('enabled', true)
            ->whereHas('user', function ($q) {
                $q->whereIn('role', ['admin', 'superadmin'])
                    ->orWhereIn('level', ['superadmin', 'sucesso']);
            })
            ->orderBy('id')
            ->first();

        if ($account && $account->access_token) {
            return $account;
        }

        $accessToken = config('payments.mercadopago.access_token');
        if (!$accessToken) {
            return null;
        }

        return new GatewayAccount([
            'provider' => 'mercadopago',
            'access_token' => $accessToken,
            'public_key' => config('payments.mercadopago.public_key'),
            'enabled' => true,
        ]);
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
