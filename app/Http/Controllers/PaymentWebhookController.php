<?php

/**
 * ============================================================
 * PROPRIEDADE INTELECTUAL E DIREITOS AUTORAIS
 * ============================================================
 *
 * @autor marcelo-brad rj
 * @contato
 * Tel: 21 981325441
 * WhatsApp: 21 98132-5441
 * Email: contato@kdkhost.com.br
 * Telegram: @MARCELO_BRAD
 * Instagram: @marcelobradrj
 *
 * ============================================================
 */

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
use App\Models\PaymentWebhookLog;
use App\Models\SumUpWebhookLog;
use App\Services\CouponService;
use App\Services\AffiliateTrackingService;
use App\Services\EventExhibitorService;
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
            // Anomaly detection: webhook invalido (assinatura nao bate)
            // Spec: advanced-security-performance, Requirement 11.3
            try {
                app(\App\Services\AnomalyDetectorService::class)->recordWebhook(
                    'sumup_' . (string) $request->ip(),
                    false
                );
            } catch (\Throwable $e) { /* swallow */ }

            Log::warning('SumUp webhook: assinatura invalida', ['order_id' => $orderId, 'token' => $token]);
            return response('Unauthorized', 401);
        }

        // Anomaly detection: webhook valido (passou pela validacao de assinatura)
        // Spec: advanced-security-performance, Requirement 11.3
        try {
            app(\App\Services\AnomalyDetectorService::class)->recordWebhook(
                'sumup_' . (string) $request->ip(),
                true
            );
        } catch (\Throwable $e) { /* swallow */ }

        $log->update(['is_valid' => true]);

        // Audit log: webhook SumUp processado (pós-validação)
        try {
            app(\App\Services\AuditLogService::class)->log(
                \App\Services\AuditLogService::ACTION_WEBHOOK,
                null,
                [],
                [],
                ['provider' => 'sumup', 'order_id' => $orderId, 'event_type' => $eventType]
            );
        } catch (\Throwable $e) { /* swallow - never let audit break webhook */ }

        try {
            app(\App\Services\QueueManagerService::class)->dispatch(
                \App\Services\QueueManagerService::QUEUE_WEBHOOKS,
                new \App\Jobs\ProcessPaymentWebhookJob('sumup', $payload, [
                    'order_id' => $orderId,
                    'token' => $token,
                ])
            );
        } catch (\Throwable $e) {
            try {
                app(SumUpWebhookProcessor::class)->process($payload, $token);
            } catch (\Throwable $inner) {
                Log::error('SumUp webhook processing error', [
                    'order_id' => $orderId,
                    'error'    => $inner->getMessage(),
                ]);
            }
        }

        return response('OK', 200);
    }

    /**
     * Webhook Pix da Efi Pro (Gerencianet) com validacao de assinatura HMAC/token
     * e processamento assincrono na fila `webhooks`.
     */
    public function efi(Request $request)
    {
        if ($request->isMethod('GET') && !$request->has('pix')) {
            return response('OK', 200);
        }

        $rawPayload = $request->getContent();
        $signature = (string) ($request->header('x-webhook-signature') ?: $request->header('x-hub-signature-256') ?: '');
        $hmacQuery = (string) ($request->query('hmac', '') ?: $request->query('token', ''));

        $efiService = app(\App\Services\Payment\EfiService::class);
        if ($request->isMethod('POST') && !$efiService->validateWebhookRequest($rawPayload, $signature, $hmacQuery)) {
            try {
                app(\App\Services\AnomalyDetectorService::class)->recordWebhook('efi_' . (string) $request->ip(), false);
            } catch (\Throwable $e) { /* swallow */ }

            Log::channel('security')->warning('Webhook Efi Pro rejeitado por autenticacao invalida', [
                'ip_hash' => hash('sha256', (string) $request->ip()),
            ]);

            return response('Unauthorized', 401);
        }

        try {
            app(\App\Services\AnomalyDetectorService::class)->recordWebhook('efi_' . (string) $request->ip(), true);
        } catch (\Throwable $e) { /* swallow */ }

        $payload = $request->all();
        $firstTxid = (string) data_get($payload, 'pix.0.txid', '');

        try {
            PaymentWebhookLog::create([
                'provider'    => 'efi',
                'external_id' => $firstTxid,
                'signature'   => $signature,
                'status'      => 'pix_received',
                'payload'     => ['pix_count' => is_array($payload['pix'] ?? null) ? count($payload['pix']) : 0, 'txid' => $firstTxid],
                'ip'          => $request->ip(),
            ]);
        } catch (\Throwable $e) {
            Log::warning('Falha ao registrar log webhook Efi Pro: ' . $e->getMessage());
        }

        try {
            app(\App\Services\QueueManagerService::class)->dispatch(
                \App\Services\QueueManagerService::QUEUE_WEBHOOKS,
                new \App\Jobs\ProcessPaymentWebhookJob('efi', $payload, [
                    'txid' => $firstTxid,
                ])
            );
        } catch (\Throwable $e) {
            Log::error('Webhook Efi Pro falhou ao enfileirar: ' . $e->getMessage(), ['txid' => $firstTxid]);
        }

        return response('OK', 200);
    }

    public function mercadopago(Request $request, $seller_id = 'platform')
    {
        // GET sem parâmetros de webhook = acesso direto via navegador
        if ($request->isMethod('GET') && !$request->has('type') && !$request->has('topic') && !$request->has('data')) {
            return response('OK', 200);
        }

        // Validação de segurança: verificar headers obrigatórios do MercadoPago
        $requestId = $request->header('x-request-id');
        $signature = $request->header('x-signature');
        $webhookSecurity = $this->validateMercadoPagoWebhookRequest($request);

        if ($request->isMethod('POST') && !$webhookSecurity['valid'] && $webhookSecurity['reject']) {
            Log::channel('security')->warning('Webhook MP rejeitado por autenticacao invalida', [
                'reason' => $webhookSecurity['reason'],
                'ip_hash' => hash('sha256', (string) $request->ip()),
                'user_agent_hash' => hash('sha256', (string) $request->userAgent()),
                'request_id' => $webhookSecurity['request_id'],
                'data_id' => $webhookSecurity['data_id'],
            ]);

            return response('Unauthorized', 401);
        }

        // Anomaly detection: registra o webhook MP. Considera valido apenas
        // quando os headers de autenticacao do MercadoPago estao presentes.
        // Spec: advanced-security-performance, Requirement 11.3
        try {
            $isValid = (bool) $webhookSecurity['valid'];
            app(\App\Services\AnomalyDetectorService::class)->recordWebhook(
                'mercadopago_' . (string) $request->ip(),
                $isValid
            );
        } catch (\Throwable $e) { /* swallow */ }

        if ($request->isMethod('POST') && !$requestId && !$signature) {
            Log::channel('security')->warning('Webhook MP: requisição sem headers de autenticação', [
                'ip_hash' => hash('sha256', (string) $request->ip()),
                'user_agent_hash' => hash('sha256', (string) $request->userAgent()),
                'payload_keys' => array_keys(array_slice($request->all(), 0, 5)),
            ]);
            // Não abortar — MercadoPago pode enviar sem headers em alguns cenários legados
            // Mas registrar para monitoramento
        }

        // Proteção contra replay attack: verificar se já processamos este request-id
        if ($requestId) {
            $cacheKey = 'mp_webhook_' . md5($requestId);
            if (\Illuminate\Support\Facades\Cache::has($cacheKey)) {
                Log::info('Webhook MP: request-id duplicado ignorado', ['request_id' => $requestId]);
                return response('OK', 200);
            }
            // Marcar como processado por 24h
            \Illuminate\Support\Facades\Cache::put($cacheKey, true, now()->addHours(24));
        }

        $type = $request->input('type') ?? $request->input('topic');

        // Registrar webhook no banco para auditoria
        $paymentIdForLog = $request->input('data.id') ?? $request->input('id');
        try {
            PaymentWebhookLog::create([
                'provider'    => 'mercadopago',
                'external_id' => (string) ($paymentIdForLog ?? ''),
                'request_id'  => $requestId,
                'signature'   => $signature,
                'status'      => $type,
                'payload'     => $this->sanitizedWebhookPayload($request),
                'ip'          => $request->ip(),
            ]);
        } catch (\Throwable $e) {
            Log::warning('Falha ao registrar webhook log MP: ' . $e->getMessage());
        }

        // Audit log: webhook MercadoPago recebido (pós-validação básica)
        try {
            app(\App\Services\AuditLogService::class)->log(
                \App\Services\AuditLogService::ACTION_WEBHOOK,
                null,
                [],
                [],
                ['provider' => 'mercadopago', 'request_id' => $requestId ?? null, 'type' => $type]
            );
        } catch (\Throwable $e) { /* swallow - never let audit break webhook */ }

        if (!in_array($type, ['subscription_preapproval', 'preapproval', 'payment'], true)) {
            return response('OK', 200);
        }

        try {
            app(\App\Services\QueueManagerService::class)->dispatch(
                \App\Services\QueueManagerService::QUEUE_WEBHOOKS,
                new \App\Jobs\ProcessPaymentWebhookJob('mercadopago', $request->all(), [
                    'seller_id' => (string) $seller_id,
                    'data_id' => (string) ($webhookSecurity['data_id'] ?? $paymentIdForLog ?? ''),
                ])
            );

            return response('OK', 200);
        } catch (\Throwable $dispatchError) {
            Log::warning('MP Webhook fallback sincrono apos falha de fila: ' . $dispatchError->getMessage());
        }

        // Se for preapproval (assinatura iniciada/autorizada)
        if ($type === 'subscription_preapproval' || $type === 'preapproval') {
            return $this->handleMPPreapproval($request);
        }

        try {
            $paymentId = $request->input('data.id') ?? $request->input('id');
            if (!$paymentId) {
                Log::warning('MP Webhook: missing payment id', [
                    'payload_keys' => array_keys(array_slice($request->all(), 0, 5)),
                ]);
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

            if (!$orderId) {
                return response('OK', 200);
            }

            $order = app(EventExhibitorService::class)->resolveOrderFromReference((string) $orderId);
            if (!$order) {
                return response('OK', 200);
            }

            if ($status === 'approved') {
                $this->processPaidOrder($order, $paymentId, $data);
            } elseif (in_array($status, ['rejected', 'cancelled', 'cancelled_by_collector', 'cancelled_by_user'], true)) {
                $order->update([
                    'status' => 'failed',
                    'metadata' => array_merge($order->metadata ?? [], ['webhook_data' => $data]),
                ]);
                app(EventExhibitorService::class)->releaseOrder($order, 'failed');
            } elseif (in_array($status, ['refunded', 'charged_back'], true)) {
                $order->update([
                    'status' => 'refunded',
                    'refunded_at' => now(),
                    'metadata' => array_merge($order->metadata ?? [], ['webhook_data' => $data]),
                ]);
                app(EventExhibitorService::class)->markOrderRefunded($order, true);
                app(\App\Services\OrderAccessRevocationService::class)->revoke($order->fresh(['items', 'user']), 'gateway_refunded');
            }

        } catch (\Throwable $e) {
            Log::error('MP Webhook Error: ' . $e->getMessage(), ['seller_id' => $seller_id]);
        }

        return response('OK', 200);
    }

    private function validateMercadoPagoWebhookRequest(Request $request): array
    {
        $requestId = (string) $request->header('x-request-id', '');
        $signature = (string) $request->header('x-signature', '');
        $dataId = $this->mercadoPagoWebhookDataId($request);
        $secret = trim((string) config('payments.mercadopago.webhook_secret', ''));
        $signatureRequired = (bool) config('payments.mercadopago.webhook_signature_required', app()->environment('production'))
            || $secret !== '';
        $allowUnsigned = (bool) config('payments.mercadopago.allow_unsigned_webhooks', !app()->environment('production'));

        $base = [
            'valid' => false,
            'reject' => false,
            'reason' => 'unsigned_legacy_allowed',
            'request_id' => $requestId,
            'data_id' => $dataId,
        ];

        if (!$request->isMethod('POST')) {
            return array_merge($base, ['valid' => true, 'reason' => 'non_post']);
        }

        if ($requestId === '' || $signature === '' || $dataId === '') {
            return array_merge($base, [
                'reason' => 'missing_signature_headers',
                'reject' => $signatureRequired && !$allowUnsigned,
            ]);
        }

        if ($secret === '') {
            return array_merge($base, [
                'reason' => 'missing_webhook_secret',
                'reject' => $signatureRequired && !$allowUnsigned,
            ]);
        }

        $parts = $this->parseMercadoPagoSignature($signature);
        $timestamp = (string) ($parts['ts'] ?? '');
        $receivedSignature = (string) ($parts['v1'] ?? '');

        if ($timestamp === '' || $receivedSignature === '') {
            return array_merge($base, [
                'reason' => 'malformed_signature',
                'reject' => true,
            ]);
        }

        $manifest = "id:{$dataId};request-id:{$requestId};ts:{$timestamp};";
        $expectedSignature = hash_hmac('sha256', $manifest, $secret);

        if (!hash_equals($expectedSignature, $receivedSignature)) {
            return array_merge($base, [
                'reason' => 'invalid_signature',
                'reject' => true,
            ]);
        }

        return array_merge($base, [
            'valid' => true,
            'reason' => 'valid_signature',
        ]);
    }

    private function mercadoPagoWebhookDataId(Request $request): string
    {
        return (string) (
            $request->query->get('data.id')
            ?: $request->query->get('data_id')
            ?: $request->input('data.id')
            ?: $request->input('data_id')
            ?: $request->input('id')
            ?: ''
        );
    }

    private function parseMercadoPagoSignature(string $signature): array
    {
        $parts = [];
        foreach (explode(',', $signature) as $piece) {
            [$key, $value] = array_pad(explode('=', trim($piece), 2), 2, '');
            if ($key !== '') {
                $parts[$key] = $value;
            }
        }

        return $parts;
    }

    private function sanitizedWebhookPayload(Request $request): array
    {
        return [
            'type' => $request->input('type') ?? $request->input('topic'),
            'action' => $request->input('action'),
            'data_id' => $this->mercadoPagoWebhookDataId($request),
            'id' => $request->input('id'),
            'live_mode' => $request->input('live_mode'),
            'api_version' => $request->input('api_version'),
        ];
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

        } else {
            $order->update([
                'transaction_id' => $order->transaction_id ?: (string) $transactionId,
                'metadata' => array_merge($order->metadata ?? [], ['webhook_data' => $data]),
            ]);
        }

        app(\App\Services\OrderSplitService::class)->syncForPaidOrder($order);
        app(CouponService::class)->markOrderRedemptionAsUsed((int) $order->id);
        $this->confirmEventRegistrationsForOrder($order);
        app(EventExhibitorService::class)->confirmPaidOrder($order);
        $this->activatePlanForOrder($order);
        app(AffiliateTrackingService::class)->recordPaidOrder($order);
        $this->fulfillDigitalItemsForOrder($order);
        app(SellerProductFulfillmentService::class)->fulfillPaidOrder($order);
        app(InvoiceService::class)->issueAndQueueForOrder($order);
        app(\App\Services\OrderControlCopyDispatcher::class)->dispatch($order);

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
        app(\App\Services\OrderSplitService::class)->syncForPaidOrder($order);
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
