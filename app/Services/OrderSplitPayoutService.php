<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderSplit;
use App\Models\OrderSplitPayout;
use Illuminate\Support\Facades\DB;

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

        $provider = $this->resolveProvider($existing, $isInternallySettled);
        $status = $this->resolveStatus($existing, $isInternallySettled);

        $payload = [
            'provider' => $provider,
            'status' => $status,
            'amount' => (float) $split->amount,
            'pix_key' => $split->pix_key,
            'processed_at' => $status === 'paid' ? ($existing?->processed_at ?? now()) : null,
            'notes' => $status === 'paid' && $provider === 'internal'
                ? 'Liquidacao interna por consolidacao do proprio recebedor.'
                : ($existing?->notes),
        ];

        return OrderSplitPayout::updateOrCreate(
            ['order_split_id' => $split->id],
            $payload
        );
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

    private function resolveProvider(?OrderSplitPayout $existing, bool $isInternallySettled): string
    {
        if ($existing && $existing->provider === 'manual' && (string) $existing->status === 'paid') {
            return 'manual';
        }

        return $isInternallySettled ? 'internal' : ($existing?->provider ?: 'manual');
    }

    private function resolveStatus(?OrderSplitPayout $existing, bool $isInternallySettled): string
    {
        if ($existing && in_array((string) $existing->status, ['failed', 'processing'], true) && !$isInternallySettled) {
            return (string) $existing->status;
        }

        if ($existing && (string) $existing->provider === 'manual' && (string) $existing->status === 'paid') {
            return 'paid';
        }

        return $isInternallySettled ? 'paid' : 'pending';
    }
}
