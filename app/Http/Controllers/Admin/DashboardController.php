<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        // Membros e Admins acessam, mas a view filtra o conteúdo
        $user = auth()->user();
        $isAdmin = $user ? (bool) $user->isAdmin() : false;
        $isSuperadmin = $user && (($user->role ?? '') === 'superadmin' || ($user->level ?? '') === 'superadmin');

        // Data for charts (Last 6 months)
        $totalRevenue = 0;
        $refundedAmount = 0;
        $totalOrders = 0;
        $totalUsers = 0;
        $salesChartData = [];
        $months = [];

        // Extra KPIs (Admin/SuperAdmin)
        $todayRevenue = 0;
        $monthRevenue = 0;
        $pendingOrders = 0;
        $failedOrders = 0;
        $activeSubscriptions = 0;
        $issuedInvoices = 0;
        $overdueInvoices = 0;
        $activePlans = 0;
        $recentOrders = collect();
        $topSellers = [];

        try {
            if ($isAdmin) {
                $totalRevenue = \App\Models\Order::where('status', 'paid')->sum('total_amount');
                $refundedAmount = \App\Models\Order::where('status', 'refunded')->sum('total_amount');
                $totalOrders = \App\Models\Order::count();
                $totalUsers = \App\Models\User::count();

                for ($i = 5; $i >= 0; $i--) {
                    $date = now()->subMonths($i);
                    $months[] = $date->format('M/Y');
                    $salesChartData[] = \App\Models\Order::where('status', 'paid')
                        ->whereMonth('created_at', $date->month)
                        ->whereYear('created_at', $date->year)
                        ->sum('total_amount');
                }

                // KPIs gerais
                $todayRevenue = \App\Models\Order::where('status', 'paid')
                    ->whereDate('created_at', now()->toDateString())
                    ->sum('total_amount');

                $monthRevenue = \App\Models\Order::where('status', 'paid')
                    ->whereBetween('created_at', [now()->startOfMonth(), now()->endOfMonth()])
                    ->sum('total_amount');

                $pendingOrders = \App\Models\Order::where('status', 'pending')->count();
                $failedOrders = \App\Models\Order::where('status', 'failed')->count();

                $activeSubscriptions = \App\Models\Subscription::query()
                    ->where('status', 'active')
                    ->where(function ($q) {
                        $q->whereNull('ends_at')->orWhere('ends_at', '>', now());
                    })
                    ->count();

                $issuedInvoices = \App\Models\Invoice::query()
                    ->whereIn('status', ['draft', 'issued'])
                    ->count();

                $overdueInvoices = \App\Models\Invoice::query()
                    ->where('status', 'issued')
                    ->whereNotNull('due_at')
                    ->where('due_at', '<', now())
                    ->count();

                $activePlans = \Illuminate\Support\Facades\Schema::hasColumn('plans', 'is_active')
                    ? \App\Models\Plan::query()->where('is_active', true)->count()
                    : \App\Models\Plan::query()->count();

                if ($isSuperadmin) {
                    $recentOrders = \App\Models\Order::query()
                        ->with(['user', 'seller'])
                        ->orderByDesc('id')
                        ->limit(8)
                        ->get();

                    $topRows = \App\Models\Order::query()
                        ->where('status', 'paid')
                        ->whereNotNull('seller_id')
                        ->selectRaw('seller_id, COUNT(*) as orders_count, SUM(total_amount) as total_amount')
                        ->groupBy('seller_id')
                        ->orderByDesc('total_amount')
                        ->limit(5)
                        ->get();

                    $sellerIds = $topRows->pluck('seller_id')->all();
                    $sellers = \App\Models\User::query()
                        ->whereIn('id', $sellerIds)
                        ->get()
                        ->keyBy('id');

                    $topSellers = $topRows->map(function ($row) use ($sellers) {
                        $seller = $sellers->get((int) $row->seller_id);

                        return [
                            'seller_id' => (int) $row->seller_id,
                            'seller_name' => $seller ? (string) $seller->name : ('ID #' . (int) $row->seller_id),
                            'orders_count' => (int) ($row->orders_count ?? 0),
                            'total_amount' => (float) ($row->total_amount ?? 0),
                        ];
                    })->values()->toArray();
                }
            } else {
                // If not admin, we skip sales stats
                $salesChartData = array_fill(0, 6, 0);
                $months = collect(range(0, 5))->map(fn($i) => now()->subMonths($i)->format('M/Y'))->reverse()->values()->toArray();
            }

            // Calendar Events (Unified for all)
            $eventsQuery = \App\Models\Event::query();
            if (!$isAdmin) {
                $eventsQuery->where('published', true);
            }

            $calendarEvents = $eventsQuery
                ->get()
                ->map(function ($event) use ($isAdmin) {
                    return [
                        'id' => $event->id,
                        'title' => $event->title,
                        'start' => $event->start_at ? $event->start_at->toIso8601String() : null,
                        'end' => $event->end_at ? $event->end_at->toIso8601String() : null,
                        'url' => $isAdmin ? route('admin.events.edit', $event->id) : null,
                        'backgroundColor' => $event->color ?? '#28a745',
                        'borderColor' => $event->color ?? '#28a745',
                        'allDay' => $event->all_day
                    ];
                });

        } catch (\Throwable $e) {
            \Log::error('Erro ao carregar dashboard: ' . $e->getMessage());
            // Fallback data
            $totalRevenue = 0;
            $refundedAmount = 0;
            $totalOrders = 0;
            $totalUsers = 0;
            $salesChartData = array_fill(0, 6, 0);
            $months = collect(range(0, 5))->map(fn($i) => now()->subMonths($i)->format('M/Y'))->reverse()->values()->toArray();
            $calendarEvents = [];
            $todayRevenue = 0;
            $monthRevenue = 0;
            $pendingOrders = 0;
            $failedOrders = 0;
            $activeSubscriptions = 0;
            $issuedInvoices = 0;
            $overdueInvoices = 0;
            $activePlans = 0;
            $recentOrders = collect();
            $topSellers = [];
        }

        return view('admin.dashboard', compact(
            'totalRevenue',
            'refundedAmount',
            'totalOrders',
            'totalUsers',
            'salesChartData',
            'months',
            'calendarEvents',
            'todayRevenue',
            'monthRevenue',
            'pendingOrders',
            'failedOrders',
            'activeSubscriptions',
            'issuedInvoices',
            'overdueInvoices',
            'activePlans',
            'recentOrders',
            'topSellers',
            'isSuperadmin'
        ));
    }
}
