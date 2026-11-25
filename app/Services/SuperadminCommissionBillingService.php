<?php

namespace App\Services;

use App\Models\OrderSplit;
use App\Models\OrderSplitPayout;
use App\Models\Setting;
use App\Models\User;
use App\Notifications\AppNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class SuperadminCommissionBillingService
{
    private const MP_BASE_URL = 'https://api.mercadopago.com';

    /**
     * Retorna o Access Token da conta Mercado Pago do Superadmin (onde as comissões são recebidas).
     */
    public function accessToken(): string
    {
        $dedicated = trim((string) Setting::get('superadmin_commission_mp_access_token', ''));
        if ($dedicated !== '') {
            return $dedicated;
        }

        return trim((string) (Setting::get('mp_access_token') ?: config('payments.mercadopago.access_token', '')));
    }

    /**
     * Dispara a cobrança automática da comissão na conta Mercado Pago do Superadmin a cada venda elegível:
     * 1) Se houver Cartão Corporativo do Admin salvo (Customer ID + Card ID/Token), cobra automaticamente no cartão sem interação humana.
     * 2) Caso contrário (ou se o cartão falhar), gera cobrança Pix imediata na conta Mercado Pago do Superadmin e envia ao administrador.
     */
    public function chargeSplit(OrderSplit $split): OrderSplitPayout
    {
        return DB::transaction(function () use ($split) {
            $lockedSplit = OrderSplit::query()
                ->with(['payout', 'order.seller', 'receiver'])
                ->lockForUpdate()
                ->findOrFail($split->id);

            $payout = app(OrderSplitPayoutService::class)->syncForSplit($lockedSplit);

            if ((string) $payout->status === 'paid' || (string) $lockedSplit->status === 'paid') {
                return $payout->fresh();
            }

            $amount = round((float) $lockedSplit->amount, 2);
            $minAmount = max(0.01, (float) Setting::get('superadmin_commission_min_payout', '1.00'));
            if ($amount < $minAmount) {
                $payout->forceFill([
                    'provider' => 'mp_auto_billing',
                    'notes' => 'Comissão abaixo do valor mínimo configurado (R$ ' . number_format($minAmount, 2, ',', '.') . ').',
                ])->save();

                return $payout->fresh();
            }

            $token = $this->accessToken();
            if ($token === '') {
                throw new \RuntimeException('Access Token da conta Mercado Pago do Superadmin não configurado.');
            }

            $adminPayer = $this->resolveAdminPayer();
            $payerEmail = trim((string) (Setting::get('superadmin_commission_admin_email', '') ?: $adminPayer?->email ?: 'financeiro@somosunn.com.br'));

            $customerId = trim((string) Setting::get('superadmin_commission_mp_customer_id', ''));
            $cardToken = trim((string) Setting::get('superadmin_commission_mp_card_token', ''));
            $paymentMethodId = trim((string) Setting::get('superadmin_commission_mp_payment_method_id', ''));

            $payout->forceFill([
                'provider' => 'mp_auto_billing',
                'status' => 'processing',
                'attempts' => (int) $payout->attempts + 1,
                'last_attempt_at' => now(),
            ])->save();

            // Tenta cobrança automática direta no cartão corporativo salvo (100% sem interação humana)
            if ($customerId !== '' && $cardToken !== '') {
                $cardResult = $this->attemptCardCharge($lockedSplit, $payout, $token, $amount, $payerEmail, $customerId, $cardToken, $paymentMethodId);
                if ($cardResult !== null) {
                    return $cardResult;
                }
            }

            // Fallback automático (ou modo Pix automático a cada venda): gera cobrança Pix no Mercado Pago do Superadmin
            return $this->createPixCharge($lockedSplit, $payout, $token, $amount, $payerEmail, $adminPayer);
        });
    }

    /**
     * Concilia automaticamente o pagamento de comissão quando o webhook do Mercado Pago confirma aprovação.
     */
    public function reconcileMercadoPagoWebhook(string $paymentId, string $status, array $paymentData = []): bool
    {
        $externalRef = (string) ($paymentData['external_reference'] ?? '');
        if (!str_starts_with($externalRef, 'COMMISSION_SPLIT_')) {
            return false;
        }

        $splitId = (int) str_replace('COMMISSION_SPLIT_', '', $externalRef);
        if ($splitId <= 0) {
            return false;
        }

        return DB::transaction(function () use ($splitId, $paymentId, $status, $paymentData): bool {
            $lockedSplit = OrderSplit::query()
                ->with(['payout', 'receiver'])
                ->lockForUpdate()
                ->find($splitId);

            if (!$lockedSplit) {
                return false;
            }

            $payout = app(OrderSplitPayoutService::class)->syncForSplit($lockedSplit);

            if ($status === 'approved') {
                $payload = [
                    'provider' => 'mp_auto_billing',
                    'status' => 'paid',
                    'external_id' => $paymentId,
                    'last_error' => null,
                    'processed_at' => now(),
                    'notes' => 'Comissão liquidada automaticamente na conta Mercado Pago do Superadmin (Pagamento #' . $paymentId . ').',
                ];

                if (Schema::hasColumn('order_split_payouts', 'gateway_response')) {
                    $payload['gateway_response'] = $paymentData;
                }

                $payout->forceFill($payload)->save();
                $lockedSplit->forceFill(['status' => 'paid'])->save();

                if ($lockedSplit->receiver) {
                    $lockedSplit->receiver->notify(new AppNotification([
                        'message' => sprintf(
                            'Comissão de R$ %s (Pedido #%d) creditada automaticamente no seu Mercado Pago.',
                            number_format((float) $lockedSplit->amount, 2, ',', '.'),
                            (int) $lockedSplit->order_id
                        ),
                        'type' => 'CommissionPaid',
                        'action_url' => route('panel.admin.splits.index'),
                        'action_label' => 'Ver repasses',
                    ]));
                }

                return true;
            }

            if (in_array($status, ['rejected', 'cancelled', 'expired'], true) && (string) $payout->status !== 'paid') {
                $failPayload = [
                    'provider' => 'mp_auto_billing',
                    'status' => 'failed',
                    'external_id' => $paymentId,
                    'last_error' => 'Cobrança Mercado Pago encerrada com status: ' . $status,
                ];

                if (Schema::hasColumn('order_split_payouts', 'gateway_response')) {
                    $failPayload['gateway_response'] = $paymentData;
                }

                $payout->forceFill($failPayload)->save();

                return true;
            }

            return true;
        });
    }

    private function attemptCardCharge(
        OrderSplit $split,
        OrderSplitPayout $payout,
        string $token,
        float $amount,
        string $payerEmail,
        string $customerId,
        string $cardToken,
        string $paymentMethodId
    ): ?OrderSplitPayout {
        $payload = [
            'transaction_amount' => $amount,
            'token' => $cardToken,
            'description' => sprintf('Comissao SOMOS UNN - Pedido #%d', (int) $split->order_id),
            'installments' => 1,
            'external_reference' => 'COMMISSION_SPLIT_' . $split->id,
            'payer' => [
                'type' => 'customer',
                'id' => $customerId,
                'email' => $payerEmail,
            ],
            'notification_url' => $this->webhookUrl(),
        ];

        if ($paymentMethodId !== '') {
            $payload['payment_method_id'] = $paymentMethodId;
        }

        $response = Http::withToken($token)
            ->withHeaders([
                'X-Idempotency-Key' => 'UNN-COMM-CARD-' . $payout->id . '-' . $payout->attempts,
            ])
            ->timeout(20)
            ->acceptJson()
            ->post(self::MP_BASE_URL . '/v1/payments', $payload);

        if (!$response->successful()) {
            Log::warning('SuperadminCommissionBillingService: cobrança no cartão corporativo falhou, acionando fallback Pix', [
                'split_id' => $split->id,
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return null;
        }

        $json = (array) $response->json();
        $mpStatus = (string) ($json['status'] ?? '');
        $paymentId = (string) ($json['id'] ?? '');

        if ($mpStatus === 'approved') {
            $paidPayload = [
                'provider' => 'mp_auto_billing',
                'status' => 'paid',
                'external_id' => $paymentId,
                'last_error' => null,
                'processed_at' => now(),
                'notes' => 'Comissão debitada automaticamente no cartão corporativo do administrador (MP #' . $paymentId . ').',
            ];

            if (Schema::hasColumn('order_split_payouts', 'gateway_response')) {
                $paidPayload['gateway_response'] = $json;
            }

            $payout->forceFill($paidPayload)->save();
            $split->forceFill(['status' => 'paid'])->save();

            return $payout->fresh();
        }

        Log::warning('SuperadminCommissionBillingService: cartão não aprovado imediatamente, acionando fallback Pix', [
            'split_id' => $split->id,
            'mp_status' => $mpStatus,
            'status_detail' => $json['status_detail'] ?? null,
        ]);

        return null;
    }

    private function createPixCharge(
        OrderSplit $split,
        OrderSplitPayout $payout,
        string $token,
        float $amount,
        string $payerEmail,
        ?User $adminPayer
    ): OrderSplitPayout {
        // Se já gerou Pix pendente para este payout, evita duplicar cobrança
        $existingQr = data_get($payout->gateway_response, 'point_of_interaction.transaction_data.qr_code');
        if (!empty($payout->external_id) && !empty($existingQr) && (string) $payout->status === 'pending') {
            return $payout->fresh();
        }

        $payerName = trim((string) ($adminPayer?->name ?: 'Administrador SOMOS UNN'));
        $parts = preg_split('/\s+/', $payerName) ?: ['Administrador'];
        $firstName = $parts[0] ?? 'Administrador';
        $lastName = count($parts) > 1 ? implode(' ', array_slice($parts, 1)) : 'UNN';

        $payload = [
            'transaction_amount' => $amount,
            'description' => sprintf('Comissao SOMOS UNN - Pedido #%d', (int) $split->order_id),
            'payment_method_id' => 'pix',
            'external_reference' => 'COMMISSION_SPLIT_' . $split->id,
            'payer' => [
                'email' => $payerEmail,
                'first_name' => $firstName,
                'last_name' => $lastName,
            ],
            'notification_url' => $this->webhookUrl(),
        ];

        $response = Http::withToken($token)
            ->withHeaders([
                'X-Idempotency-Key' => 'UNN-COMM-PIX-' . $payout->id . '-' . $payout->attempts,
            ])
            ->timeout(20)
            ->acceptJson()
            ->post(self::MP_BASE_URL . '/v1/payments', $payload);

        if (!$response->successful()) {
            $err = $response->json('message') ?: ('HTTP ' . $response->status());
            Log::error('SuperadminCommissionBillingService: falha ao gerar cobrança Pix no Mercado Pago', [
                'split_id' => $split->id,
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            $payout->forceFill([
                'provider' => 'mp_auto_billing',
                'status' => 'failed',
                'last_error' => 'Mercado Pago: ' . $err,
                'notes' => 'Falha ao gerar cobrança automática no Mercado Pago.',
            ])->save();

            throw new \RuntimeException('Falha ao gerar cobrança automática no Mercado Pago: ' . $err);
        }

        $json = (array) $response->json();
        $paymentId = (string) ($json['id'] ?? '');
        $qrCode = (string) data_get($json, 'point_of_interaction.transaction_data.qr_code', '');
        $ticketUrl = (string) data_get($json, 'point_of_interaction.transaction_data.ticket_url', '');

        $pendingPayload = [
            'provider' => 'mp_auto_billing',
            'status' => 'pending',
            'external_id' => $paymentId,
            'last_error' => null,
            'notes' => 'Cobrança Pix gerada automaticamente no Mercado Pago (#' . $paymentId . '). Aguardando quitação automática.',
        ];

        if (Schema::hasColumn('order_split_payouts', 'gateway_response')) {
            $pendingPayload['gateway_response'] = $json;
        }

        $payout->forceFill($pendingPayload)->save();

        if ($adminPayer && ($qrCode !== '' || $ticketUrl !== '')) {
            $adminPayer->notify(new AppNotification([
                'message' => sprintf(
                    'Cobrança automática de comissão (Pedido #%d) no valor de R$ %s gerada no Mercado Pago.',
                    (int) $split->order_id,
                    number_format($amount, 2, ',', '.')
                ),
                'type' => 'CommissionBilling',
                'action_url' => $ticketUrl !== '' ? $ticketUrl : route('panel.admin.splits.index'),
                'action_label' => 'Pagar Pix da Comissão',
            ]));
        }

        return $payout->fresh();
    }

    private function resolveAdminPayer(): ?User
    {
        foreach (['platform_owner_id', 'platform_admin_user_id'] as $key) {
            $id = (int) Setting::get($key, 0);
            if ($id > 0) {
                $user = User::find($id);
                if ($user) {
                    return $user;
                }
            }
        }

        return User::query()->where('role', 'admin')->oldest('id')->first();
    }

    private function webhookUrl(): string
    {
        try {
            return route('webhook.mercadopago');
        } catch (\Throwable) {
            return rtrim((string) config('app.url'), '/') . '/webhook/mercadopago';
        }
    }
}
