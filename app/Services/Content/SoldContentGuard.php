<?php

namespace App\Services\Content;

use App\Models\OrderItem;
use Illuminate\Support\Arr;
use Illuminate\Validation\ValidationException;

class SoldContentGuard
{
    private const FINANCIAL_HISTORY_STATUSES = [
        'paid',
        'refunded',
        'cancelled',
    ];

    public function preventDeletionIfSold(string|array $itemTypes, int $itemId, string $label): void
    {
        if (!$this->hasFinancialHistory($itemTypes, $itemId)) {
            return;
        }

        throw ValidationException::withMessages([
            'delete' => sprintf(
                'Não é possível excluir este %s porque já existe histórico financeiro vinculado. Despublique ou arquive para preservar vendas, faturas e certificados.',
                $label
            ),
        ]);
    }

    public function hasFinancialHistory(string|array $itemTypes, int $itemId): bool
    {
        $types = $this->expandItemTypeAliases(array_values(array_filter(Arr::wrap($itemTypes))));

        if ($itemId <= 0 || $types === []) {
            return false;
        }

        return OrderItem::query()
            ->whereIn('item_type', $types)
            ->where('item_id', $itemId)
            ->whereHas('order', function ($query) {
                $query->whereIn('status', self::FINANCIAL_HISTORY_STATUSES);
            })
            ->exists();
    }

    private function expandItemTypeAliases(array $itemTypes): array
    {
        $aliases = [
            'course' => ['course', \App\Models\Course::class],
            'mentorship' => ['mentorship', \App\Models\Mentorship::class],
            'event' => ['event', \App\Models\Event::class],
            'seller_product' => ['seller_product', \App\Models\SellerProduct::class],
            'plan' => ['plan', 'subscription', \App\Models\Plan::class],
        ];

        $expanded = [];
        foreach ($itemTypes as $type) {
            foreach ($aliases[(string) $type] ?? [(string) $type] as $alias) {
                $expanded[] = $alias;
            }
        }

        return array_values(array_unique(array_filter($expanded)));
    }
}
