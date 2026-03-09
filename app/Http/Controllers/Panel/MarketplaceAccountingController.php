<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;

class MarketplaceAccountingController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        abort_unless($user && ($user->isAdmin() || $user->canSellOnMarketplace()), 403);

        [$salesQuery, $purchasesQuery, $period] = $this->buildQueries((int) $user->id, $request);

        $salesOrders = (clone $salesQuery)->get();
        $purchaseOrders = (clone $purchasesQuery)->get();
        $summary = $this->buildSummary($salesOrders, $purchaseOrders);

        $sales = $salesQuery->paginate(20, ['*'], 'sales_page')->withQueryString();
        $purchases = $purchasesQuery->paginate(20, ['*'], 'purchases_page')->withQueryString();

        return view('panel.marketplace.accounting', compact('summary', 'sales', 'purchases', 'period'));
    }

    public function export(Request $request): StreamedResponse
    {
        $user = Auth::user();
        abort_unless($user && ($user->isAdmin() || $user->canSellOnMarketplace()), 403);

        [$salesQuery, $purchasesQuery, $period] = $this->buildQueries((int) $user->id, $request);
        $salesOrders = $salesQuery->get();
        $purchaseOrders = $purchasesQuery->get();
        $summary = $this->buildSummary($salesOrders, $purchaseOrders);
        $filename = 'contabilidade-membro-' . now()->format('Ymd-His') . '.csv';

        return response()->streamDownload(function () use ($summary, $salesOrders, $purchaseOrders, $period) {
            $handle = fopen('php://output', 'w');
            if (!$handle) {
                return;
            }

            fputcsv($handle, ['Relatorio', 'Contabilidade do Membro']);
            fputcsv($handle, ['Periodo', $period['label']]);
            fputcsv($handle, ['Gerado em', now()->format('d/m/Y H:i')]);
            fputcsv($handle, []);

            fputcsv($handle, ['Receita bruta de vendas', number_format((float) $summary['sales_gross'], 2, '.', '')]);
            fputcsv($handle, ['Estornos sobre vendas', number_format((float) $summary['sales_refunds'], 2, '.', '')]);
            fputcsv($handle, ['Taxas e comissoes', number_format((float) $summary['sales_fees'], 2, '.', '')]);
            fputcsv($handle, ['Resultado liquido das vendas', number_format((float) $summary['sales_net'], 2, '.', '')]);
            fputcsv($handle, ['Despesas em compras', number_format((float) $summary['purchase_net'], 2, '.', '')]);
            fputcsv($handle, ['Resultado geral', number_format((float) $summary['overall_net'], 2, '.', '')]);
            fputcsv($handle, []);

            fputcsv($handle, ['VENDAS']);
            fputcsv($handle, ['Pedido', 'Data', 'Comprador', 'Itens', 'Cobranca', 'Estorno', 'Taxas', 'Liquido', 'Status']);
            foreach ($salesOrders as $order) {
                fputcsv($handle, [
                    '#' . $order->id,
                    optional($this->financialDate($order))->format('d/m/Y H:i'),
                    (string) ($order->user->name ?? 'Usuario removido'),
                    $this->orderItemsLabel($order),
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
            foreach ($purchaseOrders as $order) {
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
            ->whereBetween(
                DB::raw('COALESCE(orders.paid_at, orders.manual_approved_at, orders.created_at)'),
                [$period['from']->copy()->startOfDay(), $period['to']->copy()->endOfDay()]
            )
            ->latest('id');

        $purchasesQuery = Order::query()
            ->with(['user:id,name,email', 'seller:id,name,email', 'items'])
            ->where('user_id', $userId)
            ->whereIn('status', ['paid', 'refunded'])
            ->whereBetween(
                DB::raw('COALESCE(orders.paid_at, orders.manual_approved_at, orders.created_at)'),
                [$period['from']->copy()->startOfDay(), $period['to']->copy()->endOfDay()]
            )
            ->latest('id');

        return [$salesQuery, $purchasesQuery, $period];
    }

    /**
     * @return array<string,float|int>
     */
    private function buildSummary(Collection $salesOrders, Collection $purchaseOrders): array
    {
        $salesGross = round((float) $salesOrders->sum(fn(Order $order) => (float) $order->charged_amount), 2);
        $salesRefunds = round((float) $salesOrders->sum(fn(Order $order) => (float) $order->refunded_amount), 2);
        $salesFees = round((float) $salesOrders->sum(fn(Order $order) => (float) $this->orderFees($order)), 2);
        $salesNet = round((float) $salesOrders->sum(fn(Order $order) => (float) $this->orderNetForSeller($order)), 2);

        $purchaseGross = round((float) $purchaseOrders->sum(fn(Order $order) => (float) $order->charged_amount), 2);
        $purchaseRefunds = round((float) $purchaseOrders->sum(fn(Order $order) => (float) $order->refunded_amount), 2);
        $purchaseNet = round((float) $purchaseOrders->sum(fn(Order $order) => (float) $this->orderNetExpense($order)), 2);

        return [
            'sales_count' => (int) $salesOrders->count(),
            'sales_gross' => $salesGross,
            'sales_refunds' => $salesRefunds,
            'sales_fees' => $salesFees,
            'sales_net' => $salesNet,
            'purchase_count' => (int) $purchaseOrders->count(),
            'purchase_gross' => $purchaseGross,
            'purchase_refunds' => $purchaseRefunds,
            'purchase_net' => $purchaseNet,
            'overall_net' => round($salesNet - $purchaseNet, 2),
        ];
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
