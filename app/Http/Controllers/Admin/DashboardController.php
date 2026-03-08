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
            $message = $e->getMessage();

            // Se for erro de permissão (403), exibe uma mensagem mais amigável
            if (str_contains($message, '403') || str_contains(strtolower($message), 'forbidden')) {
                $message = 'Acesso ao saldo restrito pelo Mercado Pago (Verifique permissões do token).';
            }

            return response()->json([
                'success' => false,
                'message' => $message,
                'balance' => [
                    'total_amount' => 0,
                    'available_balance' => 0,
                    'unavailable_balance' => 0,
                ],
            ]);
        }
    }
}
