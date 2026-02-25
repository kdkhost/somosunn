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
            'community_count' => (int) \App\Models\User::count(),
            'mp_balance' => null, // Saldo MP
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
                    $months = collect(range(0, 5))->map(function ($i) {
                        return now()->subMonths(5 - $i)->format('m/Y');
                    });
                    $labels = $months->map(function ($m) {
                        return \Carbon\Carbon::createFromFormat('m/Y', $m)->translatedFormat('M/Y');
                    });
                    $data = $months->map(function ($m) {
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

                    // Tentar buscar saldo MP do admin/plataforma
                    try {
                        $mpService = new \App\Services\Payment\MercadoPagoService();
                        $stats['mp_balance'] = $mpService->getBalance(null);
                    } catch (\Throwable $e) {
                        // Ignorar erro de saldo
                    }

                } else {
                    $stats['courses_count'] = (int) \App\Models\Course::where('user_id', $user->id)->count();
                    $stats['orders_paid_count'] = (int) \App\Models\Order::where('user_id', $user->id)->where('status', 'paid')->count();
                    $stats['orders_paid_total'] = (float) \App\Models\Order::where('user_id', $user->id)->where('status', 'paid')->sum('total_amount');
                    $stats['seller_paid_count'] = (int) \App\Models\Order::where('seller_id', $user->id)->where('status', 'paid')->count();
                    $stats['seller_net_total'] = (float) max(0, (float) \App\Models\Order::where('seller_id', $user->id)->where('status', 'paid')->sum('total_amount') - (float) \App\Models\Order::where('seller_id', $user->id)->where('status', 'paid')->sum('platform_fee_amount'));
                    // Gráfico: vendas dos últimos 6 meses (do vendedor)
                    $months = collect(range(0, 5))->map(function ($i) {
                        return now()->subMonths(5 - $i)->format('m/Y');
                    });
                    $labels = $months->map(function ($m) {
                        return \Carbon\Carbon::createFromFormat('m/Y', $m)->translatedFormat('M/Y');
                    });
                    $data = $months->map(function ($m) use ($user) {
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

                    // Tentar buscar saldo MP do vendedor (se tiver conta conectada/token)
                    if ($user->canSellOnMarketplace()) {
                        try {
                            $mpService = new \App\Services\Payment\MercadoPagoService();
                            $stats['mp_balance'] = $mpService->getBalance($user->id);
                        } catch (\Throwable $e) {
                            // Erro silencioso se não configurado
                        }
                    }
                }
            }
        } catch (\Throwable $e) {
        }
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

        // --- Sugestões de Networking ---
        $suggestedUsers = collect([]);
        try {
            if ($user && !empty($user->interests)) {
                $myInterests = array_map('trim', explode(',', $user->interests));

                // Buscar usuários que NÃO são o atual e NÃO estão conectados
                $query = \App\Models\User::where('id', '!=', $user->id)
                    ->where('hide_profile', false) // Respeitar privacidade
                    ->where(function ($q) use ($myInterests) {
                        foreach ($myInterests as $interest) {
                            if (!empty($interest)) {
                                $q->orWhere('interests', 'LIKE', "%{$interest}%");
                            }
                        }
                    });

                // Excluir já conectados (requer lógica de conexão)
                // Assumindo que existe método isConnectedWith ou tabela connections
                $connectedIds = \App\Models\Connection::where(function ($q) use ($user) {
                    $q->where('requester_id', $user->id);
                })->orWhere(function ($q) use ($user) {
                    $q->where('requested_id', $user->id);
                })
                    ->pluck('requester_id', 'requested_id')
                    ->flatten()
                    ->unique()
                    ->toArray();

                if (!empty($connectedIds)) {
                    $query->whereNotIn('id', $connectedIds);
                }

                $suggestedUsers = $query->inRandomOrder()->take(4)->get();
            }
        } catch (\Throwable $e) {
            // Falha silenciosa no networking
        }

        $communityCount = (int) \App\Models\User::count();

        // --- Saúde do Membro (Pessoal) ---
        $hasPlan = $user->plan_id && (!$user->plan_expires_at || $user->plan_expires_at->isFuture());
        $isProfileComplete = $user->isProfileComplete();

        if (!$hasPlan) {
            $myHealth = ['level' => 'Baixa', 'color' => '#ef4444', 'emoji' => '🔴', 'score' => 30];
        } elseif ($isProfileComplete) {
            $myHealth = ['level' => 'Alta', 'color' => '#10b981', 'emoji' => '🟢', 'score' => 100];
        } else {
            $myHealth = ['level' => 'Média', 'color' => '#f59e0b', 'emoji' => '🟡', 'score' => 65];
        }

        $myHealthDetails = [
            'plano_ativo' => $hasPlan,
            'perfil_completo' => $isProfileComplete,
            'telefone' => !blank($user->phone),
            'ocupacao' => !blank($user->occupation),
            'bio' => !blank($user->bio),
            'cidade_estado' => !blank($user->city) && !blank($user->state),
            'foto' => !blank($user->photo),
            'empresa' => !blank($user->company),
        ];

        return view('panel.dashboard', compact('user', 'plan', 'stats', 'suggestedUsers', 'communityCount', 'myHealth', 'myHealthDetails'));
    }
}

