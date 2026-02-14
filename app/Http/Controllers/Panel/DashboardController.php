<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Order;
use Illuminate\Support\Facades\Auth;


class DashboardController extends Controller
{

    /**
     * Endpoint AJAX para estatísticas dinâmicas da dashboard
     */
    public function stats()
    {
        $user = Auth::user();
        $plan = $user ? $user->activePlan() : null;
        $stats = [
            'courses_count' => 0,
            'orders_paid_count' => 0,
            'orders_paid_total' => 0.0,
            'seller_paid_count' => 0,
            'seller_net_total' => 0.0,
        ];
        $salesChart = null;
        try {
            if ($user) {
                $isAdmin = method_exists($user, 'isAdmin') && $user->isAdmin();
                $isSuperadmin = method_exists($user, 'isSuperadmin') && $user->isSuperadmin();
                if ($isAdmin || $isSuperadmin) {
                    $stats['courses_count'] = (int) \App\Models\Course::count();
                    $stats['orders_paid_count'] = (int) \App\Models\Order::where('status', 'paid')->count();
                    $stats['orders_paid_total'] = (float) \App\Models\Order::where('status', 'paid')->sum('total_amount');
                    $stats['seller_paid_count'] = (int) \App\Models\Order::where('status', 'paid')->count();
                    $stats['seller_net_total'] = (float) \App\Models\Order::where('status', 'paid')->sum('total_amount') - (float) \App\Models\Order::where('status', 'paid')->sum('platform_fee_amount');
                    // Gráfico: vendas dos últimos 6 meses
                    $months = collect(range(0, 5))->map(function($i) {
                        return now()->subMonths(5 - $i)->format('m/Y');
                    });
                    $labels = $months->map(function($m) {
                        return \Carbon\Carbon::createFromFormat('m/Y', $m)->translatedFormat('M/Y');
                    });
                    $data = $months->map(function($m) {
                        [$month, $year] = explode('/', $m);
                        return (int) \App\Models\Order::where('status', 'paid')
                            ->whereMonth('created_at', $month)
                            ->whereYear('created_at', $year)
                            ->count();
                    });
                    $salesChart = [
                        'labels' => $labels,
                        'data' => $data,
                    ];
                } else {
                    $stats['courses_count'] = (int) \App\Models\Course::where('user_id', $user->id)->count();
                    $stats['orders_paid_count'] = (int) \App\Models\Order::where('user_id', $user->id)->where('status', 'paid')->count();
                    $stats['orders_paid_total'] = (float) \App\Models\Order::where('user_id', $user->id)->where('status', 'paid')->sum('total_amount');
                    $stats['seller_paid_count'] = (int) \App\Models\Order::where('seller_id', $user->id)->where('status', 'paid')->count();
                    $stats['seller_net_total'] = (float) max(0, (float) \App\Models\Order::where('seller_id', $user->id)->where('status', 'paid')->sum('total_amount') - (float) \App\Models\Order::where('seller_id', $user->id)->where('status', 'paid')->sum('platform_fee_amount'));
                    // Gráfico: vendas dos últimos 6 meses (do vendedor)
                    $months = collect(range(0, 5))->map(function($i) {
                        return now()->subMonths(5 - $i)->format('m/Y');
                    });
                    $labels = $months->map(function($m) {
                        return \Carbon\Carbon::createFromFormat('m/Y', $m)->translatedFormat('M/Y');
                    });
                    $data = $months->map(function($m) use ($user) {
                        [$month, $year] = explode('/', $m);
                        return (int) \App\Models\Order::where('seller_id', $user->id)
                            ->where('status', 'paid')
                            ->whereMonth('created_at', $month)
                            ->whereYear('created_at', $year)
                            ->count();
                    });
                    $salesChart = [
                        'labels' => $labels,
                        'data' => $data,
                    ];
                }
            }
        } catch (\Throwable $e) {}
        return response()->json([
            'success' => true,
            'plan' => $plan?->name,
            'stats' => $stats,
            'sales_chart' => $salesChart,
        ]);
    }
    public function index()
    {
        $user = Auth::user();
        $plan = $user ? $user->activePlan() : null;

        $stats = [
            'courses_count' => 0,
            'orders_paid_count' => 0,
            'orders_paid_total' => 0.0,
            'seller_paid_count' => 0,
            'seller_net_total' => 0.0,
        ];

        try {
            if ($user) {
                $isAdmin = method_exists($user, 'isAdmin') && $user->isAdmin();
                $isSuperadmin = method_exists($user, 'isSuperadmin') && $user->isSuperadmin();
                // Admin/superadmin: visão global
                if ($isAdmin || $isSuperadmin) {
                    $stats['courses_count'] = (int) \App\Models\Course::count();
                    $stats['orders_paid_count'] = (int) \App\Models\Order::where('status', 'paid')->count();
                    $stats['orders_paid_total'] = (float) \App\Models\Order::where('status', 'paid')->sum('total_amount');
                    $stats['seller_paid_count'] = (int) \App\Models\Order::where('status', 'paid')->count();
                    $stats['seller_net_total'] = (float) \App\Models\Order::where('status', 'paid')->sum('total_amount') - (float) \App\Models\Order::where('status', 'paid')->sum('platform_fee_amount');
                } else {
                    // Responsável: só vê seus produtos
                    $stats['courses_count'] = (int) \App\Models\Course::where('user_id', $user->id)->count();
                    $stats['orders_paid_count'] = (int) \App\Models\Order::where('user_id', $user->id)->where('status', 'paid')->count();
                    $stats['orders_paid_total'] = (float) \App\Models\Order::where('user_id', $user->id)->where('status', 'paid')->sum('total_amount');
                    $stats['seller_paid_count'] = (int) \App\Models\Order::where('seller_id', $user->id)->where('status', 'paid')->count();
                    $stats['seller_net_total'] = (float) max(0, (float) \App\Models\Order::where('seller_id', $user->id)->where('status', 'paid')->sum('total_amount') - (float) \App\Models\Order::where('seller_id', $user->id)->where('status', 'paid')->sum('platform_fee_amount'));
                }
            }
        } catch (\Throwable $e) {
            // Dashboard não pode quebrar (fallback silencioso)
        }

        return view('panel.dashboard', compact('user', 'plan', 'stats'));
    }
}

