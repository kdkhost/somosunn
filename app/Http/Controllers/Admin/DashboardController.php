<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $totalRevenue = \App\Models\Order::where('status', 'paid')->sum('total_amount');
        $refundedAmount = \App\Models\Order::where('status', 'refunded')->sum('total_amount');
        $totalOrders = \App\Models\Order::count();
        $totalUsers = \App\Models\User::count();

        // Data for charts (Last 6 months)
        $salesChartData = [];
        $months = [];
        for ($i = 5; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $months[] = $date->format('M/Y');
            $salesChartData[] = \App\Models\Order::where('status', 'paid')
                ->whereMonth('created_at', $date->month)
                ->whereYear('created_at', $date->year)
                ->sum('total_amount');
        }

        return view('admin.dashboard', compact('totalRevenue', 'refundedAmount', 'totalOrders', 'totalUsers', 'salesChartData', 'months'));
    }
}