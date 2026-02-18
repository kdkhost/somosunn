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

        // Dados para gráficos (Últimos 6 meses)
        $totalRevenue = 0;
        $refundedAmount = 0;
        $totalOrders = 0;
        $totalUsers = 0;
        $salesChartData = [];
        $months = [];


        // Novas métricas globais
        $coursesCount = 0;
        $mentorshipsCount = 0;
        $eventsCount = 0;
        $certificatesCount = 0;
        $pendingJobsCount = 0;
        $logsCount = 0;

        // Dados para gráficos adicionais
        $ordersByStatus = [];
        $usersByMonth = [];
        $certificatesByMonth = [];
        $contentDistribution = [];
        $jobsStatus = [];


        try {
            if ($isAdmin) {
                $totalRevenue = \App\Models\Order::where('status', 'paid')->sum('total_amount');
                $refundedAmount = \App\Models\Order::where('status', 'refunded')->sum('total_amount');
                $totalOrders = \App\Models\Order::count();
                $totalUsers = \App\Models\User::count();

                $coursesCount = \App\Models\Course::count();
                $mentorshipsCount = \App\Models\Mentorship::count();
                $eventsCount = \App\Models\Event::count();
                $certificatesCount = \App\Models\Certificate::count();
                $pendingJobsCount = \DB::table('jobs')->count();
                $logsCount = \App\Models\ActivityLog::count();

                // Gráfico: Pedidos por status
                $rawOrdersByStatus = \App\Models\Order::selectRaw('status, COUNT(*) as total')
                    ->groupBy('status')->pluck('total', 'status')->toArray();

                $statusMap = [
                    'paid' => 'Pago',
                    'pending' => 'Pendente',
                    'refunded' => 'Reembolsado',
                    'canceled' => 'Cancelado',
                    'failed' => 'Falhou',
                    'processing' => 'Processando',
                    'completed' => 'Concluído',
                    'active' => 'Ativo',
                    'inactive' => 'Inativo'
                ];

                $ordersByStatus = [];
                foreach ($rawOrdersByStatus as $status => $count) {
                    $label = $statusMap[$status] ?? ucfirst($status);
                    $ordersByStatus[$label] = $count;
                }

                // Gráfico: Novos usuários por mês (últimos 6 meses)
                $usersByMonth = [];
                for ($i = 5; $i >= 0; $i--) {
                    $date = now()->subMonths($i);
                    $label = $date->format('M/Y');
                    $months[] = $label;
                    $salesChartData[] = \App\Models\Order::where('status', 'paid')
                        ->whereMonth('created_at', $date->month)
                        ->whereYear('created_at', $date->year)
                        ->sum('total_amount');
                    $usersByMonth[$label] = \App\Models\User::whereMonth('created_at', $date->month)
                        ->whereYear('created_at', $date->year)
                        ->count();
                }

                // Gráfico: Certificados emitidos por mês (últimos 6 meses)
                $certificatesByMonth = [];
                for ($i = 5; $i >= 0; $i--) {
                    $date = now()->subMonths($i);
                    $label = $date->format('M/Y');
                    $certificatesByMonth[$label] = \App\Models\Certificate::whereMonth('issued_at', $date->month)
                        ->whereYear('issued_at', $date->year)
                        ->count();
                }

                // Gráfico: Distribuição de conteúdo
                $contentDistribution = [
                    'Cursos' => $coursesCount,
                    'Mentorias' => $mentorshipsCount,
                    'Eventos' => $eventsCount,
                ];

                // Gráfico: Jobs pendentes x concluídos (simples)
                $jobsStatus = [
                    'Pendentes' => \DB::table('jobs')->count(),
                    'Concluídos' => \DB::table('job_batches')->where('finished_at', '!=', null)->count(),
                ];

            } else {
                // Se não for admin, pulamos estatísticas de vendas
                $salesChartData = array_fill(0, 6, 0);
                $months = collect(range(0, 5))->map(fn($i) => now()->subMonths($i)->format('M/Y'))->reverse()->values()->toArray();

                // Métricas pessoais (membro)
                $user = auth()->user();
                $coursesCount = $user->courses()->count();
                $mentorshipsCount = $user->mentorships()->count() ?? 0;
                $eventsCount = $user->eventRegistrations()->count();
            }

            // Eventos do Calendário (Unificado)
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
            // Dados de fallback
            $totalRevenue = 0;
            $refundedAmount = 0;
            $totalOrders = 0;
            $totalUsers = 0;
            $salesChartData = array_fill(0, 6, 0);
            $months = collect(range(0, 5))->map(fn($i) => now()->subMonths($i)->format('M/Y'))->reverse()->values()->toArray();
            $calendarEvents = [];
        }

        if (request()->routeIs('panel.*')) {
            return view('panel.admin.dashboard', compact(
                'totalRevenue',
                'refundedAmount',
                'totalOrders',
                'totalUsers',
                'salesChartData',
                'months',
                'calendarEvents',
                'coursesCount',
                'mentorshipsCount',
                'eventsCount',
                'certificatesCount',
                'pendingJobsCount',
                'logsCount',
                'ordersByStatus',
                'usersByMonth',
                'certificatesByMonth',
                'contentDistribution',
                'jobsStatus'
            ));
        }

        return view('admin.dashboard', compact(
            'totalRevenue',
            'refundedAmount',
            'totalOrders',
            'totalUsers',
            'salesChartData',
            'months',
            'calendarEvents',
            'coursesCount',
            'mentorshipsCount',
            'eventsCount',
            'certificatesCount',
            'pendingJobsCount',
            'logsCount',
            'ordersByStatus',
            'usersByMonth',
            'certificatesByMonth',
            'contentDistribution',
            'jobsStatus',
        ));
    }
    public function getMpBalance()
    {
        try {
            $service = new \App\Services\Payment\MercadoPagoService();
            $balance = $service->getBalance(null); // Platform balance

            return response()->json([
                'success' => true,
                'balance' => $balance
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'balance' => [
                    'total_amount' => 0,
                    'available_balance' => 0,
                    'unavailable_balance' => 0
                ]
            ]);
        }
    }
}
