<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\OrderCancellationService;
use App\Services\OrderRefundService;
use App\Services\OrderSettlementService;
use Dompdf\Dompdf;
use Dompdf\Options;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->input('search', ''));
        $status = trim((string) $request->input('status', ''));
        $saleType = trim((string) $request->input('sale_type', ''));
        $paymentScope = trim((string) $request->input('payment_scope', 'all'));
        $period = $this->resolvePeriod($request);

        $ordersQuery = Order::query()
            ->with(['user', 'items', 'invoice', 'manualApprover'])
            ->latest('id');

        $this->applySearchFilter($ordersQuery, $search);
        $this->applyPeriodFilter($ordersQuery, $period['from'], $period['to']);
        $ordersQuery->ofSaleType($saleType);

        if ($status !== '') {
            $ordersQuery->where('status', $status);
        }

        $this->applyPaymentScopeFilter($ordersQuery, $paymentScope);

        // Otimização: Usar paginação para evitar carregar milhares de registros de uma vez
        $orders = $ordersQuery->paginate(20)->withQueryString();

        $summaryBaseQuery = Order::query()->where('status', 'paid');
        $this->applySearchFilter($summaryBaseQuery, $search);
        $this->applyPeriodFilter($summaryBaseQuery, $period['from'], $period['to']);
        $summaryBaseQuery->ofSaleType($saleType);

        // Otimização: Consolidar queries de resumo
        $summaryMetrics = (clone $summaryBaseQuery)
            ->selectRaw("
                SUM(CASE WHEN (is_manual_approval IS NULL OR is_manual_approval = 0) THEN total_amount ELSE 0 END) as accounted_revenue,
                SUM(CASE WHEN (is_manual_approval IS NULL OR is_manual_approval = 0) THEN 1 ELSE 0 END) as accounted_count,
                SUM(CASE WHEN is_manual_approval = 1 THEN total_amount ELSE 0 END) as manual_revenue,
                SUM(CASE WHEN is_manual_approval = 1 THEN 1 ELSE 0 END) as manual_count,
                COUNT(DISTINCT CASE WHEN is_manual_approval = 1 THEN user_id END) as manual_users_count
            ")
            ->first();

        $accountedRevenue = (float) $summaryMetrics->accounted_revenue;
        $accountedCount = (int) $summaryMetrics->accounted_count;
        $manualRevenue = (float) $summaryMetrics->manual_revenue;
        $manualCount = (int) $summaryMetrics->manual_count;
        $manualUsersCount = (int) $summaryMetrics->manual_users_count;

        return view('admin.orders.index', [
            'orders' => $orders,
            'period' => $period,
            'search' => $search,
            'status' => $status,
            'saleType' => $saleType,
            'saleTypeLabels' => Order::SALE_TYPE_LABELS,
            'paymentScope' => $paymentScope,
            'accountedRevenue' => $accountedRevenue,
            'accountedCount' => $accountedCount,
            'manualRevenue' => $manualRevenue,
            'manualCount' => $manualCount,
            'manualUsersCount' => $manualUsersCount,
            'reportScope' => trim((string) $request->input('report_scope', 'accounted')),
        ]);
    }

    public function show(Order $order)
    {
        $order->load(['user', 'items', 'invoice', 'manualApprover']);
        $canManualApprove = $this->canCurrentUserApproveManually();

        return view('admin.orders.show', compact('order', 'canManualApprove'));
    }

    public function refund(Request $request, Order $order, OrderRefundService $orderRefundService)
    {
        try {
            $amount = $this->parseRefundAmount($request);
            $order = $orderRefundService->refund($order, $amount);

            return back()->with('success', $order->is_fully_refunded
                ? 'Estorno total processado com sucesso.'
                : 'Estorno parcial processado com sucesso.');
        } catch (\Exception $e) {
            return back()->with('error', 'Erro ao processar reembolso: ' . $e->getMessage());
        }
    }

    public function approveManually(Order $order, OrderSettlementService $orderSettlementService)
    {
        if ($order->status === 'paid') {
            return back()->with('error', 'Pedido ja esta pago.');
        }

        if (!$this->canCurrentUserApproveManually()) {
            return back()->with('error', 'Aprovacao manual desabilitada.');
        }

        try {
            $transactionId = 'MANUAL-' . $order->id . '-' . now()->format('YmdHis');

            $orderSettlementService->settleAsPaid($order, [
                'manual_approval' => true,
                'approver_id' => (int) auth()->id(),
                'transaction_id' => $transactionId,
                'payment_method' => 'manual_approval',
                'queue_invoice_email' => true,
                'send_notifications' => true,
                'gateway_data' => [
                    'source' => 'admin_manual_approval',
                    'approved_by' => (int) auth()->id(),
                    'approved_at' => now()->toIso8601String(),
                ],
            ]);
        } catch (\Throwable $e) {
            return back()->with('error', 'Erro ao aprovar manualmente: ' . $e->getMessage());
        }

        return back()->with('success', 'Pedido aprovado manualmente, fatura baixada e e-mails enviados.');
    }

    private function canCurrentUserApproveManually(): bool
    {
        $manualEnabled = (bool) \App\Models\Setting::get('marketplace_manual_approval_enabled', 1);
        if ($manualEnabled) {
            return true;
        }

        return auth()->check() && auth()->user()->isSuperAdmin();
    }

    public function cancel(Order $order, OrderCancellationService $orderCancellationService)
    {
        try {
            $order = $orderCancellationService->cancel($order);

            return back()->with('success', $order->status === 'refunded'
                ? 'Pedido pago estornado com sucesso.'
                : 'Pedido cancelado com sucesso.');
        } catch (\Throwable $e) {
            return back()->with('error', 'Erro ao cancelar pedido: ' . $e->getMessage());
        }
    }

    public function exportReport(Request $request, string $format)
    {
        $format = strtolower(trim($format));
        if (!in_array($format, ['pdf', 'xml', 'csv', 'doc', 'html'], true)) {
            abort(404);
        }

        $reportScope = trim((string) $request->input('report_scope', 'accounted'));
        if (!in_array($reportScope, ['all', 'accounted', 'manual'], true)) {
            $reportScope = 'accounted';
        }

        $search = trim((string) $request->input('search', ''));
        $saleType = trim((string) $request->input('sale_type', ''));
        $period = $this->resolvePeriod($request);

        $ordersQuery = Order::query()
            ->with(['user', 'items', 'seller:id,name,email', 'invoice:id,order_id,number,status', 'manualApprover:id,name'])
            ->where('status', 'paid')
            ->orderByDesc(DB::raw('COALESCE(orders.paid_at, orders.manual_approved_at, orders.created_at)'));

        $this->applySearchFilter($ordersQuery, $search);
        $this->applyPeriodFilter($ordersQuery, $period['from'], $period['to']);
        $ordersQuery->ofSaleType($saleType);
        $this->applyPaymentScopeFilter($ordersQuery, $reportScope);

        $orders = $ordersQuery->get();

        $summaryBaseQuery = Order::query()->where('status', 'paid');
        $this->applySearchFilter($summaryBaseQuery, $search);
        $this->applyPeriodFilter($summaryBaseQuery, $period['from'], $period['to']);
        $summaryBaseQuery->ofSaleType($saleType);

        $summary = [
            'accounted_count' => (int) (clone $summaryBaseQuery)->financialPaid()->count(),
            'accounted_total' => (float) (clone $summaryBaseQuery)->financialPaid()->sum('total_amount'),
            'manual_count' => (int) (clone $summaryBaseQuery)->manualApproved()->count(),
            'manual_total' => (float) (clone $summaryBaseQuery)->manualApproved()->sum('total_amount'),
            'manual_users_count' => (int) (clone $summaryBaseQuery)->manualApproved()->distinct('user_id')->count('user_id'),
            'scope' => $reportScope,
            'period_label' => $period['label'],
            'generated_at' => now(),
        ];

        $filenameBase = 'relatorio-financeiro-' . now()->format('Ymd-His');

        return match ($format) {
            'csv' => $this->exportCsv($orders, $summary, $filenameBase . '.csv'),
            'xml' => $this->exportXml($orders, $summary, $filenameBase . '.xml'),
            'doc' => $this->exportDoc($orders, $summary, $filenameBase . '.doc'),
            'html' => $this->exportHtml($orders, $summary, $filenameBase . '.html'),
            default => $this->exportPdf($orders, $summary, $filenameBase . '.pdf'),
        };
    }

    private function applySearchFilter(Builder $query, string $search): void
    {
        if ($search === '') {
            return;
        }

        $query->where(function (Builder $q) use ($search) {
            $q->where('orders.id', 'like', '%' . $search . '%')
                ->orWhere('orders.transaction_id', 'like', '%' . $search . '%')
                ->orWhereHas('user', function (Builder $userQuery) use ($search) {
                    $userQuery->where('name', 'like', '%' . $search . '%')
                        ->orWhere('email', 'like', '%' . $search . '%');
                });
        });
    }

    private function applyPaymentScopeFilter(Builder $query, string $scope): void
    {
        if ($scope === 'accounted') {
            $query->financialPaid();
            return;
        }

        if ($scope === 'manual') {
            $query->manualApproved();
        }
    }

    private function applyPeriodFilter(Builder $query, Carbon $from, Carbon $to): void
    {
        $query->whereBetween(
            DB::raw('COALESCE(orders.paid_at, orders.manual_approved_at, orders.created_at)'),
            [$from->copy()->startOfDay(), $to->copy()->endOfDay()]
        );
    }

    private function parseRefundAmount(Request $request): ?float
    {
        $rawAmount = trim((string) $request->input('amount', ''));
        if ($rawAmount === '') {
            return null;
        }

        $normalizedAmount = str_replace(',', '.', $rawAmount);
        if (!is_numeric($normalizedAmount)) {
            throw new \InvalidArgumentException('Valor de estorno invalido.');
        }

        return round((float) $normalizedAmount, 2);
    }

    /**
     * @return array{period:string,from:Carbon,to:Carbon,label:string}
     */
    private function resolvePeriod(Request $request): array
    {
        $period = trim((string) $request->input('period', 'monthly'));
        $validPeriods = ['monthly', 'bimonthly', 'quarterly', 'semiannual', 'annual', 'custom'];
        if (!in_array($period, $validPeriods, true)) {
            $period = 'monthly';
        }

        $now = now();
        $from = null;
        $to = null;
        $label = '';

        switch ($period) {
            case 'bimonthly':
                $from = $now->copy()->subMonth()->startOfMonth();
                $to = $now->copy()->endOfMonth();
                $label = 'Bimestral (' . $from->format('d/m/Y') . ' a ' . $to->format('d/m/Y') . ')';
                break;
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
                $label = 'Anual (' . $from->format('Y') . ')';
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
                $label = 'Mensal (' . $now->translatedFormat('F/Y') . ')';
                break;
        }

        if (!$from || !$to) {
            $from = $now->copy()->startOfMonth();
            $to = $now->copy()->endOfMonth();
            $period = 'monthly';
            $label = 'Mensal (' . $now->translatedFormat('F/Y') . ')';
        }

        return [
            'period' => $period,
            'from' => $from,
            'to' => $to,
            'label' => $label,
        ];
    }

    private function exportCsv($orders, array $summary, string $filename): StreamedResponse
    {
        return response()->streamDownload(function () use ($orders, $summary) {
            $handle = fopen('php://output', 'w');
            if (!$handle) {
                return;
            }

            fputcsv($handle, ['Relatorio', 'Financeiro de Pedidos']);
            fputcsv($handle, ['Periodo', $summary['period_label'] ?? '-']);
            fputcsv($handle, ['Gerado em', optional($summary['generated_at'] ?? null)->format('d/m/Y H:i') ?? now()->format('d/m/Y H:i')]);
            fputcsv($handle, ['Escopo', $summary['scope'] ?? 'accounted']);
            fputcsv($handle, []);

            fputcsv($handle, ['Total contabilizado (R$)', number_format((float) ($summary['accounted_total'] ?? 0), 2, '.', '')]);
            fputcsv($handle, ['Pedidos contabilizados', (int) ($summary['accounted_count'] ?? 0)]);
            fputcsv($handle, ['Total aprovacoes manuais (R$)', number_format((float) ($summary['manual_total'] ?? 0), 2, '.', '')]);
            fputcsv($handle, ['Pedidos aprovacao manual', (int) ($summary['manual_count'] ?? 0)]);
            fputcsv($handle, ['Usuarios com aprovacao manual', (int) ($summary['manual_users_count'] ?? 0)]);
            fputcsv($handle, []);

            fputcsv($handle, [
                'Pedido',
                'Data financeira',
                'Tipo',
                'Cliente',
                'E-mail',
                'Telefone',
                'Endereco',
                'Origem',
                'Metodo',
                'Valor bruto',
                'Desconto',
                'Cupom',
                'Total liquido',
                'Fatura',
                'Aprovado manual por',
            ]);

            foreach ($orders as $order) {
                $financialDate = $order->paid_at ?? $order->manual_approved_at ?? $order->created_at;
                $source = $order->is_manual_approval ? 'manual' : 'contabilizado';
                $invoiceLabel = '-';
                if ($order->invoice) {
                    $invoiceLabel = (string) ($order->invoice->number ?: ('#' . $order->invoice->id));
                }

                fputcsv($handle, [
                    '#' . $order->id,
                    $financialDate ? $financialDate->format('d/m/Y H:i') : '-',
                    $order->saleTypeLabel(),
                    (string) ($order->user->name ?? 'Usuario removido'),
                    (string) ($order->user->email ?? ''),
                    (string) ($order->user->phone ?? ''),
                    $order->buyerAddress(),
                    $source,
                    (string) ($order->payment_method ?: $order->gateway ?: '-'),
                    number_format((float) $order->gross_amount, 2, '.', ''),
                    number_format((float) $order->financial_discount_amount, 2, '.', ''),
                    (string) ($order->coupon_code ?: ''),
                    number_format((float) ($order->total_amount ?? 0), 2, '.', ''),
                    $invoiceLabel,
                    (string) ($order->manualApprover->name ?? ''),
                ]);
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    private function exportXml($orders, array $summary, string $filename)
    {
        $xml = new \SimpleXMLElement('<financial_report/>');
        $xml->addChild('period', (string) ($summary['period_label'] ?? '-'));
        $xml->addChild('generated_at', (string) optional($summary['generated_at'] ?? null)->format(DATE_ATOM));
        $xml->addChild('scope', (string) ($summary['scope'] ?? 'accounted'));

        $totals = $xml->addChild('totals');
        $totals->addChild('accounted_total', number_format((float) ($summary['accounted_total'] ?? 0), 2, '.', ''));
        $totals->addChild('accounted_count', (string) ((int) ($summary['accounted_count'] ?? 0)));
        $totals->addChild('manual_total', number_format((float) ($summary['manual_total'] ?? 0), 2, '.', ''));
        $totals->addChild('manual_count', (string) ((int) ($summary['manual_count'] ?? 0)));
        $totals->addChild('manual_users_count', (string) ((int) ($summary['manual_users_count'] ?? 0)));

        $ordersNode = $xml->addChild('orders');
        foreach ($orders as $order) {
            $financialDate = $order->paid_at ?? $order->manual_approved_at ?? $order->created_at;
            $invoiceLabel = '-';
            if ($order->invoice) {
                $invoiceLabel = (string) ($order->invoice->number ?: ('#' . $order->invoice->id));
            }

            $orderNode = $ordersNode->addChild('order');
            $orderNode->addChild('id', (string) $order->id);
            $orderNode->addChild('financial_date', $financialDate ? $financialDate->format(DATE_ATOM) : '');
            $orderNode->addChild('sale_type', htmlspecialchars($order->saleTypeLabel()));
            $orderNode->addChild('customer_name', htmlspecialchars((string) ($order->user->name ?? 'Usuario removido')));
            $orderNode->addChild('customer_email', htmlspecialchars((string) ($order->user->email ?? '')));
            $orderNode->addChild('customer_phone', htmlspecialchars((string) ($order->user->phone ?? '')));
            $orderNode->addChild('customer_address', htmlspecialchars($order->buyerAddress()));
            $orderNode->addChild('source', $order->is_manual_approval ? 'manual' : 'accounted');
            $orderNode->addChild('payment_method', htmlspecialchars((string) ($order->payment_method ?: $order->gateway ?: '-')));
            $orderNode->addChild('gross_amount', number_format((float) $order->gross_amount, 2, '.', ''));
            $orderNode->addChild('discount_amount', number_format((float) $order->financial_discount_amount, 2, '.', ''));
            $orderNode->addChild('coupon_code', htmlspecialchars((string) ($order->coupon_code ?: '')));
            $orderNode->addChild('total_amount', number_format((float) ($order->total_amount ?? 0), 2, '.', ''));
            $orderNode->addChild('invoice_number', htmlspecialchars($invoiceLabel));
            $orderNode->addChild('manual_approver', htmlspecialchars((string) ($order->manualApprover->name ?? '')));
        }

        return response($xml->asXML(), 200, [
            'Content-Type' => 'application/xml; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    private function exportPdf($orders, array $summary, string $filename)
    {
        $html = view('admin.orders.report_print', [
            'orders' => $orders,
            'summary' => $summary,
        ])->render();

        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', true);

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html, 'UTF-8');
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        return response($dompdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    private function exportDoc($orders, array $summary, string $filename)
    {
        $html = view('admin.orders.report_print', [
            'orders' => $orders,
            'summary' => $summary,
        ])->render();

        return response($html, 200, [
            'Content-Type' => 'application/msword; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    private function exportHtml($orders, array $summary, string $filename)
    {
        $html = view('admin.orders.report_print', [
            'orders' => $orders,
            'summary' => $summary,
        ])->render();

        return response($html, 200, [
            'Content-Type' => 'text/html; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }
}
