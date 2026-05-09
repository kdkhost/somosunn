<?php

namespace App\Http\Controllers;

use App\Jobs\SendMarketplaceOrderPaidEmailsJob;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Event;
use App\Models\EventRegistration;
use App\Models\Mentorship;
use App\Models\Order;
use App\Models\Plan;
use App\Models\Setting;
use App\Models\User;
use App\Models\OrderSplit;
use App\Models\SumUpWebhookLog;
use App\Services\CouponService;
use App\Services\AffiliateTrackingService;
use App\Services\InvoiceService;
use App\Services\Marketplace\SellerProductFulfillmentService;
use App\Services\Payment\SumUpService;
use App\Services\Payment\SumUpWebhookProcessor;
use App\Services\PointsService;
use App\Support\EmailQueueSettings;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class PaymentWebhookController extends Controller
{
    /**
     * Webhook dinâmico SumUp — URL única por transação.
     * Rota: POST /webhook/sumup/{orderId}/{token}
     */
    public function sumup(Request $request, int $orderId, string $token)
    {
        $rawPayload = $request->getContent();
        $signature  = $request->header('X-SumUp-Signature', '')
            ?: $request->header('X-Payload-Signature', '');
        $payload    = $request->all();
        $eventType  = $payload['event_type'] ?? $payload['type'] ?? 'unknown';

        // Loga o webhook recebido
        $log = SumUpWebhookLog::create([
            'order_id'   => $orderId,
            'event_type' => $eventType,
            'payload'    => $payload,
            'signature'  => $signature,
            'is_valid'   => false,
        ]);

        // Valida assinatura HMAC
        $secret  = (string) (Setting::get('sumup_webhook_secret') ?: config('payments.sumup.webhook_secret', ''));
        $sumUpSvc = app(SumUpService::class);

        if ($secret && !$sumUpSvc->validateWebhookSignature($rawPayload, $signature, $secret)) {
            Log::warning('SumUp webhook: assinatura invalida', ['order_id' => $orderId, 'token' => $token]);
            return response('Unauthorized', 401);
        }

        $log->update(['is_valid' => true]);

        try {
            app(SumUpWebhookProcessor::class)->process($payload, $token);
        } catch (\Throwable $e) {
            Log::error('SumUp webhook processing error', [
                'order_id' => $orderId,
                'error'    => $e->getMessage(),
            ]);
        }

        return response('OK', 200);
    }

    public function mercadopago(Request $request, $seller_id = 'platform')
    {
        // GET sem parâmetros de webhook = acesso direto via navegador
        if ($request->isMethod('GET') && !$request->has('type') && !$request->has('topic') && !$request->has('data')) {
            return response('OK', 200);
        }

        $type = $request->input('type') ?? $request->input('topic');

        // Se for preapproval (assinatura iniciada/autorizada)
        if ($type === 'subscription_preapproval' || $type === 'preapproval') {
            return $this->handleMPPreapproval($request);
        }

        if ($type !== 'payment') {
            return response('OK', 200);
        }

        try {
            $paymentId = $request->input('data.id') ?? $request->input('id');
            if (!$paymentId) {
                Log::warning('MP Webhook: missing payment id', ['payload' => $request->all()]);
                return response('OK', 200);
            }

            $token = (string) config('payments.mercadopago.access_token');

            if (!$token) {
                // Fallback de segurança se o config não estiver populado (o AppServiceProvider deveria cuidar disso)
                $env = Setting::get('mercadopago_env', 'sandbox');
                $prefix = $env === 'production' ? 'mercadopago_prod_' : 'mercadopago_sandbox_';
                $token = Setting::get($prefix . 'access_token', Setting::get('mercadopago_access_token'));
            }

            if (!$token) {
                Log::warning('MP Webhook: missing token', ['seller_id' => (string) $seller_id]);
                return response('OK', 200);
            }

            $response = Http::withToken($token)->get('https://api.mercadopago.com/v1/payments/' . $paymentId);
            if (!$response->successful()) {
                Log::warning('MP Webhook: failed to fetch payment', [
                    'payment_id' => $paymentId,
                    'status' => $response->status(),
                    'error' => $response->body()
                ]);
                return response('OK', 200);
            }

            $data = $response->json();
            $orderId = $data['external_reference'] ?? null;
            $status = (string) ($data['status'] ?? '');

            if (!$orderId || $status !== 'approved') {
                return response('OK', 200);
            }

            $order = Order::find($orderId);
            if (!$order) {
                return response('OK', 200);
            }

            $this->processPaidOrder($order, $paymentId, $data);

        } catch (\Throwable $e) {
            Log::error('MP Webhook Error: ' . $e->getMessage(), ['seller_id' => $seller_id]);
        }

        return response('OK', 200);
    }

    private function handleMPPreapproval(Request $request)
    {
        try {
            $preapprovalId = $request->input('data.id') ?? $request->input('id');
            if (!$preapprovalId)
                return response('OK', 200);

            $mpService = app(\App\Services\Payment\MercadoPagoService::class);
            $data = $mpService->getPreapproval($preapprovalId);

            $orderId = $data['external_reference'] ?? null;
            $status = (string) ($data['status'] ?? '');

            if (!$orderId || !in_array($status, ['authorized', 'active'])) {
                return response('OK', 200);
            }

            $order = Order::find($orderId);
            if (!$order)
                return response('OK', 200);

            $this->processPaidOrder($order, $preapprovalId, $data);

        } catch (\Throwable $e) {
            Log::error('MP Preapproval Webhook Error: ' . $e->getMessage());
        }

        return response('OK', 200);
    }

    public function processPaidOrder(Order $order, $transactionId, $data)
    {
        $wasPaid = (string) $order->status === 'paid';

        if (!$wasPaid) {
            $order->update([
                'status' => 'paid',
                'paid_at' => now(),
                'transaction_id' => (string) $transactionId,
                'metadata' => array_merge($order->metadata ?? [], ['webhook_data' => $data]),
            ]);
            Log::info("Order #{$order->id} marked as PAID via Webhook");

            // Calcular Split de Pagamento
            $this->calculateAndSaveSplits($order);
        } else {
            $order->update([
                'transaction_id' => $order->transaction_id ?: (string) $transactionId,
                'metadata' => array_merge($order->metadata ?? [], ['webhook_data' => $data]),
            ]);
        }

        app(CouponService::class)->markOrderRedemptionAsUsed((int) $order->id);
        $this->confirmEventRegistrationsForOrder($order);
        $this->activatePlanForOrder($order);
        app(AffiliateTrackingService::class)->recordPaidOrder($order);
        $this->fulfillDigitalItemsForOrder($order);
        app(SellerProductFulfillmentService::class)->fulfillPaidOrder($order);
        app(InvoiceService::class)->issueAndQueueForOrder($order);

        if (!$wasPaid || !data_get($order->metadata, 'emails.marketplace_paid_sent_at')) {
            EmailQueueSettings::dispatch(new SendMarketplaceOrderPaidEmailsJob((int) $order->id));

            // Notificar Vendedor (se houver seller_id e não for a plataforma)
            if ($order->seller_id && $order->seller_id !== 'platform') {
                $seller = \App\Models\User::find($order->seller_id);
                if ($seller) {
                    $seller->notify(new \App\Notifications\AppNotification([
                        'message' => 'Parabéns! Você realizou uma nova venda no valor de R$ ' . number_format((float) $order->total_amount, 2, ',', '.') . '.',
                        'type' => 'SaleConfirmed',
                        'action_url' => route('panel.marketplace.sales'),
                        'action_label' => 'Ver vendas'
                    ]));
                }
            }

            // Notificar Comprador
            if ($order->user) {
                $order->user->notify(new \App\Notifications\AppNotification([
                    'message' => 'Seu pagamento foi confirmado! O acesso aos seus itens foi liberado.',
                    'type' => 'PaymentConfirmed',
                    'action_url' => route('panel.dashboard'),
                    'action_label' => 'Acessar agora'
                ]));
            }
        }
    }

    private function calculateAndSaveSplits(Order $order)
    {
        $total = (float) $order->total_amount;
        if ($total <= 0)
            return;

        // Limpar splits existentes se houver
        OrderSplit::where('order_id', $order->id)->delete();

        // Pegar porcentagens das configurações
        $sellerPercent = (float) Setting::get('marketplace_split_seller_percent', 70);
        $platformPercent = (float) Setting::get('marketplace_split_platform_percent', 10);
        $trafficPercent = (float) Setting::get('marketplace_split_traffic_percent', 10);
        $superadminPercent = (float) Setting::get('marketplace_split_superadmin_percent', 10);

        // Achar SuperAdmin (para a chave PIX)
        $superadmin = User::where('role', 'superadmin')
            ->orWhere('level', 'superadmin')
            ->first();

        $splits = [
            [
                'type' => 'seller',
                'percent' => $sellerPercent,
                'user_id' => ($order->seller_id && $order->seller_id !== 'platform') ? $order->seller_id : null,
            ],
            [
                'type' => 'platform',
                'percent' => $platformPercent,
                'user_id' => null,
            ],
            [
                'type' => 'traffic',
                'percent' => $trafficPercent,
                'user_id' => null,
            ],
            [
                'type' => 'superadmin',
                'percent' => $superadminPercent,
                'user_id' => $superadmin ? $superadmin->id : null,
            ],
        ];

        foreach ($splits as $split) {
            $percent = (float) $split['percent'];
            if ($percent <= 0) {
                continue;
            }

            $amount = round(($total * $percent) / 100, 2);
            $pixKey = null;

            if ($split['type'] === 'seller' && $order->seller) {
                $pixKey = $order->seller->pix_key;
            } elseif ($split['type'] === 'superadmin' && $superadmin) {
                $pixKey = $superadmin->pix_key;
            } elseif ($split['type'] === 'platform') {
                $pixKey = Setting::get('marketplace_split_platform_pix');
            } elseif ($split['type'] === 'traffic') {
                $pixKey = Setting::get('marketplace_split_traffic_pix');
            }

            \App\Models\OrderSplit::create([
                'order_id' => $order->id,
                'receiver_id' => $split['user_id'],
                'receiver_type' => $split['type'],
                'percentage' => $percent,
                'amount' => $amount,
                'pix_key' => $pixKey,
                'status' => 'pending',
            ]);
        }
    }

    private function confirmEventRegistrationsForOrder(Order $order): void
    {
        $registrations = EventRegistration::where('order_id', $order->id)->get();
        if ($registrations->isEmpty()) {
            return;
        }

        DB::transaction(function () use ($registrations, $order) {
            $needsManualRefund = false;

            foreach ($registrations as $reg) {
                if (in_array($reg->status, EventRegistration::COUNTED_STATUSES, true)) {
                    continue;
                }
                if ($reg->status === EventRegistration::STATUS_CANCELLED) {
                    continue;
                }

                $event = Event::whereKey($reg->event_id)->lockForUpdate()->first();
                if (!$event) {
                    continue;
                }

                if ($event->capacity && !$event->hasCapacityFor((int) $reg->quantity)) {
                    $reg->update(['status' => EventRegistration::STATUS_CANCELLED]);
                    $needsManualRefund = true;
                    continue;
                }

                $reg->update(['status' => EventRegistration::STATUS_PAID]);

                // Gamificação: participação em evento pago confirmada
                try {
                    $regUser = User::find($reg->user_id);
                    if ($regUser) {
                        (new PointsService())->award($regUser, 'attend_event', ['event_id' => $reg->event_id]);
                    }
                } catch (\Throwable $e) {
                    Log::warning('Falha ao pontuar attend_event (pago): ' . $e->getMessage());
                }
            }

            if ($needsManualRefund) {
                $order->update([
                    'metadata' => array_merge($order->metadata ?? [], [
                        'event_overbooked' => true,
                        'needs_manual_refund' => true,
                    ]),
                ]);
            }
        });
    }

    private function fulfillDigitalItemsForOrder(Order $order): void
    {
        $order->loadMissing('items');

        foreach ($order->items as $item) {
            if ($item->item_type === 'course') {
                $course = Course::find($item->item_id);
                if (!$course) {
                    continue;
                }

                Enrollment::firstOrCreate([
                    'user_id' => $order->user_id,
                    'enrollable_id' => $course->id,
                    'enrollable_type' => Course::class,
                ], [
                    'status' => 'active',
                    'started_at' => now(),
                    'progress' => [],
                ]);
            }

            if ($item->item_type === 'mentorship') {
                $mentorship = Mentorship::find($item->item_id);
                if (!$mentorship) {
                    continue;
                }

                $mentorshipEnrollment = Enrollment::firstOrCreate([
                    'user_id' => $order->user_id,
                    'enrollable_id' => $mentorship->id,
                    'enrollable_type' => Mentorship::class,
                ], [
                    'status' => 'active',
                    'started_at' => now(),
                    'progress' => [],
                ]);

                // Gamificação: inscrição em mentoria confirmada (somente na criação)
                if ($mentorshipEnrollment->wasRecentlyCreated) {
                    try {
                        $enrolledUser = User::find($order->user_id);
                        if ($enrolledUser) {
                            (new PointsService())->award($enrolledUser, 'attend_mentorship', ['mentorship_id' => $mentorship->id]);
                        }
                    } catch (\Throwable $e) {
                        Log::warning('Falha ao pontuar attend_mentorship: ' . $e->getMessage());
                    }
                }
            }
        }
    }

    private function activatePlanForOrder(Order $order): void
    {
        $item = $order->items()->where('item_type', 'plan')->first();
        if (!$item) {
            return;
        }

        $plan = Plan::find($item->item_id);
        if (!$plan) {
            return;
        }

        $user = $order->user;
        if (!$user) {
            return;
        }

        $user->update([
            'plan_id' => $plan->id,
            'plan_expires_at' => $this->planExpiresAt($plan, $this->resolveOrderPlanPeriod($order, $item)),
        ]);

        // Gamificação: pontos de referral para o indicador
        // Concedidos apenas quando o comprador paga um plano pago (não gratuito)
        // e existe um referidor vinculado. Deduplicação por order_id no meta.
        $this->awardReferralPointsForPlanOrder($plan, $user, $order);
    }

    private function awardReferralPointsForPlanOrder(Plan $plan, User $user, Order $order): void
    {
        // Apenas para planos pagos
        if ($plan->is_free || (float) $plan->price <= 0) {
            return;
        }

        // O comprador precisa ter sido indicado
        if (!$user->referred_by) {
            return;
        }

        $referrer = User::find($user->referred_by);
        if (!$referrer) {
            return;
        }

        try {
            // Busca a regra de pontos 'referral' ativa
            $rule = \App\Models\PointsRule::where('key', 'referral')->where('active', true)->first();
            if (!$rule || $rule->points <= 0) {
                return;
            }

            // Deduplicação própria por order_id: nunca creditar o mesmo pedido duas vezes.
            // NÃO usamos PointsService::award() para evitar o bloqueio da guarda
            // repeatable=false, pois um mesmo indicador pode indicar múltiplas pessoas.
            $alreadyAwarded = \App\Models\PointsLog::where('user_id', $referrer->id)
                ->where('action_key', 'referral')
                ->where('meta', 'LIKE', '%"order_id":' . $order->id . '%')
                ->exists();

            if ($alreadyAwarded) {
                return;
            }

            \App\Models\PointsLog::create([
                'user_id'    => $referrer->id,
                'action_key' => 'referral',
                'points'     => $rule->points,
                'meta'       => json_encode([
                    'new_user_id'   => $user->id,
                    'new_user_name' => $user->name,
                    'order_id'      => $order->id,
                    'plan_id'       => $plan->id,
                    'plan_name'     => $plan->name,
                ]),
            ]);

            $referrer->increment('points', $rule->points);

            Log::info('Referral points awarded after plan payment.', [
                'referrer_id' => $referrer->id,
                'buyer_id'    => $user->id,
                'order_id'    => $order->id,
                'plan'        => $plan->name,
                'points'      => $rule->points,
            ]);

            // Notifica o indicador em tempo real
            $referrer->notify(new \App\Notifications\AppNotification([
                'message'      => 'Seu indicado ' . $user->name . ' assinou um plano! Você ganhou +' . $rule->points . ' pontos de indicação.',
                'type'         => 'ReferralRewarded',
                'action_url'   => route('panel.referral.index'),
                'action_label' => 'Ver indicações',
            ]));

        } catch (\Throwable $e) {
            Log::warning('Falha ao pontuar referral por pagamento de plano: ' . $e->getMessage(), [
                'order_id'    => $order->id,
                'referrer_id' => $referrer->id ?? null,
            ]);
        }
    }

    private function planExpiresAt(Plan $plan, ?string $period = null): ?\Carbon\Carbon
    {
        $period = trim((string) ($period ?: $plan->period ?: ''));
        $periodLower = Str::lower($period);

        if ($periodLower === 'vitalício' || $periodLower === 'vitalicio') {
            return null;
        }

        if (ctype_digit($period)) {
            return now()->addDays((int) $period);
        }

        return match ($periodLower) {
            'mensal' => now()->addMonth(),
            'trimestral' => now()->addMonths(3),
            'semestral' => now()->addMonths(6),
            'anual' => now()->addYear(),
            default => now()->addMonth(),
        };
    }

    private function resolveOrderPlanPeriod(Order $order, mixed $item): string
    {
        $itemPeriod = trim((string) data_get($item, 'data.period', ''));
        if ($itemPeriod !== '') {
            return Plan::sanitizePeriod($itemPeriod);
        }

        $metadataPeriod = trim((string) data_get($order->metadata, 'period', ''));
        if ($metadataPeriod !== '') {
            return Plan::sanitizePeriod($metadataPeriod);
        }

        return Plan::sanitizePeriod((string) ($order->period ?? ''));
    }
}
