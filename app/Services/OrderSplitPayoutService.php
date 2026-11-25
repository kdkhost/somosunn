<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderSplit;
use App\Models\OrderSplitPayout;
use App\Models\Setting;
use App\Notifications\AppNotification;
use App\Services\Payment\EfiService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class OrderSplitPayoutService
{
    public function syncForOrder(Order $order): void
    {
        $order->loadMissing('splits.payout');

        foreach ($order->splits as $split) {
            $this->syncForSplit($split);
        }
    }

    public function syncForSplit(OrderSplit $split): OrderSplitPayout
    {
        $split->loadMissing(['order', 'payout']);

        $existing = $split->payout;
        $isInternallySettled = (string) $split->status === 'paid';

        $provider = $this->resolveProvider($split, $existing, $isInternallySettled);
        $status = $this->resolveStatus($existing, $isInternallySettled);

        $payload = [
            'provider' => $provider,
            'status' => $status,
            'amount' => (float) $split->amount,
            'pix_key' => $split->pix_key,
            'processed_at' => $status === 'paid' ? ($existing?->processed_at ?? now()) : null,
            'notes' => $this->resolveNotes($provider, $status, $existing),
        ];

        if (Schema::hasColumn('order_split_payouts', 'scheduled_for')) {
            if ($status === 'pending' && $provider === 'efi_pix_out') {
                $delayDays = max(0, (int) Setting::get('superadmin_commission_payout_delay_days', '1'));
                $baseDate = $split->order?->paid_at ?? now();
                $payload['scheduled_for'] = $existing?->scheduled_for ?? $baseDate->copy()->addDays($delayDays);
            } elseif ($status === 'paid') {
                $payload['scheduled_for'] = $existing?->scheduled_for;
            }
        }

        return OrderSplitPayout::updateOrCreate(
            ['order_split_id' => $split->id],
            $payload
        );
    }

    /**
     * Executa o repasse/cobrança automática conforme o modo configurado pelo Superadmin:
     * - mp_auto_billing (Opção C): cobrança automática a cada venda no Mercado Pago (cartão corporativo salvo ou Pix automático).
     * - efi_pix_out (Opção B): Pix Out automático via Efí Pay para a chave Pix do Mercado Pago.
     */
    public function processAutomaticPayout(OrderSplit $split, bool $ignoreSchedule = false): OrderSplitPayout
    {
        $mode = trim((string) Setting::get('superadmin_commission_payout_mode', 'mp_auto_billing'));
        if ($mode === 'mp_auto_billing' && (string) $split->receiver_type === 'superadmin') {
            return app(SuperadminCommissionBillingService::class)->chargeSplit($split);
        }

        $efi = app(EfiService::class);

        return DB::transaction(function () use ($split, $ignoreSchedule, $efi) {
            $lockedSplit = OrderSplit::query()
                ->with(['payout', 'order', 'receiver'])
                ->lockForUpdate()
                ->findOrFail($split->id);

            $payout = $this->syncForSplit($lockedSplit);

            if ((string) $payout->status === 'paid' || (string) $lockedSplit->status === 'paid') {
                return $payout->fresh();
            }

            $targetPixKey = trim((string) ($lockedSplit->pix_key ?: Setting::get('superadmin_commission_pix_key', '')));
            if ($targetPixKey === '') {
                throw new \RuntimeException('Cadastre a chave Pix do Mercado Pago do recebedor antes de processar o repasse automático.');
            }

            $minAmount = max(0.01, (float) Setting::get('superadmin_commission_min_payout', '1.00'));
            if ((float) $lockedSplit->amount < $minAmount) {
                $payout->forceFill([
                    'notes' => 'Valor abaixo do mínimo configurado para disparo automático (R$ ' . number_format($minAmount, 2, ',', '.') . ').',
                ])->save();

                return $payout->fresh();
            }

            if (!$ignoreSchedule && Schema::hasColumn('order_split_payouts', 'scheduled_for') && $payout->scheduled_for && $payout->scheduled_for->isFuture()) {
                return $payout->fresh();
            }

            if (!$efi->isEnabled()) {
                throw new \RuntimeException('Integração Efí Pay não está ativa nas configurações de pagamento.');
            }

            // Verifica saldo disponível na conta Efí (evita marcar como falha definitiva se a SumUp ainda não depositou)
            $balance = $efi->getAccountBalance();
            if ($balance !== null && $balance < (float) $lockedSplit->amount) {
                $update = [
                    'provider' => 'efi_pix_out',
                    'status' => 'pending',
                    'last_attempt_at' => now(),
                    'notes' => sprintf(
                        'Aguardando saldo na conta Efí (disponível: R$ %s | necessário: R$ %s). Nova tentativa automática agendada.',
                        number_format($balance, 2, ',', '.'),
                        number_format((float) $lockedSplit->amount, 2, ',', '.')
                    ),
                ];

                if (Schema::hasColumn('order_split_payouts', 'scheduled_for')) {
                    $update['scheduled_for'] = now()->addHour();
                }

                $payout->forceFill($update)->save();

                return $payout->fresh();
            }

            $idEnvio = 'UNNSPLIT' . $payout->id;
            $description = sprintf(
                'Comissao SOMOS UNN Pedido #%d',
                (int) $lockedSplit->order_id
            );

            $payout->forceFill([
                'provider' => 'efi_pix_out',
                'status' => 'processing',
                'pix_key' => $targetPixKey,
                'attempts' => (int) $payout->attempts + 1,
                'last_attempt_at' => now(),
            ])->save();

            try {
                $result = $efi->sendPix($idEnvio, (float) $lockedSplit->amount, $targetPixKey, $description);

                $paidPayload = [
                    'provider' => 'efi_pix_out',
                    'status' => 'paid',
                    'external_id' => $result['e2eId'] ?: $result['idEnvio'],
                    'last_error' => null,
                    'processed_at' => now(),
                    'notes' => 'Pix automático enviado via Efí Pay para chave Pix de destino (' . $targetPixKey . ').',
                ];

                if (Schema::hasColumn('order_split_payouts', 'gateway_response')) {
                    $paidPayload['gateway_response'] = $result['raw'];
                }

                $payout->forceFill($paidPayload)->save();
                $lockedSplit->forceFill(['status' => 'paid', 'pix_key' => $targetPixKey])->save();

                if ($lockedSplit->receiver) {
                    $lockedSplit->receiver->notify(new AppNotification([
                        'message' => sprintf(
                            'Comissão automática de R$ %s (Pedido #%d) enviada via Pix para sua conta Mercado Pago.',
                            number_format((float) $lockedSplit->amount, 2, ',', '.'),
                            (int) $lockedSplit->order_id
                        ),
                        'type' => 'CommissionPaid',
                        'action_url' => route('panel.admin.splits.index'),
                        'action_label' => 'Ver repasses',
                    ]));
                }

                return $payout->fresh();
            } catch (\Throwable $e) {
                Log::error('OrderSplitPayoutService: falha no repasse automático', [
                    'split_id' => $lockedSplit->id,
                    'order_id' => $lockedSplit->order_id,
                    'error' => $e->getMessage(),
                ]);

                $failPayload = [
                    'provider' => 'efi_pix_out',
                    'status' => 'failed',
                    'last_error' => $e->getMessage(),
                    'notes' => 'Falha ao disparar Pix automático via Efí Pay.',
                ];

                if (Schema::hasColumn('order_split_payouts', 'scheduled_for')) {
                    $failPayload['scheduled_for'] = now()->addHours(2);
                }

                $payout->forceFill($failPayload)->save();

                throw new \RuntimeException($e->getMessage(), 0, $e);
            }
        });
    }

    /**
     * Processa todos os repasses automáticos de comissão do superadmin que já atingiram a janela de pagamento.
     *
     * @return array{processed:int,paid:int,waiting_balance:int,failed:int,skipped:bool}
     */
    public function processDueAutomaticPayouts(bool $ignoreSchedule = false): array
    {
        $summary = [
            'processed' => 0,
            'paid' => 0,
            'waiting_balance' => 0,
            'failed' => 0,
            'skipped' => false,
        ];

        $autoEnabled = (string) Setting::get('superadmin_commission_auto_enabled', '0') === '1';
        $mode = trim((string) Setting::get('superadmin_commission_payout_mode', 'mp_auto_billing'));

        if (!$autoEnabled || !in_array($mode, ['efi_pix_out', 'mp_auto_billing'], true)) {
            $summary['skipped'] = true;

            return $summary;
        }

        $query = OrderSplitPayout::query()
            ->with('split')
            ->whereIn('status', ['pending', 'failed'])
            ->where('attempts', '<', 10)
            ->whereHas('split', fn ($q) => $q->where('receiver_type', 'superadmin')->where('status', '!=', 'paid'));

        if (!$ignoreSchedule && Schema::hasColumn('order_split_payouts', 'scheduled_for')) {
            $query->where(function ($q) {
                $q->whereNull('scheduled_for')->orWhere('scheduled_for', '<=', now());
            });
        }

        $payouts = $query->orderBy('id')->get();

        foreach ($payouts as $payout) {
            if (!$payout->split) {
                continue;
            }

            $summary['processed']++;

            try {
                $updated = $this->processAutomaticPayout($payout->split, $ignoreSchedule);
                if ((string) $updated->status === 'paid') {
                    $summary['paid']++;
                } else {
                    $summary['waiting_balance']++;
                }
            } catch (\Throwable $e) {
                $summary['failed']++;
            }
        }

        return $summary;
    }

    public function confirmManualPayout(OrderSplit $split): OrderSplitPayout
    {
        return DB::transaction(function () use ($split) {
            $lockedSplit = OrderSplit::query()
                ->with('payout')
                ->lockForUpdate()
                ->findOrFail($split->id);

            if ((string) ($lockedSplit->payout?->status) === 'paid' || (string) $lockedSplit->status === 'paid') {
                throw new \RuntimeException('Este repasse ja foi conciliado anteriormente.');
            }

            if (empty($lockedSplit->pix_key)) {
                throw new \RuntimeException('Cadastre a chave PIX do destinatario antes de confirmar o repasse.');
            }

            $payout = $this->syncForSplit($lockedSplit);

            $payout->forceFill([
                'provider' => 'manual',
                'status' => 'paid',
                'attempts' => (int) $payout->attempts + 1,
                'last_error' => null,
                'last_attempt_at' => now(),
                'processed_at' => now(),
                'notes' => 'Repasse confirmado manualmente pelo administrativo.',
            ])->save();

            $lockedSplit->forceFill(['status' => 'paid'])->save();

            return $payout->fresh();
        });
    }

    public function registerFailure(OrderSplit $split, string $message): OrderSplitPayout
    {
        $message = trim($message);
        if ($message === '') {
            throw new \RuntimeException('Informe o motivo da falha do repasse.');
        }

        return DB::transaction(function () use ($split, $message) {
            $lockedSplit = OrderSplit::query()
                ->with('payout')
                ->lockForUpdate()
                ->findOrFail($split->id);

            if ((string) ($lockedSplit->payout?->status) === 'paid' || (string) $lockedSplit->status === 'paid') {
                throw new \RuntimeException('Este repasse ja foi conciliado e nao pode receber falha.');
            }

            $payout = $this->syncForSplit($lockedSplit);

            $payout->forceFill([
                'provider' => $payout->provider ?: 'manual',
                'status' => 'failed',
                'attempts' => (int) $payout->attempts + 1,
                'last_error' => $message,
                'last_attempt_at' => now(),
                'notes' => 'Ultima tentativa registrada manualmente pelo administrativo.',
            ])->save();

            return $payout->fresh();
        });
    }

    private function resolveProvider(OrderSplit $split, ?OrderSplitPayout $existing, bool $isInternallySettled): string
    {
        if ($existing && in_array((string) $existing->provider, ['manual', 'efi_pix_out', 'mercadopago_split', 'mp_auto_billing'], true) && (string) $existing->status === 'paid') {
            return (string) $existing->provider;
        }

        $mpSplitApplied = (bool) data_get($split->order?->metadata, 'mp_marketplace_split_applied', false);
        if ($isInternallySettled && $mpSplitApplied && in_array((string) $split->receiver_type, ['superadmin', 'platform'], true)) {
            return 'mercadopago_split';
        }

        if ($isInternallySettled) {
            return 'internal';
        }

        $autoEnabled = (string) Setting::get('superadmin_commission_auto_enabled', '0') === '1';
        $mode = trim((string) Setting::get('superadmin_commission_payout_mode', 'mp_auto_billing'));

        if ((string) $split->receiver_type === 'superadmin' && $autoEnabled && in_array($mode, ['efi_pix_out', 'mp_auto_billing'], true)) {
            return $mode;
        }

        return $existing?->provider ?: 'manual';
    }

    private function resolveStatus(?OrderSplitPayout $existing, bool $isInternallySettled): string
    {
        if ($existing && in_array((string) $existing->status, ['failed', 'processing'], true) && !$isInternallySettled) {
            return (string) $existing->status;
        }

        if ($existing && in_array((string) $existing->provider, ['manual', 'efi_pix_out', 'mercadopago_split', 'mp_auto_billing'], true) && (string) $existing->status === 'paid') {
            return 'paid';
        }

        return $isInternallySettled ? 'paid' : 'pending';
    }

    private function resolveNotes(string $provider, string $status, ?OrderSplitPayout $existing): ?string
    {
        if ($status === 'paid' && $provider === 'internal') {
            return 'Liquidacao interna por consolidacao do proprio recebedor.';
        }

        if ($status === 'paid' && $provider === 'mercadopago_split') {
            return 'Comissao liquidada automaticamente via divisao nativa do Mercado Pago.';
        }

        if ($status === 'pending' && $provider === 'mp_auto_billing' && empty($existing?->notes)) {
            return 'Pronto para cobranca automatica a cada venda na conta Mercado Pago do Superadmin.';
        }

        if ($status === 'pending' && $provider === 'efi_pix_out' && empty($existing?->notes)) {
            return 'Agendado para envio automatico via Pix Out (Efi Pay) para a chave Pix do Mercado Pago.';
        }

        return $existing?->notes;
    }
}
