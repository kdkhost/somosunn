<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        // Membros e Admins acessam, mas a view filtra o conteúdo
        $isAdmin = auth()->user()->isAdmin();

        try {
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

            // Calendar Events (Unified for all)
            $calendarEvents = \App\Models\Event::where('published', true)
                ->get()
                ->map(function ($event) {
                    return [
                        'title' => $event->title,
                        'start' => $event->start_at->toIso8601String(),
                        'end' => $event->end_at ? $event->end_at->toIso8601String() : null,
                        'url' => route('events.show', $event->id),
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
        }

        return view('admin.dashboard', compact('totalRevenue', 'refundedAmount', 'totalOrders', 'totalUsers', 'salesChartData', 'months', 'calendarEvents'));
    }
}