<?php

namespace App\Services;

use App\Models\Order;
use Carbon\CarbonInterface;
use Illuminate\Pagination\AbstractPaginator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class SalesAnalyticsService
{
    /**
     * @return array<string, array<int, array{units_sold:int,orders_count:int,buyers_count:int,net_revenue:float}>>
     */
    public function paidItemMetrics(array $types = [], ?CarbonInterface $from = null, ?CarbonInterface $to = null): array
    {
        $query = DB::table('order_items')
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->where('orders.status', 'paid')
            ->selectRaw('
                order_items.item_type,
                order_items.item_id,
                SUM(order_items.quantity) as units_sold,
                COUNT(DISTINCT orders.id) as orders_count,
                COUNT(DISTINCT orders.user_id) as buyers_count,
                SUM(order_items.price * order_items.quantity) as net_revenue
            ')
            ->groupBy('order_items.item_type', 'order_items.item_id');

        if (!empty($types)) {
            $query->whereIn('order_items.item_type', $types);
        }

        $this->applyPeriodFilter($query, $from, $to);

        $metrics = [];

        foreach ($query->get() as $row) {
            $type = (string) $row->item_type;
            $itemId = (int) $row->item_id;

            $metrics[$type][$itemId] = [
                'units_sold' => (int) $row->units_sold,
                'orders_count' => (int) $row->orders_count,
                'buyers_count' => (int) $row->buyers_count,
                'net_revenue' => round((float) $row->net_revenue, 2),
            ];
        }

        return $metrics;
    }

    public function decorateEvents(mixed $events, ?CarbonInterface $from = null, ?CarbonInterface $to = null): mixed
    {
        $metrics = $this->paidItemMetrics(['event', 'event_exhibitor_area'], $from, $to);

        return $this->decorate($events, function ($event) use ($metrics) {
            $eventId = (int) $event->id;
            $ticketMetrics = $metrics['event'][$eventId] ?? null;
            $exhibitorMetrics = $metrics['event_exhibitor_area'][$eventId] ?? null;

            $ticketsSold = (int) ($ticketMetrics['units_sold'] ?? 0);
            $ticketsOrders = (int) ($ticketMetrics['orders_count'] ?? 0);
            $exhibitorsSold = (int) ($exhibitorMetrics['units_sold'] ?? 0);
            $exhibitorOrders = (int) ($exhibitorMetrics['orders_count'] ?? 0);

            $event->setAttribute('tickets_sold_count', $ticketsSold);
            $event->setAttribute('ticket_orders_count', $ticketsOrders);
            $event->setAttribute('exhibitor_sales_count', $exhibitorsSold);
            $event->setAttribute('exhibitor_orders_count', $exhibitorOrders);
            $event->setAttribute('total_sales_count', $ticketsSold + $exhibitorsSold);
            $event->setAttribute('sales_revenue_total', round(
                (float) ($ticketMetrics['net_revenue'] ?? 0) + (float) ($exhibitorMetrics['net_revenue'] ?? 0),
                2
            ));
        });
    }

    public function decorateCourses(mixed $courses, ?CarbonInterface $from = null, ?CarbonInterface $to = null): mixed
    {
        $metrics = $this->paidItemMetrics(['course'], $from, $to);

        return $this->decorate($courses, function ($course) use ($metrics) {
            $data = $metrics['course'][(int) $course->id] ?? null;

            $course->setAttribute('sales_count', (int) ($data['units_sold'] ?? 0));
            $course->setAttribute('sales_orders_count', (int) ($data['orders_count'] ?? 0));
            $course->setAttribute('buyers_count', (int) ($data['buyers_count'] ?? 0));
            $course->setAttribute('sales_revenue_total', round((float) ($data['net_revenue'] ?? 0), 2));
        });
    }

    public function decorateMentorships(mixed $mentorships, ?CarbonInterface $from = null, ?CarbonInterface $to = null): mixed
    {
        $metrics = $this->paidItemMetrics(['mentorship'], $from, $to);

        return $this->decorate($mentorships, function ($mentorship) use ($metrics) {
            $data = $metrics['mentorship'][(int) $mentorship->id] ?? null;

            $mentorship->setAttribute('sales_count', (int) ($data['units_sold'] ?? 0));
            $mentorship->setAttribute('buyers_count', (int) ($data['buyers_count'] ?? 0));
            $mentorship->setAttribute('sales_orders_count', (int) ($data['orders_count'] ?? 0));
            $mentorship->setAttribute('sales_revenue_total', round((float) ($data['net_revenue'] ?? 0), 2));
        });
    }

    public function decorateSellerProducts(mixed $products, ?CarbonInterface $from = null, ?CarbonInterface $to = null): mixed
    {
        $metrics = $this->paidItemMetrics(['seller_product'], $from, $to);

        return $this->decorate($products, function ($product) use ($metrics) {
            $data = $metrics['seller_product'][(int) $product->id] ?? null;

            $product->setAttribute('sales_count', (int) ($data['units_sold'] ?? 0));
            $product->setAttribute('buyers_count', (int) ($data['buyers_count'] ?? 0));
            $product->setAttribute('sales_orders_count', (int) ($data['orders_count'] ?? 0));
            $product->setAttribute('sales_revenue_total', round((float) ($data['net_revenue'] ?? 0), 2));
        });
    }

    /**
     * @return array{rows: AbstractPaginator, summary: array<string, int|float>}
     */
    public function productSalesReport(
        string $search = '',
        ?string $saleType = null,
        ?CarbonInterface $from = null,
        ?CarbonInterface $to = null,
        int $perPage = 20
    ): array {
        $aggregate = DB::table('order_items')
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->where('orders.status', 'paid')
            ->selectRaw('
                order_items.item_type,
                order_items.item_id,
                MAX(order_items.title) as title,
                SUM(order_items.quantity) as units_sold,
                COUNT(DISTINCT orders.id) as orders_count,
                COUNT(DISTINCT orders.user_id) as buyers_count,
                SUM(order_items.price * order_items.quantity) as net_revenue
            ')
            ->groupBy('order_items.item_type', 'order_items.item_id');

        $validTypes = array_keys(Order::SALE_TYPE_LABELS);
        $saleType = trim((string) $saleType);

        if ($saleType !== '' && in_array($saleType, $validTypes, true)) {
            $aggregate->where('order_items.item_type', $saleType);
        }

        if ($search !== '') {
            $aggregate->where('order_items.title', 'like', '%' . $search . '%');
        }

        $this->applyPeriodFilter($aggregate, $from, $to);

        $rows = DB::query()
            ->fromSub($aggregate, 'sales_report')
            ->selectRaw('
                sales_report.item_type,
                sales_report.item_id,
                sales_report.title,
                sales_report.units_sold,
                sales_report.orders_count,
                sales_report.buyers_count,
                sales_report.net_revenue
            ')
            ->orderByDesc('sales_report.units_sold')
            ->orderByDesc('sales_report.net_revenue')
            ->paginate($perPage)
            ->withQueryString();

        $summary = DB::query()
            ->fromSub($aggregate, 'sales_report_summary')
            ->selectRaw('
                COUNT(*) as catalog_items_count,
                COALESCE(SUM(units_sold), 0) as total_units_sold,
                COALESCE(SUM(orders_count), 0) as total_orders_count,
                COALESCE(SUM(buyers_count), 0) as total_buyers_count,
                COALESCE(SUM(net_revenue), 0) as total_revenue
            ')
            ->first();

        return [
            'rows' => $rows,
            'summary' => [
                'catalog_items_count' => (int) ($summary->catalog_items_count ?? 0),
                'total_units_sold' => (int) ($summary->total_units_sold ?? 0),
                'total_orders_count' => (int) ($summary->total_orders_count ?? 0),
                'total_buyers_count' => (int) ($summary->total_buyers_count ?? 0),
                'total_revenue' => round((float) ($summary->total_revenue ?? 0), 2),
            ],
        ];
    }

    /**
     * @return array{
     *     item: array{type:string,id:int,title:string,type_label:string,purchase_type_label:string},
     *     rows: Collection<int, object>,
     *     summary: array{buyers_count:int,orders_count:int,quantity:int,total_amount:float}
     * }
     */
    public function paidItemBuyersReport(
        string $itemType,
        int $itemId,
        ?CarbonInterface $from = null,
        ?CarbonInterface $to = null
    ): array {
        $baseQuery = DB::table('order_items')
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->leftJoin('users', 'users.id', '=', 'orders.user_id')
            ->where('orders.status', 'paid')
            ->where('order_items.item_type', $itemType)
            ->where('order_items.item_id', $itemId);

        $this->applyPeriodFilter($baseQuery, $from, $to);

        $rows = (clone $baseQuery)
            ->selectRaw("
                orders.id as order_id,
                order_items.item_type,
                order_items.item_id,
                MAX(order_items.title) as item_title,
                users.id as buyer_user_id,
                COALESCE(NULLIF(users.name, ''), CONCAT('Pedido #', orders.id)) as buyer_name,
                COALESCE(NULLIF(users.email, ''), '') as buyer_email,
                COALESCE(NULLIF(users.phone, ''), '') as buyer_phone,
                COALESCE(orders.paid_at, orders.manual_approved_at, orders.created_at) as purchased_at,
                COALESCE(NULLIF(orders.payment_method, ''), NULLIF(orders.gateway, ''), '-') as payment_method,
                orders.transaction_id,
                SUM(order_items.quantity) as quantity,
                SUM(order_items.price * order_items.quantity) as total_amount
            ")
            ->groupBy(
                'orders.id',
                'order_items.item_type',
                'order_items.item_id',
                'users.id',
                'users.name',
                'users.email',
                'users.phone',
                'orders.paid_at',
                'orders.manual_approved_at',
                'orders.created_at',
                'orders.payment_method',
                'orders.gateway',
                'orders.transaction_id'
            )
            ->orderByRaw("LOWER(COALESCE(NULLIF(users.name, ''), CONCAT('Pedido #', orders.id))) ASC")
            ->orderBy('orders.id')
            ->get()
            ->map(function ($row) use ($itemType) {
                $row->buyer_user_id = $row->buyer_user_id !== null ? (int) $row->buyer_user_id : null;
                $row->quantity = (int) $row->quantity;
                $row->total_amount = round((float) $row->total_amount, 2);
                $row->purchase_type_label = $this->purchaseTypeLabel($itemType);
                $row->purchased_at = $row->purchased_at ? Carbon::parse($row->purchased_at) : null;

                return $row;
            });

        $itemTitle = (string) optional($rows->first())->item_title;
        if ($itemTitle === '') {
            $itemTitle = 'Item #' . $itemId;
        }

        return [
            'item' => [
                'type' => $itemType,
                'id' => $itemId,
                'title' => $itemTitle,
                'type_label' => Order::SALE_TYPE_LABELS[$itemType] ?? ucfirst(str_replace('_', ' ', $itemType)),
                'purchase_type_label' => $this->purchaseTypeLabel($itemType),
            ],
            'rows' => $rows,
            'summary' => [
                'buyers_count' => $rows->map(function ($row) {
                    if ($row->buyer_user_id) {
                        return 'user:' . $row->buyer_user_id;
                    }

                    if ((string) $row->buyer_email !== '') {
                        return 'email:' . strtolower((string) $row->buyer_email);
                    }

                    return 'order:' . $row->order_id;
                })->unique()->count(),
                'orders_count' => $rows->count(),
                'quantity' => (int) $rows->sum('quantity'),
                'total_amount' => round((float) $rows->sum('total_amount'), 2),
            ],
        ];
    }

    private function purchaseTypeLabel(string $itemType): string
    {
        return match ($itemType) {
            'event' => 'Ingresso',
            'event_exhibitor_area' => 'Expositor',
            'course' => 'Curso',
            'mentorship' => 'Mentoria',
            'seller_product' => 'Marketplace',
            'plan' => 'Plano/Assinatura',
            default => Order::SALE_TYPE_LABELS[$itemType] ?? 'Outro',
        };
    }

    private function applyPeriodFilter(mixed $query, ?CarbonInterface $from, ?CarbonInterface $to): void
    {
        if (!$from || !$to) {
            return;
        }

        $query->whereBetween(
            DB::raw('COALESCE(orders.paid_at, orders.manual_approved_at, orders.created_at)'),
            [$from->copy()->startOfDay(), $to->copy()->endOfDay()]
        );
    }

    private function decorate(mixed $items, callable $callback): mixed
    {
        if ($items instanceof AbstractPaginator) {
            $collection = $items->getCollection();
            $collection->each($callback);
            $items->setCollection($collection);

            return $items;
        }

        if ($items instanceof Collection) {
            $items->each($callback);
        }

        return $items;
    }
}
