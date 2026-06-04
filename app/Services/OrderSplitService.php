<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderSplit;
use App\Models\Setting;
use App\Models\User;
use App\Support\MarketplaceFee;
use Illuminate\Support\Facades\DB;

class OrderSplitService
{
    public function syncForPaidOrder(Order $order): void
    {
        if ((string) $order->status !== 'paid') {
            return;
        }

        $order->loadMissing('seller');

        DB::transaction(function () use ($order) {
            if ((float) $order->total_amount <= 0) {
                OrderSplit::query()->where('order_id', $order->id)->delete();
                $order->platform_fee_amount = 0;
                $order->metadata = array_merge($order->metadata ?? [], [
                    'platform_fee_percent' => MarketplaceFee::deductionPercent($order->seller),
                ]);
                $order->save();

                return;
            }

            $existing = OrderSplit::query()
                ->where('order_id', $order->id)
                ->lockForUpdate()
                ->get();

            $desired = $this->desiredSplits($order);

            OrderSplit::query()->where('order_id', $order->id)->delete();

            foreach ($desired as $split) {
                $wasPaid = $existing
                    ->where('receiver_id', $split['receiver_id'])
                    ->where('status', 'paid')
                    ->sum(fn (OrderSplit $item) => (float) $item->amount) >= ((float) $split['amount'] - 0.009);

                OrderSplit::create([
                    'order_id' => $order->id,
                    'receiver_id' => $split['receiver_id'],
                    'receiver_type' => $split['receiver_type'],
                    'percentage' => $split['percentage'],
                    'amount' => $split['amount'],
                    'pix_key' => $split['pix_key'],
                    'status' => $split['auto_paid'] || $wasPaid ? 'paid' : 'pending',
                ]);
            }

            $externalAmount = collect($desired)
                ->filter(fn (array $split) => $split['receiver_type'] !== 'seller' && !$split['auto_paid'])
                ->sum('amount');

            $order->platform_fee_amount = round((float) $externalAmount, 2);
            $order->metadata = array_merge($order->metadata ?? [], [
                'platform_fee_percent' => MarketplaceFee::deductionPercent($order->seller),
            ]);
            $order->save();
        });
    }

    /**
     * @return array<int,array{receiver_id:?int,receiver_type:string,percentage:float,amount:float,pix_key:?string,auto_paid:bool}>
     */
    private function desiredSplits(Order $order): array
    {
        $total = (float) $order->total_amount;
        $seller = $order->seller;
        $platformOwner = $seller?->isAdmin() && !$seller->isSuperAdmin() ? $seller : $this->platformOwner();
        $superadmin = $this->superadmin();
        $revenueOwner = $seller ?? $platformOwner;
        $marketingManager = $this->marketingManager();

        $shares = [
            ['type' => $seller ? 'seller' : 'platform', 'percent' => $this->percent('marketplace_split_seller_percent', 70), 'receiver' => $revenueOwner],
            ['type' => 'platform', 'percent' => $this->percent('marketplace_split_platform_percent', 10), 'receiver' => $platformOwner],
            ['type' => 'traffic', 'percent' => $this->percent('marketplace_split_traffic_percent', 10), 'receiver' => $marketingManager],
            ['type' => 'superadmin', 'percent' => $this->percent('marketplace_split_superadmin_percent', 10), 'receiver' => $superadmin],
        ];

        $grouped = [];
        foreach ($shares as $share) {
            $percent = (float) $share['percent'];
            if ($percent <= 0) {
                continue;
            }

            $receiver = $share['receiver'];
            $receiverId = $receiver?->id;
            $consolidateWithSeller = $receiverId !== null && $receiverId === $seller?->id;
            $key = $consolidateWithSeller
                ? 'seller:' . $receiverId
                : $share['type'] . ':' . ($receiverId ?? 'unassigned');

            if (!isset($grouped[$key])) {
                $grouped[$key] = [
                    'receiver_id' => $receiverId,
                    'receiver_type' => $this->consolidatedType($share['type'], $receiverId, $seller?->id, $revenueOwner?->id, $marketingManager?->id),
                    'percentage' => 0.0,
                    'amount' => 0.0,
                    'pix_key' => $receiver?->pix_key,
                    'auto_paid' => $receiverId !== null && $receiverId === $seller?->id,
                ];
            }

            $grouped[$key]['percentage'] += $percent;
            $grouped[$key]['amount'] += round(($total * $percent) / 100, 2);
        }

        foreach ($grouped as &$split) {
            $split['percentage'] = round((float) $split['percentage'], 2);
            $split['amount'] = round((float) $split['amount'], 2);
        }
        unset($split);

        return array_values($grouped);
    }

    private function consolidatedType(string $originalType, ?int $receiverId, ?int $sellerId, ?int $revenueOwnerId, ?int $marketingManagerId): string
    {
        if ($receiverId !== null && $receiverId === $sellerId) {
            return 'seller';
        }

        if ($receiverId !== null && $receiverId === $revenueOwnerId) {
            return 'platform';
        }

        if ($receiverId !== null && $receiverId === $marketingManagerId) {
            return 'traffic';
        }

        return $originalType;
    }

    private function platformOwner(): ?User
    {
        foreach (['platform_owner_id', 'platform_admin_user_id'] as $key) {
            $userId = (int) Setting::get($key, 0);
            $user = $userId > 0 ? User::find($userId) : null;
            if ($user?->isAdmin() && !$user->isSuperAdmin()) {
                return $user;
            }
        }

        return User::query()
                ->where(fn ($query) => $query->where('role', 'admin')->orWhere('level', 'sucesso'))
                ->oldest('id')
                ->first()
            ?? $this->superadmin();
    }

    private function superadmin(): ?User
    {
        return User::query()
            ->where(fn ($query) => $query->where('role', 'superadmin')->orWhere('level', 'superadmin'))
            ->oldest('id')
            ->first();
    }

    private function marketingManager(): ?User
    {
        $userId = (int) Setting::get('platform_marketing_user_id', 0);

        return $userId > 0 ? User::find($userId) : null;
    }

    private function percent(string $key, float $default): float
    {
        return max(0, (float) Setting::get($key, $default));
    }
}
