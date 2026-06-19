<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\SalesAnalyticsService;
use App\Support\PdfBranding;
use Dompdf\Dompdf;
use Dompdf\Options;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class SalesReportBuyerController extends Controller
{
    public function index(Request $request, SalesAnalyticsService $salesAnalyticsService)
    {
        $report = $this->buildReport($request, $salesAnalyticsService);

        return view('admin.orders.partials.sales_report_buyers_table', [
            'report' => $report,
        ]);
    }

    public function print(Request $request, SalesAnalyticsService $salesAnalyticsService)
    {
        $report = $this->buildReport($request, $salesAnalyticsService);

        return view('admin.orders.sales_report_buyers_print', [
            'report' => $report,
            'autoPrint' => true,
        ]);
    }

    public function pdf(Request $request, SalesAnalyticsService $salesAnalyticsService)
    {
        $report = $this->buildReport($request, $salesAnalyticsService);
        $html = view('admin.orders.sales_report_buyers_print', [
            'report' => $report,
            'autoPrint' => false,
        ])->render();

        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', true);

        $html = PdfBranding::injectDefaultLogoWatermark($html);

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html, 'UTF-8');
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $filename = 'compradores-item-' . (int) data_get($report, 'item.id') . '-' . now()->format('Ymd-His') . '.pdf';

        return response($dompdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    private function buildReport(Request $request, SalesAnalyticsService $salesAnalyticsService): array
    {
        $itemType = trim((string) $request->input('item_type', ''));
        $itemId = (int) $request->input('item_id', -1);

        abort_unless($itemType !== '' && $itemId >= 0 && preg_match('/^[a-z0-9_-]+$/i', $itemType), 404);

        $period = $this->resolvePeriod($request);
        $report = $salesAnalyticsService->paidItemBuyersReport(
            $itemType,
            $itemId,
            $period['from'],
            $period['to']
        );

        $report['period'] = $period;
        $report['generated_at'] = now();

        return $report;
    }

    /**
     * @return array{from:Carbon,to:Carbon,label:string}
     */
    private function resolvePeriod(Request $request): array
    {
        $period = trim((string) $request->input('period', ''));
        $validPeriods = ['monthly', 'bimonthly', 'quarterly', 'semiannual', 'annual', 'custom'];

        if ($period === '') {
            return $this->resolveDateRange($request);
        }

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
                    return $this->monthlyPeriod();
                }
                break;
            case 'monthly':
            default:
                return $this->monthlyPeriod();
        }

        return [
            'from' => $from,
            'to' => $to,
            'label' => $label,
        ];
    }

    /**
     * @return array{from:Carbon,to:Carbon,label:string}
     */
    private function resolveDateRange(Request $request): array
    {
        $dateFrom = trim((string) $request->input('date_from', ''));
        $dateTo = trim((string) $request->input('date_to', ''));

        if ($dateFrom === '' || $dateTo === '') {
            return $this->monthlyPeriod();
        }

        try {
            $from = Carbon::parse($dateFrom)->startOfDay();
            $to = Carbon::parse($dateTo)->endOfDay();
            if ($to->lt($from)) {
                [$from, $to] = [$to->copy()->startOfDay(), $from->copy()->endOfDay()];
            }
        } catch (\Throwable $e) {
            return $this->monthlyPeriod();
        }

        return [
            'from' => $from,
            'to' => $to,
            'label' => 'Periodo (' . $from->format('d/m/Y') . ' a ' . $to->format('d/m/Y') . ')',
        ];
    }

    /**
     * @return array{from:Carbon,to:Carbon,label:string}
     */
    private function monthlyPeriod(): array
    {
        $now = now();
        $from = $now->copy()->startOfMonth();
        $to = $now->copy()->endOfMonth();

        return [
            'from' => $from,
            'to' => $to,
            'label' => 'Mensal (' . $from->format('m/Y') . ')',
        ];
    }
}
