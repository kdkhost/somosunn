<?php

namespace App\Services;

use App\Models\Order;
use Carbon\CarbonInterface;
use Illuminate\Pagination\AbstractPaginator;
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
