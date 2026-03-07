<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\DashboardMetricsService;

class DashboardController extends Controller
{
    public function __construct(private DashboardMetricsService $metrics)
    {
    }

    public function index()
    {
        $payload = $this->metrics->adminPayload(auth()->user());

        if (request()->routeIs('panel.*')) {
            return view('panel.admin.dashboard', $payload);
        }

        return view('admin.dashboard', $payload);
    }

    public function stats()
    {
        return response()->json([
            'success' => true,
        ] + $this->metrics->adminPayload(auth()->user(), request()->boolean('fresh')));
    }

    public function getMpBalance()
    {
        try {
            $service = new \App\Services\Payment\MercadoPagoService();
            $balance = $service->getBalance(null);

            return response()->json([
                'success' => true,
                'balance' => $balance,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'balance' => [
                    'total_amount' => 0,
                    'available_balance' => 0,
                    'unavailable_balance' => 0,
                ],
            ]);
        }
    }
}
