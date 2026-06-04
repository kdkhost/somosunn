<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\Connection;
use App\Models\PointsLog;
use App\Models\RedeemableItem;
use App\Models\Redemption;
use App\Models\User;
use App\Services\DashboardMetricsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

class DashboardController extends Controller
{
    public function __construct(private DashboardMetricsService $metrics)
    {
    }

    public function stats(Request $request)
    {
        if (!$request->expectsJson() && !$request->ajax() && !$request->wantsJson()) {
            return redirect()->route('panel.dashboard');
        }

        $user = Auth::user();
        $payload = $user ? $this->metrics->panelStats($user, $request->boolean('fresh')) : ['plan' => null, 'stats' => [], 'sales_chart' => null];

        return response()->json(['success' => true] + $payload);
    }

    public function index()
    {
        $user = Auth::user();
        $dashboardStats = $user ? $this->metrics->panelStats($user) : ['plan' => null, 'stats' => []];
        $plan = $user ? $user->activePlan() : null;
        $stats = $dashboardStats['stats'] ?? [];
        $visitMetrics = $dashboardStats['visit_metrics'] ?? [];

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
        } catch (\Throwable) {
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
            'email_verificado' => $user->hasVerifiedEmail(),
            'telefone' => !blank($user->phone),
            'ocupacao' => !blank($user->occupation),
            'bio' => !blank($user->bio),
            'cidade_estado' => !blank($user->city) && !blank($user->state),
            'foto' => !blank($user->photo),
            'empresa' => !blank($user->company),
        ];

        if (
            method_exists($user, 'canSellOnMarketplace')
            && $user->canSellOnMarketplace()
            && $this->supportsSellerHealthMetrics()
        ) {
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
            
            // Otimização: Cache do ranking por 1 hora (ou use um job para calcular periodicamente)
            $rankPosition = Cache::remember('user_rank_' . $user->id, now()->addHour(), function() use ($userPoints) {
                return User::where('points', '>', $userPoints)->count() + 1;
            });

            // Otimização: Usar range de datas ao invés de whereYear/whereMonth
            $startOfMonth = now()->startOfMonth();
            $endOfMonth = now()->endOfMonth();
            
            $pontosEsteMes = (int) PointsLog::where('user_id', $user->id)
                ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
                ->sum('points');
        } catch (\Throwable) {
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
            'pontosEsteMes',
            'visitMetrics'
        ));
    }

    private static array $schemaCache = [];

    private function supportsSellerHealthMetrics(): bool
    {
        $cacheKey = 'supports_seller_health_metrics';
        if (isset(self::$schemaCache[$cacheKey])) {
            return self::$schemaCache[$cacheKey];
        }

        return self::$schemaCache[$cacheKey] = Schema::hasTable('redeemable_items')
            && Schema::hasTable('redemptions')
            && Schema::hasColumn('redeemable_items', 'provider_type')
            && Schema::hasColumn('redeemable_items', 'provider_user_id')
            && Schema::hasColumn('redemptions', 'provider_type')
            && Schema::hasColumn('redemptions', 'provider_user_id')
            && Schema::hasColumn('redemptions', 'estimated_delivery_at')
            && Schema::hasColumn('redemptions', 'tracking_code')
            && Schema::hasColumn('redemptions', 'tracking_url');
    }
}
