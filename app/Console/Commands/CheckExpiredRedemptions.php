<?php

namespace App\Console\Commands;

use App\Models\PointsLog;
use App\Models\Redemption;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class CheckExpiredRedemptions extends Command
{
    protected $signature = 'redemptions:check-expired';
    protected $description = 'Cancela resgates nao entregues no prazo e aplica punicao ao vendedor (bloqueio 2 dias + suspensao 2 eventos)';

    public function handle(): int
    {
        $this->info('Verificando resgates expirados...');

        $count = 0;

        // Buscar resgates em status 'processing' ou 'shipped' que passaram do prazo
        Redemption::query()
            ->whereIn('status', ['processing', 'pending'])
            ->whereNotNull('redeemable_item_id')
            ->with(['item', 'user', 'providerUser'])
            ->chunkById(50, function ($redemptions) use (&$count) {
                foreach ($redemptions as $redemption) {
                    if ($this->isExpired($redemption)) {
                        $this->cancelAndPunish($redemption);
                        $count++;
                    }
                }
            });

        $this->info("Resgates cancelados por atraso: {$count}");

        return self::SUCCESS;
    }

    /**
     * Verifica se o resgate passou do prazo de entrega.
     */
    protected function isExpired(Redemption $redemption): bool
    {
        $item = $redemption->item;
        if (!$item) {
            return false;
        }

        // Prazo em dias (padrao 7 se nao configurado)
        $leadDays = max(1, (int) ($item->delivery_lead_days ?: 7));

        // Data base: quando foi aprovado (processing) ou criado
        $baseDate = $redemption->updated_at ?? $redemption->created_at;

        $deadline = Carbon::parse($baseDate)->addDays($leadDays);

        return now()->greaterThan($deadline);
    }

    /**
     * Cancela o resgate, restaura pontos e aplica punicao ao vendedor.
     */
    protected function cancelAndPunish(Redemption $redemption): void
    {
        $buyer = $redemption->user;
        $seller = $redemption->providerUser;

        $itemName = $redemption->item ? $redemption->item->name : '?';
        $this->info("  Cancelando resgate #{$redemption->id} (item: {$itemName})");

        // 1) Restaurar pontos do comprador
        if ($buyer && $redemption->points_spent > 0) {
            $buyer->increment('points', $redemption->points_spent);

            PointsLog::create([
                'user_id' => $buyer->id,
                'action_key' => 'redemption_expired_refund',
                'points' => $redemption->points_spent,
                'meta' => json_encode([
                    'redemption_id' => $redemption->id,
                    'item_name' => $redemption->item->name ?? 'Item',
                    'reason' => 'Vendedor nao entregou no prazo. Pontos restaurados.',
                ]),
            ]);

            $this->info("    Pontos restaurados: {$redemption->points_spent} para {$buyer->name}");
        }

        // 2) Restaurar estoque
        if ($redemption->item && (int) $redemption->item->stock >= 0) {
            $redemption->item->increment('stock');
        }

        // 3) Cancelar o resgate
        $redemption->update([
            'status' => 'cancelled',
            'cancelled_at' => now(),
            'admin_notes' => ($redemption->admin_notes ? $redemption->admin_notes . "\n" : '')
                . '[AUTO] Cancelado por atraso na entrega. Pontos restaurados ao comprador. Punicao aplicada ao vendedor.',
        ]);

        // 4) Punir o vendedor (se existir)
        if ($seller) {
            $this->applyPunishment($seller, $redemption);
        }

        // 5) Notificar comprador
        if ($buyer) {
            try {
                $buyer->notify(new \App\Notifications\RedemptionStatusUpdated($redemption));
            } catch (\Throwable $e) {
                Log::warning("Falha ao notificar comprador sobre cancelamento: " . $e->getMessage());
            }
        }
    }

    /**
     * Aplica punicao ao vendedor: bloqueio de 2 dias + suspensao de 2 eventos.
     * Punicoes se acumulam.
     */
    protected function applyPunishment(User $seller, Redemption $redemption): void
    {
        $blockDays = 2;
        $eventsSuspended = 2;

        // Bloqueio: se ja esta bloqueado, acumula a partir do fim do bloqueio atual
        $currentBlock = $seller->blocked_until ? Carbon::parse($seller->blocked_until) : now();
        if ($currentBlock->isPast()) {
            $currentBlock = now();
        }
        $newBlockUntil = $currentBlock->addDays($blockDays);

        // Suspensao de eventos: acumula
        $currentSuspension = (int) ($seller->events_suspension_remaining ?? 0);
        $newSuspension = $currentSuspension + $eventsSuspended;

        $itemNameForReason = $redemption->item ? $redemption->item->name : 'item';
        $reason = "Nao entregou resgate #{$redemption->id} ({$itemNameForReason}) no prazo. "
            . "Bloqueio ate " . $newBlockUntil->format('d/m/Y H:i') . ". "
            . "Suspensao de {$newSuspension} eventos.";

        $seller->update([
            'blocked_until' => $newBlockUntil,
            'block_reason' => $reason,
            'events_suspension_remaining' => $newSuspension,
        ]);

        Log::info("Punicao aplicada ao vendedor #{$seller->id} ({$seller->name}): bloqueio ate {$newBlockUntil->format('d/m/Y H:i')}, {$newSuspension} eventos suspensos.");
        $this->warn("    Punicao: {$seller->name} bloqueado ate {$newBlockUntil->format('d/m/Y H:i')}, {$newSuspension} eventos suspensos.");
    }
}
