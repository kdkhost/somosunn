<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\Certificate;
use App\Models\Course;
use App\Models\Event;
use App\Models\Mentorship;
use App\Models\Order;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Contracts\Cache\Repository as CacheRepository;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DashboardMetricsService
{
    public function panelStats(User $user, bool $fresh = false): array
    {
        return $this->remember(
            $this->panelStatsCacheKey($user),
            fn () => $this->buildPanelStats($user),
            $fresh
        );
    }

    public function adminPayload(User $user, bool $fresh = false): array
    {
        return $this->remember(
            $this->adminPayloadCacheKey($user),
            fn () => $this->buildAdminPayload($user),
            $fresh
        );
    }

    public function warmAllCaches(bool $fresh = false, ?int $userId = null): array
    {
        $usersWarmed = 0;
        $adminCachesWarmed = 0;
        $chunk = max(10, (int) config('dashboard.warm_chunk', 100));

        $query = User::query()->orderBy('id');

        if ($userId !== null) {
            $query->where('id', $userId);
        }

        $query->chunkById($chunk, function ($users) use (&$usersWarmed, &$adminCachesWarmed, $fresh) {
            foreach ($users as $user) {
                $this->panelStats($user, $fresh);
                $usersWarmed++;

                if ($user->isAdmin() || $user->isSuperAdmin()) {
                    $this->adminPayload($user, $fresh);
                    $adminCachesWarmed++;
                }
            }
        });

        return [
            'users_warmed' => $usersWarmed,
            'admin_caches_warmed' => $adminCachesWarmed,
        ];
    }

    private function buildPanelStats(User $user): array
    {
        $plan = $user->activePlan();
        $stats = [
            'courses_count' => 0,
            'orders_paid_count' => 0,
            'orders_paid_total' => 0.0,
            'seller_paid_count' => 0,
            'seller_net_total' => 0.0,
            'community_count' => $this->safeInt(fn () => User::count()),
            'mp_balance' => null,
        ];
        $salesChart = null;

        try {
            if ($user->isAdmin() || $user->isSuperAdmin()) {
                $stats['courses_count'] = (int) Course::count();
                $stats['orders_paid_count'] = (int) Order::where('status', 'paid')->count();
                $stats['orders_paid_total'] = (float) Order::where('status', 'paid')->sum('total_amount');
                $stats['seller_paid_count'] = (int) Order::where('status', 'paid')->count();
                $stats['seller_net_total'] = (float) Order::where('status', 'paid')->sum('total_amount')
                    - (float) Order::where('status', 'paid')->sum('platform_fee_amount');

                $salesChart = $this->buildSalesChart(function (int $month, int $year): int {
                    return (int) Order::where('status', 'paid')
                        ->whereMonth('created_at', $month)
                        ->whereYear('created_at', $year)
                        ->count();
                });

                try {
                    $mpService = new \App\Services\Payment\MercadoPagoService();
                    $stats['mp_balance'] = $mpService->getBalance(null);
                } catch (\Throwable) {
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

                $salesChart = $this->buildSalesChart(function (int $month, int $year) use ($user): int {
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
                    } catch (\Throwable) {
                    }
                }
            }
        } catch (\Throwable) {
        }

        return [
            'plan' => $plan?->name,
            'stats' => $stats,
            'sales_chart' => $salesChart,
        ];
    }

    private function buildAdminPayload(User $user): array
    {
        $isAdmin = $user->isAdmin();
        $months = collect(range(0, 5))
            ->map(fn (int $i) => now()->subMonths($i)->format('M/Y'))
            ->reverse()
            ->values()
            ->toArray();

        $payload = [
            'totalRevenue' => 0,
            'refundedAmount' => 0,
            'totalOrders' => 0,
            'totalUsers' => 0,
            'salesChartData' => array_fill(0, 6, 0),
            'months' => $months,
            'calendarEvents' => collect(),
            'coursesCount' => 0,
            'mentorshipsCount' => 0,
            'eventsCount' => 0,
            'certificatesCount' => 0,
            'pendingJobsCount' => 0,
            'logsCount' => 0,
            'ordersByStatus' => [],
            'usersByMonth' => [],
            'certificatesByMonth' => [],
            'contentDistribution' => [],
            'jobsStatus' => [],
            'customerHealth' => ['Alta' => 0, 'Média' => 0, 'Baixa' => 0],
            'myHealth' => ['level' => 'Alta', 'color' => '#10b981', 'emoji' => '🟢', 'score' => 100],
            'myHealthDetails' => [],
        ];

        try {
            if ($isAdmin) {
                $payload['totalRevenue'] = (float) Order::financialPaid()->sum('total_amount');
                $payload['refundedAmount'] = (float) Order::where('status', 'refunded')->sum('total_amount');
                $payload['totalOrders'] = (int) Order::count();
                $payload['totalUsers'] = (int) User::count();
                $payload['coursesCount'] = (int) Course::count();
                $payload['mentorshipsCount'] = $this->tableExists('mentorships') ? (int) Mentorship::count() : 0;
                $payload['eventsCount'] = $this->tableExists('events') ? (int) Event::count() : 0;
                $payload['certificatesCount'] = $this->tableExists('certificates') ? (int) Certificate::count() : 0;
                $payload['pendingJobsCount'] = $this->tableExists('jobs') ? (int) DB::table('jobs')->count() : 0;
                $payload['logsCount'] = $this->tableExists('activity_logs') ? (int) ActivityLog::count() : 0;

                foreach (User::query()->select('id', 'plan_id', 'plan_expires_at', 'phone', 'occupation', 'bio', 'city', 'state', 'photo', 'company')->cursor() as $listedUser) {
                    $hasPlan = $listedUser->plan_id && (!$listedUser->plan_expires_at || $listedUser->plan_expires_at->isFuture());
                    $isComplete = method_exists($listedUser, 'isProfileComplete') ? $listedUser->isProfileComplete() : false;

                    if (!$hasPlan) {
                        $payload['customerHealth']['Baixa']++;
                    } elseif ($isComplete) {
                        $payload['customerHealth']['Alta']++;
                    } else {
                        $payload['customerHealth']['Média']++;
                    }
                }

                $statusMap = [
                    'paid' => 'Pago',
                    'pending' => 'Pendente',
                    'refunded' => 'Reembolsado',
                    'canceled' => 'Cancelado',
                    'failed' => 'Falhou',
                    'processing' => 'Processando',
                    'completed' => 'Concluído',
                    'active' => 'Ativo',
                    'inactive' => 'Inativo',
                ];

                foreach (Order::selectRaw('status, COUNT(*) as total')->groupBy('status')->pluck('total', 'status')->toArray() as $status => $count) {
                    $payload['ordersByStatus'][$statusMap[$status] ?? ucfirst((string) $status)] = $count;
                }

                $salesChartData = [];
                $usersByMonth = [];
                $certificatesByMonth = [];

                for ($i = 5; $i >= 0; $i--) {
                    $date = now()->subMonths($i);
                    $label = $date->format('M/Y');
                    $salesChartData[] = (float) Order::financialPaid()
                        ->whereMonth('created_at', $date->month)
                        ->whereYear('created_at', $date->year)
                        ->sum('total_amount');
                    $usersByMonth[$label] = (int) User::whereMonth('created_at', $date->month)
                        ->whereYear('created_at', $date->year)
                        ->count();
                    $certificatesByMonth[$label] = $this->tableExists('certificates')
                        ? (int) Certificate::whereMonth('issued_at', $date->month)->whereYear('issued_at', $date->year)->count()
                        : 0;
                }

                $payload['salesChartData'] = $salesChartData;
                $payload['usersByMonth'] = $usersByMonth;
                $payload['certificatesByMonth'] = $certificatesByMonth;
                $payload['contentDistribution'] = [
                    'Cursos' => $payload['coursesCount'],
                    'Mentorias' => $payload['mentorshipsCount'],
                    'Eventos' => $payload['eventsCount'],
                ];
                $payload['jobsStatus'] = [
                    'Pendentes' => $this->tableExists('jobs') ? (int) DB::table('jobs')->count() : 0,
                    'Concluídos' => $this->tableExists('job_batches') ? (int) DB::table('job_batches')->whereNotNull('finished_at')->count() : 0,
                ];
            } else {
                $payload['coursesCount'] = (int) Course::where('user_id', $user->id)->count();
                $payload['mentorshipsCount'] = $this->tableExists('mentorships') ? (int) Mentorship::where('user_id', $user->id)->count() : 0;
                $payload['eventsCount'] = $this->tableExists('event_registrations') ? (int) DB::table('event_registrations')->where('user_id', $user->id)->count() : 0;

                $hasPlan = $user->plan_id && (!$user->plan_expires_at || $user->plan_expires_at->isFuture());
                $isComplete = $user->isProfileComplete();

                if (!$hasPlan) {
                    $payload['myHealth'] = ['level' => 'Baixa', 'color' => '#ef4444', 'emoji' => '🔴', 'score' => 30];
                } elseif ($isComplete) {
                    $payload['myHealth'] = ['level' => 'Alta', 'color' => '#10b981', 'emoji' => '🟢', 'score' => 100];
                } else {
                    $payload['myHealth'] = ['level' => 'Média', 'color' => '#f59e0b', 'emoji' => '🟡', 'score' => 65];
                }

                $payload['myHealthDetails'] = [
                    'plano_ativo' => $hasPlan,
                    'perfil_completo' => $isComplete,
                    'telefone' => !blank($user->phone),
                    'ocupacao' => !blank($user->occupation),
                    'bio' => !blank($user->bio),
                    'cidade_estado' => !blank($user->city) && !blank($user->state),
                    'foto' => !blank($user->photo),
                    'empresa' => !blank($user->company),
                ];
            }

            if ($this->tableExists('events')) {
                $eventsQuery = Event::query();
                if (!$isAdmin) {
                    $eventsQuery->where('published', true);
                }

                $payload['calendarEvents'] = $eventsQuery->get()->map(function (Event $event) use ($isAdmin): array {
                    return [
                        'id' => $event->id,
                        'title' => $event->title,
                        'start' => $event->start_at ? $event->start_at->toIso8601String() : null,
                        'end' => $event->end_at ? $event->end_at->toIso8601String() : null,
                        'url' => $isAdmin ? route('admin.events.edit', $event->id) : null,
                        'backgroundColor' => $event->color ?? '#28a745',
                        'borderColor' => $event->color ?? '#28a745',
                        'allDay' => (bool) $event->all_day,
                    ];
                });
            }
        } catch (\Throwable $e) {
            \Log::error('Erro ao carregar dashboard cacheada: ' . $e->getMessage());
        }

        return $payload;
    }

    private function buildSalesChart(callable $resolver): array
    {
        $months = collect(range(0, 5))->map(fn (int $index) => now()->subMonths(5 - $index)->format('m/Y'));

        return [
            'labels' => $months->map(fn (string $month) => Carbon::createFromFormat('m/Y', $month)->translatedFormat('M/Y'))->values()->all(),
            'data' => $months->map(function (string $month) use ($resolver) {
                [$monthNumber, $year] = explode('/', $month);
                return $resolver((int) $monthNumber, (int) $year);
            })->values()->all(),
        ];
    }

    private function remember(string $key, callable $resolver, bool $fresh): array
    {
        if ($fresh) {
            $this->cache()->forget($key);
        }

        return $this->cache()->remember(
            $key,
            now()->addSeconds(max(5, (int) config('dashboard.cache_ttl_seconds', 30))),
            $resolver
        );
    }

    private function panelStatsCacheKey(User $user): string
    {
        return sprintf('dashboard:panel:stats:v1:user:%d:role:%s:plan:%s', $user->id, $user->role ?? 'member', $user->plan_id ?? 'none');
    }

    private function adminPayloadCacheKey(User $user): string
    {
        return sprintf('dashboard:admin:payload:v1:user:%d:role:%s', $user->id, $user->role ?? 'member');
    }

    private function cache(): CacheRepository
    {
        $store = config('dashboard.cache_store');

        return $store ? Cache::store($store) : Cache::store(config('cache.default'));
    }

    private function safeInt(callable $resolver): int
    {
        try {
            return (int) $resolver();
        } catch (\Throwable) {
            return 0;
        }
    }

    private function tableExists(string $table): bool
    {
        try {
            return Schema::hasTable($table);
        } catch (\Throwable) {
            return false;
        }
    }
}
