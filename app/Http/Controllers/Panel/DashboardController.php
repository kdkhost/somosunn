<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\Connection;
use App\Models\Course;
use App\Models\Order;
use App\Models\PointsLog;
use App\Models\RedeemableItem;
use App\Models\Redemption;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
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
            'community_count' => (int) User::count(),
            'mp_balance' => null,
        ];
        $salesChart = null;

        try {
            if ($user) {
                $isAdmin = method_exists($user, 'isAdmin') && $user->isAdmin();
                $isSuperadmin = method_exists($user, 'isSuperadmin') && $user->isSuperadmin();

                if ($isAdmin || $isSuperadmin) {
                    $stats['courses_count'] = (int) Course::count();
                    $stats['orders_paid_count'] = (int) Order::where('status', 'paid')->count();
                    $stats['orders_paid_total'] = (float) Order::where('status', 'paid')->sum('total_amount');
                    $stats['seller_paid_count'] = (int) Order::where('status', 'paid')->count();
                    $stats['seller_net_total'] = (float) Order::where('status', 'paid')->sum('total_amount')
                        - (float) Order::where('status', 'paid')->sum('platform_fee_amount');

                    $salesChart = $this->buildSalesChart(function ($month, $year) {
                        return (int) Order::where('status', 'paid')
                            ->whereMonth('created_at', $month)
                            ->whereYear('created_at', $year)
                            ->count();
                    });

                    try {
                        $mpService = new \App\Services\Payment\MercadoPagoService();
                        $stats['mp_balance'] = $mpService->getBalance(null);
                    } catch (\Throwable $e) {
                    }
                } else {
                    $stats['courses_count'] = (int) Course::where('user_id', $user->id)->count();
                    $stats['orders_paid_count'] = (int) Order::where('user_id', $user->id)->where('status', 'paid')->count();
                    $stats['orders_paid_total'] = (float) Order::where('user_id', $user->id)->where('status', 'paid')->sum('total_amount');
                    $stats['seller_paid_count'] = (int) Order::where('seller_id', $user->id)->where('status', 'paid')->count();
                    $stats['seller_net_total'] = (float) max(
                        0,
                        (float) Order::where('seller_id', $user->id)->where('status', 'paid')->sum('total_amount')
                        - (float) Order::where('seller_id', $user->id)->where('status', 'paid')->sum('platform_fee_amount')
                    );

                    $salesChart = $this->buildSalesChart(function ($month, $year) use ($user) {
                        return (int) Order::where('seller_id', $user->id)
                            ->where('status', 'paid')
                            ->whereMonth('created_at', $month)
                            ->whereYear('created_at', $year)
                            ->count();
                    });

                    if ($user->canSellOnMarketplace()) {
                        try {
                            $mpService = new \App\Services\Payment\MercadoPagoService();
                            $stats['mp_balance'] = $mpService->getBalance($user->id);
                        } catch (\Throwable $e) {
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

                if ($isAdmin || $isSuperadmin) {
                    $stats['courses_count'] = (int) Course::count();
                    $stats['orders_paid_count'] = (int) Order::where('status', 'paid')->count();
                    $stats['orders_paid_total'] = (float) Order::where('status', 'paid')->sum('total_amount');
                    $stats['seller_paid_count'] = (int) Order::where('status', 'paid')->count();
                    $stats['seller_net_total'] = (float) Order::where('status', 'paid')->sum('total_amount')
                        - (float) Order::where('status', 'paid')->sum('platform_fee_amount');
                } else {
                    $stats['courses_count'] = (int) Course::where('user_id', $user->id)->count();
                    $stats['orders_paid_count'] = (int) Order::where('user_id', $user->id)->where('status', 'paid')->count();
                    $stats['orders_paid_total'] = (float) Order::where('user_id', $user->id)->where('status', 'paid')->sum('total_amount');
                    $stats['seller_paid_count'] = (int) Order::where('seller_id', $user->id)->where('status', 'paid')->count();
                    $stats['seller_net_total'] = (float) max(
                        0,
                        (float) Order::where('seller_id', $user->id)->where('status', 'paid')->sum('total_amount')
                        - (float) Order::where('seller_id', $user->id)->where('status', 'paid')->sum('platform_fee_amount')
                    );
                }
            }
        } catch (\Throwable $e) {
        }

        $suggestedUsers = collect([]);
        try {
            if ($user && !empty($user->interests)) {
                $myInterests = array_map('trim', explode(',', (string) $user->interests));

                $query = User::where('id', '!=', $user->id)
                    ->where('hide_profile', false)
                    ->where(function ($inner) use ($myInterests) {
                        foreach ($myInterests as $interest) {
                            if ($interest !== '') {
                                $inner->orWhere('interests', 'LIKE', '%' . $interest . '%');
                            }
                        }
                    });

                $connectedIds = Connection::where(function ($inner) use ($user) {
                    $inner->where('requester_id', $user->id);
                })->orWhere(function ($inner) use ($user) {
                    $inner->where('requested_id', $user->id);
                })
                    ->pluck('requester_id', 'requested_id')
                    ->flatten()
                    ->unique()
                    ->toArray();

                if ($connectedIds !== []) {
                    $query->whereNotIn('id', $connectedIds);
                }

                $suggestedUsers = $query->inRandomOrder()->take(4)->get();
            }
        } catch (\Throwable $e) {
        }

        $communityCount = (int) User::count();

        $hasPlan = $user->plan_id && (!$user->plan_expires_at || $user->plan_expires_at->isFuture());
        $isProfileComplete = $user->isProfileComplete();
        $sellerHealthChecks = [];

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

        if (method_exists($user, 'canSellOnMarketplace') && $user->canSellOnMarketplace()) {
            $sellerItemsQuery = RedeemableItem::query()
                ->where('provider_type', 'seller')
                ->where('provider_user_id', $user->id);

            $sellerRedemptionsQuery = Redemption::query()
                ->where('provider_type', 'seller')
                ->where('provider_user_id', $user->id);

            $catalogExists = (clone $sellerItemsQuery)->exists();
            $overdueDeliveries = (clone $sellerRedemptionsQuery)
                ->whereIn('status', ['pending', 'processing', 'shipped'])
                ->whereNotNull('estimated_delivery_at')
                ->where('estimated_delivery_at', '<', now())
                ->count();
            $shippedWithoutTracking = (clone $sellerRedemptionsQuery)
                ->where('status', 'shipped')
                ->where(function ($query) {
                    $query->whereNull('tracking_code')->whereNull('tracking_url');
                })
                ->count();

            $myHealthDetails['catalogo_resgate_ativo'] = $catalogExists;
            $myHealthDetails['entregas_sem_atraso'] = $overdueDeliveries === 0;
            $myHealthDetails['rastreio_configurado'] = $shippedWithoutTracking === 0;

            $sellerHealthChecks = [
                ['key' => 'catalogo_resgate_ativo', 'label' => 'Catálogo de Resgates', 'icon' => 'fa-gift'],
                ['key' => 'entregas_sem_atraso', 'label' => 'Entregas sem Atraso', 'icon' => 'fa-truck-fast'],
                ['key' => 'rastreio_configurado', 'label' => 'Rastreio Atualizado', 'icon' => 'fa-location-dot'],
            ];

            if ($overdueDeliveries > 0) {
                $myHealth = ['level' => 'Baixa', 'color' => '#ef4444', 'emoji' => '🔴', 'score' => min((int) $myHealth['score'], 40)];
            } elseif (!$catalogExists) {
                $myHealth = ['level' => 'Média', 'color' => '#f59e0b', 'emoji' => '🟡', 'score' => min((int) $myHealth['score'], 70)];
            } elseif ((int) $myHealth['score'] < 100) {
                $myHealth['score'] = min(100, (int) $myHealth['score'] + 5);
            }
        }

        $userPoints = 0;
        $rankPosition = 0;
        $pontosEsteMes = 0;
        try {
            $userPoints = (int) ($user->points ?? 0);
            $rankPosition = User::where('points', '>', $userPoints)->count() + 1;
            $pontosEsteMes = (int) PointsLog::where('user_id', $user->id)
                ->whereYear('created_at', now()->year)
                ->whereMonth('created_at', now()->month)
                ->sum('points');
        } catch (\Throwable $e) {
        }

        return view('panel.dashboard', compact(
            'user',
            'plan',
            'stats',
            'suggestedUsers',
            'communityCount',
            'myHealth',
            'myHealthDetails',
            'sellerHealthChecks',
            'userPoints',
            'rankPosition',
            'pontosEsteMes'
        ));
    }

    private function buildSalesChart(callable $resolver): array
    {
        $months = collect(range(0, 5))->map(fn ($index) => now()->subMonths(5 - $index)->format('m/Y'));

        return [
            'labels' => $months->map(fn ($month) => \Carbon\Carbon::createFromFormat('m/Y', $month)->translatedFormat('M/Y')),
            'data' => $months->map(function ($month) use ($resolver) {
                [$monthNumber, $year] = explode('/', $month);
                return $resolver((int) $monthNumber, (int) $year);
            }),
        ];
    }
}
