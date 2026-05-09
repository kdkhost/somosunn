<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\Certificate;
use App\Models\Course;
use App\Models\Event;
use App\Models\Mentorship;
use App\Models\Order;
use App\Models\ServiceVisit;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Contracts\Cache\Repository as CacheRepository;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DashboardMetricsService
{
    public function panelStats(User $user, bool $fresh = false): array
    {
        return $this->remember(
            $this->panelStatsCacheKey($user),
            fn() => $this->buildPanelStats($user),
            $fresh
        );
    }

    public function adminPayload(User $user, bool $fresh = false): array
    {
        return $this->remember(
            $this->adminPayloadCacheKey($user),
            fn() => $this->buildAdminPayload($user),
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
        $visitMetrics = $this->buildOwnerVisitMetrics($user);
        $stats = [
            'courses_count' => 0,
            'orders_paid_count' => 0,
            'orders_paid_total' => 0.0,
            'seller_paid_count' => 0,
            'seller_net_total' => 0.0,
            'community_count' => $this->safeInt(fn() => User::count()),
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
            'visit_metrics' => $visitMetrics,
        ];
    }

    private function buildAdminPayload(User $user): array
    {
        $isAdmin = $user->isAdmin();
        $months = collect(range(0, 5))
            ->map(fn(int $i) => now()->subMonths($i)->format('M/Y'))
            ->reverse()
            ->values()
            ->toArray();

        $payload = [
            'totalRevenue' => 0,
            'revenueToday' => 0,
            'refundedAmount' => 0,
            'totalOrders' => 0,
            'totalUsers' => 0,
            'usersToday' => 0,
            'isAdmin' => $isAdmin,
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
            'serviceVisitsEnabled' => false,
            'serviceVisitSummary' => $this->emptyServiceVisitSummary(),
            'serviceVisitTimeline' => $this->emptyServiceVisitTimeline(),
            'serviceVisitTopItems' => [],
            'serviceVisitOwnerLeaders' => [],
        ];


        try {
            if ($isAdmin) {
                $payload['totalRevenue'] = (float) Order::financialPaid()->sum('total_amount');
                $payload['revenueToday'] = (float) Order::financialPaid()->whereDate('created_at', now()->toDateString())->sum('total_amount');
                $payload['refundedAmount'] = (float) Order::where('status', 'refunded')->sum('total_amount');
                $payload['totalOrders'] = (int) Order::count();
                $payload['totalUsers'] = (int) User::count();
                $payload['usersToday'] = (int) User::whereDate('created_at', now()->toDateString())->count();
                $payload['coursesCount'] = (int) Course::count();
                $payload['mentorshipsCount'] = $this->tableExists('mentorships') ? (int) Mentorship::count() : 0;
                $payload['eventsCount'] = $this->tableExists('events') ? (int) Event::count() : 0;
                $payload['certificatesCount'] = $this->tableExists('certificates') ? (int) Certificate::count() : 0;
                $payload['pendingJobsCount'] = $this->tableExists('jobs') ? (int) DB::table('jobs')->count() : 0;
                $payload['logsCount'] = $this->tableExists('activity_logs') ? (int) ActivityLog::count() : 0;

                // Otimização: calcular saúde do cliente via queries agregadas ao invés de iterar todos
                $now = now()->toDateTimeString();
                $totalUsersCount = (int) User::count();

                // Usuários com plano ativo (plan_id preenchido E (sem expiração OU expiração futura))
                $withActivePlan = (int) User::whereNotNull('plan_id')
                    ->where('plan_id', '>', 0)
                    ->where(function ($q) use ($now) {
                        $q->whereNull('plan_expires_at')
                          ->orWhere('plan_expires_at', '>', $now);
                    })
                    ->count();

                // Usuários com plano ativo E perfil completo (campos essenciais preenchidos)
                $withActivePlanComplete = (int) User::whereNotNull('plan_id')
                    ->where('plan_id', '>', 0)
                    ->where(function ($q) use ($now) {
                        $q->whereNull('plan_expires_at')
                          ->orWhere('plan_expires_at', '>', $now);
                    })
                    ->where(function ($q) {
                        $q->whereNotNull('phone')->where('phone', '!=', '')
                          ->whereNotNull('occupation')->where('occupation', '!=', '')
                          ->whereNotNull('city')->where('city', '!=', '')
                          ->whereNotNull('state')->where('state', '!=', '');
                    })
                    ->count();

                $payload['customerHealth']['Alta'] = $withActivePlanComplete;
                $payload['customerHealth']['Média'] = $withActivePlan - $withActivePlanComplete;
                $payload['customerHealth']['Baixa'] = $totalUsersCount - $withActivePlan;

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

                // Otimização: Pegar totais por mês de uma vez
                $sixMonthsAgo = now()->subMonths(5)->startOfMonth();

                $salesByMonth = Order::financialPaid()
                    ->where('created_at', '>=', $sixMonthsAgo)
                    ->selectRaw("DATE_FORMAT(created_at, '%m/%Y') as month, SUM(total_amount) as total")
                    ->groupBy('month')
                    ->pluck('total', 'month')
                    ->toArray();

                $newUsersByMonth = User::where('created_at', '>=', $sixMonthsAgo)
                    ->selectRaw("DATE_FORMAT(created_at, '%m/%Y') as month, COUNT(*) as total")
                    ->groupBy('month')
                    ->pluck('total', 'month')
                    ->toArray();

                $certsByMonth = $this->tableExists('certificates')
                    ? Certificate::where('issued_at', '>=', $sixMonthsAgo)
                        ->selectRaw("DATE_FORMAT(issued_at, '%m/%Y') as month, COUNT(*) as total")
                        ->groupBy('month')
                        ->pluck('total', 'month')
                        ->toArray()
                    : [];

                $salesChartData = [];
                $usersByMonth = [];
                $certificatesByMonth = [];

                for ($i = 5; $i >= 0; $i--) {
                    $date = now()->subMonths($i);
                    $label = $date->format('m/Y'); // Formato consistente com a query
                    $displayLabel = $date->format('M/Y');

                    $salesChartData[] = (float) ($salesByMonth[$label] ?? 0);
                    $usersByMonth[$displayLabel] = (int) ($newUsersByMonth[$label] ?? 0);
                    $certificatesByMonth[$displayLabel] = (int) ($certsByMonth[$label] ?? 0);
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
                $payload = array_merge($payload, $this->buildGlobalVisitMetrics());
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

    private function buildOwnerVisitMetrics(User $user): array
    {
        if (!$this->supportsServiceVisits()) {
            return $this->emptyOwnerVisitMetrics(false);
        }

        $ownedScopes = $this->ownedServiceScopes($user);
        $ownedProductsCount = collect($ownedScopes)->sum(static fn(array $ids): int => count($ids));

        if ($ownedProductsCount === 0) {
            return $this->emptyOwnerVisitMetrics(true);
        }

        $query = $this->applyVisitScope(ServiceVisit::query(), $ownedScopes);

        return [
            'enabled' => true,
            'owned_products_count' => $ownedProductsCount,
            'total_visits' => (int) (clone $query)->count(),
            'last_24h' => (int) (clone $query)->where('visited_at', '>=', now()->subDay())->count(),
            'by_type' => $this->serviceVisitCountsByType($query),
            'timeline' => $this->buildServiceVisitTimeline($query),
            'top_items' => $this->buildServiceVisitTopItems($query, 5),
        ];
    }

    private function buildGlobalVisitMetrics(): array
    {
        if (!$this->supportsServiceVisits()) {
            return [
                'serviceVisitsEnabled' => false,
                'serviceVisitSummary' => $this->emptyServiceVisitSummary(),
                'serviceVisitTimeline' => $this->emptyServiceVisitTimeline(),
                'serviceVisitTopItems' => [],
                'serviceVisitOwnerLeaders' => [],
            ];
        }

        $query = ServiceVisit::query();
        $byType = $this->serviceVisitCountsByType($query);

        return [
            'serviceVisitsEnabled' => true,
            'serviceVisitSummary' => array_merge(
                $this->emptyServiceVisitSummary(),
                [
                    'total' => (int) (clone $query)->count(),
                    'last_24h' => (int) (clone $query)->where('visited_at', '>=', now()->subDay())->count(),
                    'site' => (int) ($byType['site'] ?? 0),
                    'curso' => (int) ($byType['curso'] ?? 0),
                    'evento' => (int) ($byType['evento'] ?? 0),
                    'mentoria' => (int) ($byType['mentoria'] ?? 0),
                    'palestra' => (int) ($byType['palestra'] ?? 0),
                    'monitored_products' => $this->countMonitoredProducts(),
                ]
            ),
            'serviceVisitTimeline' => $this->buildServiceVisitTimeline($query),
            'serviceVisitTopItems' => $this->buildServiceVisitTopItems($query, 8),
            'serviceVisitOwnerLeaders' => $this->buildServiceVisitOwnerLeaders(),
        ];
    }

    private function emptyOwnerVisitMetrics(bool $enabled = true): array
    {
        return [
            'enabled' => $enabled,
            'owned_products_count' => 0,
            'total_visits' => 0,
            'last_24h' => 0,
            'by_type' => $this->defaultVisitTypeBuckets(),
            'timeline' => $this->emptyServiceVisitTimeline(),
            'top_items' => [],
        ];
    }

    private function emptyServiceVisitSummary(): array
    {
        return [
            'total' => 0,
            'last_24h' => 0,
            'site' => 0,
            'curso' => 0,
            'evento' => 0,
            'mentoria' => 0,
            'palestra' => 0,
            'monitored_products' => 0,
        ];
    }

    private function emptyServiceVisitTimeline(): array
    {
        return [
            'labels' => collect(range(6, 0))
                ->map(fn(int $days) => now()->subDays($days)->translatedFormat('d/m'))
                ->values()
                ->all(),
            'data' => array_fill(0, 7, 0),
        ];
    }

    private function defaultVisitTypeBuckets(): array
    {
        return [
            'site' => 0,
            'curso' => 0,
            'evento' => 0,
            'mentoria' => 0,
            'palestra' => 0,
        ];
    }

    private function countMonitoredProducts(): int
    {
        return $this->safeInt(fn() => Course::count())
            + ($this->tableExists('events') ? $this->safeInt(fn() => Event::count()) : 0)
            + ($this->tableExists('mentorships') ? $this->safeInt(fn() => Mentorship::count()) : 0);
    }

    private function supportsServiceVisits(): bool
    {
        return $this->tableExists('service_visits');
    }

    private function ownedServiceScopes(User $user): array
    {
        return [
            'curso' => $this->tableExists('courses')
                ? Course::query()->where('user_id', $user->id)->pluck('id')->map(static fn($id) => (int) $id)->all()
                : [],
            'evento' => $this->tableExists('events')
                ? Event::query()->where('user_id', $user->id)->pluck('id')->map(static fn($id) => (int) $id)->all()
                : [],
            'mentoria' => $this->tableExists('mentorships')
                ? Mentorship::query()->where('mentor_id', $user->id)->pluck('id')->map(static fn($id) => (int) $id)->all()
                : [],
        ];
    }

    private function applyVisitScope($query, array $scopes)
    {
        $filledScopes = array_filter($scopes, static fn(array $ids): bool => $ids !== []);

        if ($filledScopes === []) {
            return $query->whereRaw('1 = 0');
        }

        return $query->where(function ($outer) use ($filledScopes) {
            foreach ($filledScopes as $type => $ids) {
                $outer->orWhere(function ($inner) use ($type, $ids) {
                    $inner->where('service_type', $type)
                        ->whereIn('service_id', $ids);
                });
            }
        });
    }

    private function serviceVisitCountsByType($query): array
    {
        $counts = $this->defaultVisitTypeBuckets();

        foreach ((clone $query)->selectRaw('service_type, COUNT(*) as total')->groupBy('service_type')->get() as $row) {
            $type = (string) $row->service_type;
            if (array_key_exists($type, $counts)) {
                $counts[$type] = (int) $row->total;
            }
        }

        return $counts;
    }

    private function buildServiceVisitTimeline($query): array
    {
        $labels = [];
        $data = [];

        foreach (range(6, 0) as $days) {
            $date = now()->subDays($days);
            $labels[] = $date->translatedFormat('d/m');
            $data[] = (int) (clone $query)
                ->whereBetween('visited_at', [$date->copy()->startOfDay(), $date->copy()->endOfDay()])
                ->count();
        }

        return [
            'labels' => $labels,
            'data' => $data,
        ];
    }

    private function buildServiceVisitTopItems($query, int $limit = 5): array
    {
        /** @var Collection<int, object> $rows */
        $rows = (clone $query)
            ->selectRaw('service_type, service_id, COUNT(*) as total')
            ->groupBy('service_type', 'service_id')
            ->orderByDesc('total')
            ->limit($limit)
            ->get();

        if ($rows->isEmpty()) {
            return [];
        }

        $catalog = $this->loadServiceCatalog($rows);

        return $rows->map(function ($row) use ($catalog): array {
            $type = (string) $row->service_type;
            $serviceId = $row->service_id !== null ? (string) $row->service_id : null;
            $catalogEntry = $serviceId !== null ? ($catalog[$type][$serviceId] ?? null) : null;

            return [
                'type_key' => $type,
                'type' => $this->serviceTypeLabel($type),
                'label' => $catalogEntry['label'] ?? $this->fallbackServiceLabel($type, $row->service_id),
                'owner_name' => $catalogEntry['owner_name'] ?? null,
                'total' => (int) $row->total,
            ];
        })->values()->all();
    }

    private function buildServiceVisitOwnerLeaders(): array
    {
        if (!$this->supportsServiceVisits()) {
            return [];
        }

        /** @var Collection<int, object> $rows */
        $rows = ServiceVisit::query()
            ->whereIn('service_type', ['curso', 'evento', 'mentoria'])
            ->whereNotNull('service_id')
            ->selectRaw('service_type, service_id, COUNT(*) as total')
            ->groupBy('service_type', 'service_id')
            ->get();

        if ($rows->isEmpty()) {
            return [];
        }

        $catalog = $this->loadServiceCatalog($rows);
        $leaders = [];

        foreach ($rows as $row) {
            $type = (string) $row->service_type;
            $serviceId = (string) $row->service_id;
            $entry = $catalog[$type][$serviceId] ?? null;
            $ownerId = $entry['owner_id'] ?? null;

            if (!$ownerId) {
                continue;
            }

            if (!isset($leaders[$ownerId])) {
                $leaders[$ownerId] = [
                    'owner_id' => (int) $ownerId,
                    'name' => $entry['owner_name'] ?? ('Usuário #' . $ownerId),
                    'total' => 0,
                    'curso' => 0,
                    'evento' => 0,
                    'mentoria' => 0,
                ];
            }

            $leaders[$ownerId]['total'] += (int) $row->total;
            if (isset($leaders[$ownerId][$type])) {
                $leaders[$ownerId][$type] += (int) $row->total;
            }
        }

        usort($leaders, static fn(array $left, array $right): int => $right['total'] <=> $left['total']);

        return array_slice(array_values($leaders), 0, 6);
    }

    private function loadServiceCatalog(Collection $rows): array
    {
        $idsByType = [
            'curso' => [],
            'evento' => [],
            'mentoria' => [],
        ];

        foreach ($rows as $row) {
            $type = (string) $row->service_type;
            if (isset($idsByType[$type]) && $row->service_id !== null) {
                $idsByType[$type][] = (int) $row->service_id;
            }
        }

        $catalog = [
            'curso' => [],
            'evento' => [],
            'mentoria' => [],
        ];
        $ownerIds = [];

        if ($idsByType['curso'] !== [] && $this->tableExists('courses')) {
            foreach (Course::query()->whereIn('id', array_unique($idsByType['curso']))->get(['id', 'title', 'user_id']) as $course) {
                $catalog['curso'][(string) $course->id] = [
                    'label' => (string) $course->title,
                    'owner_id' => $course->user_id ? (int) $course->user_id : null,
                ];
                if ($course->user_id) {
                    $ownerIds[] = (int) $course->user_id;
                }
            }
        }

        if ($idsByType['evento'] !== [] && $this->tableExists('events')) {
            foreach (Event::query()->whereIn('id', array_unique($idsByType['evento']))->get(['id', 'title', 'user_id']) as $event) {
                $catalog['evento'][(string) $event->id] = [
                    'label' => (string) $event->title,
                    'owner_id' => $event->user_id ? (int) $event->user_id : null,
                ];
                if ($event->user_id) {
                    $ownerIds[] = (int) $event->user_id;
                }
            }
        }

        if ($idsByType['mentoria'] !== [] && $this->tableExists('mentorships')) {
            foreach (Mentorship::query()->whereIn('id', array_unique($idsByType['mentoria']))->get(['id', 'title', 'mentor_id']) as $mentorship) {
                $catalog['mentoria'][(string) $mentorship->id] = [
                    'label' => (string) $mentorship->title,
                    'owner_id' => $mentorship->mentor_id ? (int) $mentorship->mentor_id : null,
                ];
                if ($mentorship->mentor_id) {
                    $ownerIds[] = (int) $mentorship->mentor_id;
                }
            }
        }

        $ownerNames = $ownerIds === []
            ? []
            : User::query()->whereIn('id', array_values(array_unique($ownerIds)))->pluck('name', 'id')->all();

        foreach ($catalog as &$items) {
            foreach ($items as &$item) {
                $ownerId = $item['owner_id'] ?? null;
                $item['owner_name'] = $ownerId ? ($ownerNames[$ownerId] ?? null) : null;
            }
        }
        unset($items, $item);

        return $catalog;
    }

    private function serviceTypeLabel(string $type): string
    {
        return match ($type) {
            'curso' => 'Curso',
            'evento' => 'Evento',
            'mentoria' => 'Mentoria',
            'palestra' => 'Palestra',
            'site' => 'Site',
            default => ucfirst($type),
        };
    }

    private function fallbackServiceLabel(string $type, mixed $serviceId): string
    {
        return match ($type) {
            'site' => 'Site institucional',
            'curso' => $serviceId ? 'Curso #' . $serviceId : 'Cursos',
            'evento' => $serviceId ? 'Evento #' . $serviceId : 'Eventos',
            'mentoria' => $serviceId ? 'Mentoria #' . $serviceId : 'Mentorias',
            'palestra' => $serviceId ? 'Palestra #' . $serviceId : 'Palestras',
            default => $serviceId ? ucfirst($type) . ' #' . $serviceId : ucfirst($type),
        };
    }

    private function buildSalesChart(callable $resolver): array
    {
        $months = collect(range(0, 5))->map(fn(int $index) => now()->subMonths(5 - $index)->format('m/Y'));

        return [
            'labels' => $months->map(fn(string $month) => Carbon::createFromFormat('m/Y', $month)->translatedFormat('M/Y'))->values()->all(),
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
        return sprintf('dashboard:panel:stats:v2:user:%d:role:%s:plan:%s', $user->id, $user->role ?? 'member', $user->plan_id ?? 'none');
    }

    private function adminPayloadCacheKey(User $user): string
    {
        return sprintf('dashboard:admin:payload:v2:user:%d:role:%s', $user->id, $user->role ?? 'member');
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
