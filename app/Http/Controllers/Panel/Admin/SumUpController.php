<?php

namespace App\Http\Controllers\Panel\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\SumUpTransaction;
use App\Services\OrderRefundService;
use App\Services\Payment\SumUpService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SumUpController extends Controller
{
    public function index(Request $request): View
    {
        $query = SumUpTransaction::with(['order.user'])
            ->orderByDesc('created_at');

        if ($request->filled('search')) {
            $term = $request->search;
            $query->where(function ($q) use ($term) {
                $q->where('checkout_id', 'like', "%{$term}%")
                    ->orWhere('transaction_id', 'like', "%{$term}%")
                    ->orWhereHas('order.user', fn($u) => $u->where('name', 'like', "%{$term}%")
                        ->orWhere('email', 'like', "%{$term}%"));
            });
        }

        if ($request->filled('status')) {
            $query->where('status', strtoupper($request->status));
        }

        if ($request->filled('payment_type')) {
            $query->where('payment_type', strtoupper($request->payment_type));
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $transactions = $query->paginate(20)->withQueryString();

        $totals = [
            'paid'     => SumUpTransaction::where('status', 'PAID')->sum('amount'),
            'pending'  => SumUpTransaction::where('status', 'PENDING')->count(),
            'failed'   => SumUpTransaction::where('status', 'FAILED')->count(),
            'refunded' => SumUpTransaction::where('status', 'REFUNDED')->sum('amount'),
        ];

        return view('panel.admin.sumup.index', compact('transactions', 'totals'));
    }

    public function show(SumUpTransaction $sumupTransaction): View
    {
        $sumupTransaction->load(['order.user', 'order.items', 'webhookLogs']);
        return view('panel.admin.sumup.show', ['transaction' => $sumupTransaction]);
    }

    public function refund(Request $request, Order $order, OrderRefundService $refundService): RedirectResponse
    {
        try {
            $rawAmount = trim((string) $request->input('amount', ''));
            $amount    = $rawAmount !== '' ? round((float) str_replace(',', '.', $rawAmount), 2) : null;

            $refundService->refund($order, $amount);

            return back()->with('success', 'Reembolso SumUp processado com sucesso.');
        } catch (\Exception $e) {
            return back()->with('error', 'Erro ao processar reembolso: ' . $e->getMessage());
        }
    }

    public function report(Request $request): View
    {
        $period    = $request->input('period', 'monthly');
        $dateFrom  = $request->input('date_from', now()->startOfMonth()->toDateString());
        $dateTo    = $request->input('date_to', now()->toDateString());

        $transactions = SumUpTransaction::where('status', 'PAID')
            ->whereBetween('created_at', [$dateFrom . ' 00:00:00', $dateTo . ' 23:59:59'])
            ->get();

        $feePercentage = (float) config('payments.sumup.fee_percentage', 2.75);
        $feeFixed      = (float) config('payments.sumup.fee_fixed', 0);

        $grossRevenue = $transactions->sum('amount');
        $fees         = $transactions->sum(fn($t) => round(($t->amount * $feePercentage / 100) + $feeFixed, 2));
        $netRevenue   = round($grossRevenue - $fees, 2);

        $refundedAmount = SumUpTransaction::where('status', 'REFUNDED')
            ->whereBetween('created_at', [$dateFrom . ' 00:00:00', $dateTo . ' 23:59:59'])
            ->sum('amount');

        // Dados para gráfico (agrupados por dia)
        $chartData = SumUpTransaction::where('status', 'PAID')
            ->whereBetween('created_at', [$dateFrom . ' 00:00:00', $dateTo . ' 23:59:59'])
            ->selectRaw('DATE(created_at) as date, SUM(amount) as total, COUNT(*) as count')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return view('panel.admin.sumup.report', compact(
            'transactions', 'grossRevenue', 'fees', 'netRevenue',
            'refundedAmount', 'chartData', 'dateFrom', 'dateTo', 'period'
        ));
    }

    public function exportReport(Request $request): StreamedResponse
    {
        $dateFrom = $request->input('date_from', now()->startOfMonth()->toDateString());
        $dateTo   = $request->input('date_to', now()->toDateString());

        $transactions = SumUpTransaction::with(['order.user'])
            ->whereBetween('created_at', [$dateFrom . ' 00:00:00', $dateTo . ' 23:59:59'])
            ->orderByDesc('created_at')
            ->get();

        $filename = 'sumup-relatorio-' . $dateFrom . '-a-' . $dateTo . '.csv';

        return response()->streamDownload(function () use ($transactions) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['ID', 'Checkout ID', 'Transaction ID', 'Comprador', 'Email', 'Valor', 'Método', 'Status', 'Data']);

            foreach ($transactions as $t) {
                fputcsv($handle, [
                    $t->id,
                    $t->checkout_id,
                    $t->transaction_id ?? '-',
                    $t->order?->user?->name ?? '-',
                    $t->order?->user?->email ?? '-',
                    number_format($t->amount, 2, '.', ''),
                    $t->payment_type,
                    $t->status,
                    $t->created_at->format('d/m/Y H:i'),
                ]);
            }

            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv']);
    }

    public function testConnection(): JsonResponse
    {
        $valid = app(SumUpService::class)->validateCredentials();

        return response()->json([
            'success' => $valid,
            'message' => $valid
                ? 'Conexao com SumUp estabelecida com sucesso!'
                : 'Falha na conexao. Verifique as credenciais SumUp.',
        ]);
    }
}
