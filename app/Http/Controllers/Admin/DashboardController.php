<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        return "<h1>DASHBOARD CONTROLLER REACHED</h1>If you see this, the Middleware is fine. The error is in the View/Blade.";
        
        /*
        try {
            // ... (original logic disabled)

            $refundedAmount = \App\Models\Order::where('status', 'refunded')->sum('total_amount');
            $totalOrders = \App\Models\Order::count();
            $totalUsers = \App\Models\User::count();

            // Data for charts (Last 6 months)
            for ($i = 5; $i >= 0; $i--) {
                $date = now()->subMonths($i);
                $months[] = $date->format('M/Y');
                $salesChartData[] = \App\Models\Order::where('status', 'paid')
                    ->whereMonth('created_at', $date->month)
                    ->whereYear('created_at', $date->year)
                    ->sum('total_amount');
            }
        } catch (\Throwable $e) {
            \Log::error('Erro ao carregar dashboard: ' . $e->getMessage());
            // Fallback data
            $totalRevenue = 0; $refundedAmount = 0; $totalOrders = 0; $totalUsers = 0;
            $salesChartData = array_fill(0, 6, 0);
            $months = collect(range(0, 5))->map(fn($i) => now()->subMonths($i)->format('M/Y'))->reverse()->values()->toArray();
        }

        return view('admin.dashboard', compact('totalRevenue', 'refundedAmount', 'totalOrders', 'totalUsers', 'salesChartData', 'months'));
    }
}