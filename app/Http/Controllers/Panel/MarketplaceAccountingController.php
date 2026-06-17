<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\StreamedResponse;

class MarketplaceAccountingController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        abort_unless($user && ($user->isAdmin() || $user->canSellOnMarketplace()), 403);

        [$salesQuery, $purchasesQuery, $period] = $this->buildQueries((int) $user->id, $request);

        $summary = $this->buildSummaryFromQueries($salesQuery, $purchasesQuery);

        $sales = $salesQuery->paginate(20, ['*'], 'sales_page')->withQueryString();
        $purchases = $purchasesQuery->paginate(20, ['*'], 'purchases_page')->withQueryString();

        return view('panel.marketplace.accounting', compact('summary', 'sales', 'purchases', 'period'));
    }

    public function export(Request $request): StreamedResponse
    {
        $user = Auth::user();
        abort_unless($user && ($user->isAdmin() || $user->canSellOnMarketplace()), 403);

        [$salesQuery, $purchasesQuery, $period] = $this->buildQueries((int) $user->id, $request);
        $summary = $this->buildSummaryFromQueries($salesQuery, $purchasesQuery);
        $filename = 'contabilidade-membro-' . now()->format('Ymd-His') . '.csv';

        return response()->streamDownload(function () use ($summary, $salesQuery, $purchasesQuery, $period) {
            $handle = fopen('php://output', 'w');
            if (!$handle) {
                return;
            }

            fputcsv($handle, ['Relatorio', 'Contabilidade do Membro']);
            fputcsv($handle, ['Periodo', $period['label']]);
            fputcsv($handle, ['Gerado em', now()->format('d/m/Y H:i')]);
            fputcsv($handle, []);

            fputcsv($handle, ['Valor bruto dos produtos vendidos', number_format((float) $summary['sales_gross'], 2, '.', '')]);
            fputcsv($handle, ['Descontos em vendas', number_format((float) $summary['sales_discounts'], 2, '.', '')]);
            fputcsv($handle, ['Total cobrado em vendas', number_format((float) $summary['sales_charged'], 2, '.', '')]);
            fputcsv($handle, ['Estornos sobre vendas', number_format((float) $summary['sales_refunds'], 2, '.', '')]);
            fputcsv($handle, ['Taxas e comissoes', number_format((float) $summary['sales_fees'], 2, '.', '')]);
            fputcsv($handle, ['Resultado liquido das vendas', number_format((float) $summary['sales_net'], 2, '.', '')]);
            fputcsv($handle, ['Despesas em compras', number_format((float) $summary['purchase_net'], 2, '.', '')]);
            fputcsv($handle, ['Resultado geral', number_format((float) $summary['overall_net'], 2, '.', '')]);
            fputcsv($handle, []);

            fputcsv($handle, ['VENDAS']);
            fputcsv($handle, ['Pedido', 'Data', 'Comprador', 'Itens', 'Bruto', 'Desconto', 'Cupom', 'Cobranca', 'Estorno', 'Taxas', 'Liquido', 'Status']);
            foreach ((clone $salesQuery)->reorder()->lazyById(250) as $order) {
                fputcsv($handle, [
                    '#' . $order->id,
                    optional($this->financialDate($order))->format('d/m/Y H:i'),
                    (string) ($order->user->name ?? 'Usuario removido'),
                    $this->orderItemsLabel($order),
                    number_format((float) $order->gross_amount, 2, '.', ''),
                    number_format((float) $order->financial_discount_amount, 2, '.', ''),
                    (string) ($order->coupon_code ?: ''),
                    number_format((float) $order->charged_amount, 2, '.', ''),
                    number_format((float) $order->refunded_amount, 2, '.', ''),
                    number_format((float) $this->orderFees($order), 2, '.', ''),
                    number_format((float) $this->orderNetForSeller($order), 2, '.', ''),
                    (string) $order->status,
                ]);
            }

            fputcsv($handle, []);
            fputcsv($handle, ['COMPRAS']);
            fputcsv($handle, ['Pedido', 'Data', 'Vendedor', 'Itens', 'Cobranca', 'Estorno', 'Despesa liquida', 'Status']);
            foreach ((clone $purchasesQuery)->reorder()->lazyById(250) as $order) {
                fputcsv($handle, [
                    '#' . $order->id,
                    optional($this->financialDate($order))->format('d/m/Y H:i'),
                    (string) ($order->seller->name ?? 'Plataforma'),
                    $this->orderItemsLabel($order),
                    number_format((float) $order->charged_amount, 2, '.', ''),
                    number_format((float) $order->refunded_amount, 2, '.', ''),
                    number_format((float) $this->orderNetExpense($order), 2, '.', ''),
                    (string) $order->status,
                ]);
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    public function print(Request $request)
    {
        $user = Auth::user();
        abort_unless($user && ($user->isAdmin() || $user->canSellOnMarketplace()), 403);

        [$salesQuery, $purchasesQuery, $period] = $this->buildQueries((int) $user->id, $request);
        $salesOrders = $salesQuery->get();
        $purchaseOrders = $purchasesQuery->get();
        $summary = $this->buildSummary($salesOrders, $purchaseOrders);

        return view('panel.marketplace.accounting_print', [
            'summary' => $summary,
            'salesOrders' => $salesOrders,
            'purchaseOrders' => $purchaseOrders,
            'period' => $period,
        ]);
    }

    /**
     * @return array{0:\Illuminate\Database\Eloquent\Builder,1:\Illuminate\Database\Eloquent\Builder,2:array{period:string,from:Carbon,to:Carbon,label:string}}
     */
    private function buildQueries(int $userId, Request $request): array
    {
        $period = $this->resolvePeriod($request);

        $salesQuery = Order::query()
            ->with(['user:id,name,email', 'seller:id,name,email', 'items'])
            ->where('seller_id', $userId)
            ->whereIn('status', ['paid', 'refunded'])
            ->where(fn($query) => $this->applyFinancialPeriod($query, $period))
            ->latest('id');

        $purchasesQuery = Order::query()
            ->with(['user:id,name,email', 'seller:id,name,email', 'items'])
            ->where('user_id', $userId)
            ->whereIn('status', ['paid', 'refunded'])
            ->where(fn($query) => $this->applyFinancialPeriod($query, $period))
            ->latest('id');

        return [$salesQuery, $purchasesQuery, $period];
    }

    /**
     * @return array<string,float|int>
     */
    private function buildSummaryFromQueries($salesQuery, $purchasesQuery): array
    {
        $columns = [
            'id',
            'status',
            'total_amount',
            'fee_amount',
            'platform_fee_amount',
            'metadata',
            'refunded_at',
        ];

        return $this->buildSummary(
            (clone $salesQuery)->withoutEagerLoads()->select($columns)->reorder()->lazyById(500),
            (clone $purchasesQuery)->withoutEagerLoads()->select($columns)->reorder()->lazyById(500)
        );
    }

    /**
     * @param iterable<Order> $salesOrders
     * @param iterable<Order> $purchaseOrders
     * @return array<string,float|int>
     */
    private function buildSummary(iterable $salesOrders, iterable $purchaseOrders): array
    {
        $salesCount = $purchaseCount = 0;
        $salesGross = $salesDiscounts = $salesCharged = $salesRefunds = $salesFees = $salesNet = 0.0;
        $purchaseGross = $purchaseDiscounts = $purchaseCharged = $purchaseRefunds = $purchaseNet = 0.0;

        foreach ($salesOrders as $order) {
            $salesCount++;
            $salesGross += (float) $order->gross_amount;
            $salesDiscounts += (float) $order->financial_discount_amount;
            $salesCharged += (float) $order->charged_amount;
            $salesRefunds += (float) $order->refunded_amount;
            $salesFees += $this->orderFees($order);
            $salesNet += $this->orderNetForSeller($order);
        }

        foreach ($purchaseOrders as $order) {
            $purchaseCount++;
            $purchaseGross += (float) $order->gross_amount;
            $purchaseDiscounts += (float) $order->financial_discount_amount;
            $purchaseCharged += (float) $order->charged_amount;
            $purchaseRefunds += (float) $order->refunded_amount;
            $purchaseNet += $this->orderNetExpense($order);
        }

        return [
            'sales_count' => $salesCount,
            'sales_gross' => round($salesGross, 2),
            'sales_discounts' => round($salesDiscounts, 2),
            'sales_charged' => round($salesCharged, 2),
            'sales_refunds' => round($salesRefunds, 2),
            'sales_fees' => round($salesFees, 2),
            'sales_net' => round($salesNet, 2),
            'purchase_count' => $purchaseCount,
            'purchase_gross' => round($purchaseGross, 2),
            'purchase_discounts' => round($purchaseDiscounts, 2),
            'purchase_charged' => round($purchaseCharged, 2),
            'purchase_refunds' => round($purchaseRefunds, 2),
            'purchase_net' => round($purchaseNet, 2),
            'overall_net' => round($salesNet - $purchaseNet, 2),
        ];
    }

    private function applyFinancialPeriod($query, array $period): void
    {
        $range = [$period['from']->copy()->startOfDay(), $period['to']->copy()->endOfDay()];

        $query
            ->whereBetween('paid_at', $range)
            ->orWhere(function ($query) use ($range) {
                $query->whereNull('paid_at')->whereBetween('manual_approved_at', $range);
            })
            ->orWhere(function ($query) use ($range) {
                $query->whereNull('paid_at')
                    ->whereNull('manual_approved_at')
                    ->whereBetween('created_at', $range);
            });
    }

    private function orderFees(Order $order): float
    {
        return round((float) ($order->platform_fee_amount ?? 0) + (float) ($order->fee_amount ?? 0), 2);
    }

    private function orderNetForSeller(Order $order): float
    {
        return round((float) $order->charged_amount - (float) $order->refunded_amount - $this->orderFees($order), 2);
    }

    private function orderNetExpense(Order $order): float
    {
        return round((float) $order->charged_amount - (float) $order->refunded_amount, 2);
    }

    private function orderItemsLabel(Order $order): string
    {
        $label = $order->items->pluck('title')->filter()->take(3)->join(', ');
        if ($order->items->count() > 3) {
            $label .= '...';
        }

        return $label !== '' ? $label : '-';
    }

    private function financialDate(Order $order): ?Carbon
    {
        return $order->paid_at ?: $order->manual_approved_at ?: $order->created_at;
    }

    /**
     * @return array{period:string,from:Carbon,to:Carbon,label:string}
     */
    private function resolvePeriod(Request $request): array
    {
        $period = trim((string) $request->input('period', 'monthly'));
        $validPeriods = ['monthly', 'quarterly', 'semiannual', 'annual', 'custom'];
        if (!in_array($period, $validPeriods, true)) {
            $period = 'monthly';
        }

        $now = now();
        $from = null;
        $to = null;
        $label = '';

        switch ($period) {
            case 'quarterly':
                $from = $now->copy()->subMonths(2)->startOfMonth();
                $to = $now->copy()->endOfMonth();
                $label = 'Trimestral (' . $from->format('d/m/Y') . ' a ' . $to->format('d/m/Y') . ')';
                break;
            case 'semiannual':
                $from = $now->copy()->subMonths(5)->startOfMonth();
                $to = $now->copy()->endOfMonth();
                $label = 'Semestral (' . $from->format('d/m/Y') . ' a ' . $to->format('d/m/Y') . ')';
                break;
            case 'annual':
                $from = $now->copy()->startOfYear();
                $to = $now->copy()->endOfYear();
                $label = 'Anual (' . $now->format('Y') . ')';
                break;
            case 'custom':
                try {
                    $from = Carbon::parse((string) $request->input('date_from'))->startOfDay();
                    $to = Carbon::parse((string) $request->input('date_to'))->endOfDay();
                    if ($to->lt($from)) {
                        [$from, $to] = [$to->copy()->startOfDay(), $from->copy()->endOfDay()];
                    }
                    $label = 'Personalizado (' . $from->format('d/m/Y') . ' a ' . $to->format('d/m/Y') . ')';
                } catch (\Throwable $e) {
                    $period = 'monthly';
                }
                break;
            case 'monthly':
            default:
                $from = $now->copy()->startOfMonth();
                $to = $now->copy()->endOfMonth();
                $label = 'Mensal (' . $now->format('m/Y') . ')';
                break;
        }

        if (!$from || !$to) {
            $from = $now->copy()->startOfMonth();
            $to = $now->copy()->endOfMonth();
            $period = 'monthly';
            $label = 'Mensal (' . $now->format('m/Y') . ')';
        }

        return [
            'period' => $period,
            'from' => $from,
            'to' => $to,
            'label' => $label,
        ];
    }
}
