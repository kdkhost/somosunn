<?php

namespace App\Traits;

use App\Models\Order;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

trait PreventsDoubleSubmit
{
    /**
     * Verifica se já existe um pedido pending recente para o mesmo item.
     * Se existir, retorna o pedido existente em vez de criar um novo.
     *
     * @param string $itemType  ex: 'course', 'plan', 'event', 'seller_product'
     * @param int    $itemId    ID do item
     * @param string $gateway   ex: 'mercadopago', 'sumup'
     * @param int    $maxAgeHours  Máximo de horas para considerar como duplicata
     * @return Order|null
     */
    protected function findReusablePendingOrder(string $itemType, int $itemId, string $gateway, int $maxAgeHours = 24): ?Order
    {
        $userId = (int) Auth::id();
        if (!$userId) {
            return null;
        }

        return Order::where('user_id', $userId)
            ->where('status', 'pending')
            ->where('gateway', $gateway)
            ->whereHas('items', function ($q) use ($itemType, $itemId) {
                $q->where('item_type', $itemType)->where('item_id', $itemId);
            })
            ->where('created_at', '>', now()->subHours($maxAgeHours))
            ->latest('id')
            ->first();
    }

    /**
     * Previne double-click no formulário usando cache lock.
     * Retorna true se o request é duplicado (deve ser bloqueado).
     */
    protected function isDoubleSubmit(string $action, int $lockSeconds = 10): bool
    {
        $userId = (int) Auth::id();
        $key = "checkout_lock:{$userId}:{$action}";

        if (Cache::has($key)) {
            return true; // É double-submit
        }

        Cache::put($key, true, $lockSeconds);
        return false;
    }

    /**
     * Libera o lock de double-submit (chamar após erro para permitir retry).
     */
    protected function releaseDoubleSubmitLock(string $action): void
    {
        $userId = (int) Auth::id();
        $key = "checkout_lock:{$userId}:{$action}";
        Cache::forget($key);
    }
}
