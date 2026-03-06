<?php

namespace App\Services;

use App\Models\PointsLog;
use App\Models\ReferralLinkEvent;
use App\Models\ReferralLinkVisit;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReferralAnalyticsService
{
    public function trackingAvailable(): bool
    {
        return Schema::hasTable('referral_link_visits') && Schema::hasTable('referral_link_events');
    }

    public function buildDashboardPayload(?int $referrerUserId = null, int $visitLimit = 10): array
    {
        $trackingSummary = $this->emptyTrackingSummary();
        $trackingChannels = collect();
        $trackedVisits = collect();
        $trackingDailyChart = $this->emptyDailyTrackingChart();
        $trackingAcquisitionChart = $this->emptyChannelTrackingChart();
        $trackingSharingChart = $this->emptySharingTrackingChart();
        $trackingStatusMessage = 'Atualização automática a cada 5 segundos.';
        $trackingStatusTone = 'success';

        if ($this->trackingAvailable()) {
            $visitsQuery = $this->visitsQuery($referrerUserId);
            $eventsQuery = $this->eventsQuery($referrerUserId);

            $trackingSummary = [
                'clicks' => (int) (clone $visitsQuery)->sum('clicks_count'),
                'visits' => (int) (clone $visitsQuery)->count(),
                'pageviews' => (int) (clone $visitsQuery)->sum('pageviews_count'),
                'registrations' => (int) (clone $visitsQuery)->whereNotNull('registered_user_id')->count(),
                'checkout_starts' => (int) (clone $visitsQuery)->sum('checkout_started_count'),
                'purchases' => (int) (clone $visitsQuery)->sum('purchases_count'),
                'revenue' => round((float) (clone $visitsQuery)->sum('total_revenue_amount'), 2),
                'shares' => (int) (clone $eventsQuery)->where('event_type', 'share')->count(),
                'reshares' => (int) (clone $eventsQuery)->where('event_type', 'reshare')->count(),
                'copies' => (int) (clone $eventsQuery)->where('event_type', 'copy')->count(),
                'registration_conversion' => 0,
                'purchase_conversion' => 0,
            ];

            $trackingSummary['registration_conversion'] = $trackingSummary['visits'] > 0
                ? (int) round(($trackingSummary['registrations'] / $trackingSummary['visits']) * 100)
                : 0;
            $trackingSummary['purchase_conversion'] = $trackingSummary['visits'] > 0
                ? (int) round(($trackingSummary['purchases'] / $trackingSummary['visits']) * 100)
                : 0;

            $trackingChannels = $this->buildSharingChannelBreakdown($referrerUserId);
            $trackingDailyChart = $this->buildDailyTrackingChart($referrerUserId);
            $trackingAcquisitionChart = $this->buildAcquisitionChannelChart($referrerUserId);
            $trackingSharingChart = $this->buildSharingChannelChart($trackingChannels);
            $trackedVisits = $this->latestVisits($referrerUserId, $visitLimit);
        } else {
            $trackingStatusMessage = 'O rastreio detalhado ainda não está ativo neste ambiente. Rode as migrations para criar as tabelas de cliques, visitas e eventos.';
            $trackingStatusTone = 'warning';
        }

        return [
            'trackingAvailable' => $this->trackingAvailable(),
            'trackingSummary' => $trackingSummary,
            'trackingChannels' => $trackingChannels,
            'trackedVisits' => $trackedVisits,
            'trackedVisitsFeed' => $this->serializeTrackedVisits($trackedVisits),
            'trackingDailyChart' => $trackingDailyChart,
            'trackingAcquisitionChart' => $trackingAcquisitionChart,
            'trackingSharingChart' => $trackingSharingChart,
            'trackingStatusMessage' => $trackingStatusMessage,
            'trackingStatusTone' => $trackingStatusTone,
            'trackingUpdatedAt' => now()->toIso8601String(),
            'trackingUpdatedAtLabel' => now()->format('d/m/Y H:i:s'),
        ];
    }

    public function latestVisits(?int $referrerUserId = null, int $limit = 10): Collection
    {
        if (!$this->trackingAvailable()) {
            return collect();
        }

        return $this->visitsQuery($referrerUserId)
            ->with('registeredUser:id,name,email,photo')
            ->latest('first_visited_at')
            ->limit($limit)
            ->get();
    }

    public function detailedEventsPaginator(?int $referrerUserId = null, int $perPage = 25, string $pageName = 'events_page'): LengthAwarePaginator
    {
        if (!$this->trackingAvailable()) {
            return $this->emptyPaginator($perPage, $pageName);
        }

        $paginator = $this->eventsQuery($referrerUserId)
            ->with([
                'visit.registeredUser:id,name,email',
                'referrer:id,name,email,referral_code',
                'registeredUser:id,name,email',
                'actor:id,name,email',
            ])
            ->latest('occurred_at')
            ->paginate($perPage, ['*'], $pageName)
            ->withQueryString();

        $paginator->setCollection(
            $paginator->getCollection()->map(function (ReferralLinkEvent $event) {
                return (object) $this->serializeDetailedEvent($event);
            })
        );

        return $paginator;
    }

    public function buildChannelFunnels(?int $referrerUserId = null, int $limit = 10): Collection
    {
        if (!$this->trackingAvailable()) {
            return collect();
        }

        return $this->visitsQuery($referrerUserId)
            ->select([
                'utm_source',
                'referrer_url',
                'registered_user_id',
                'clicks_count',
                'pageviews_count',
                'checkout_started_count',
                'purchases_count',
                'total_revenue_amount',
            ])
            ->get()
            ->groupBy(fn (ReferralLinkVisit $visit) => $this->resolveAcquisitionChannelLabel($visit))
            ->map(function (Collection $group, string $label) {
                $visits = (int) $group->count();
                $registrations = (int) $group->whereNotNull('registered_user_id')->count();
                $purchases = (int) $group->sum('purchases_count');

                return (object) [
                    'channel' => $label,
                    'clicks' => (int) $group->sum('clicks_count'),
                    'visits' => $visits,
                    'pageviews' => (int) $group->sum('pageviews_count'),
                    'registrations' => $registrations,
                    'checkouts' => (int) $group->sum('checkout_started_count'),
                    'purchases' => $purchases,
                    'revenue' => round((float) $group->sum('total_revenue_amount'), 2),
                    'registration_conversion' => $visits > 0 ? min(100, (int) round(($registrations / $visits) * 100)) : 0,
                    'purchase_conversion' => $visits > 0 ? min(100, (int) round(($purchases / $visits) * 100)) : 0,
                ];
            })
            ->sortByDesc(fn ($item) => sprintf('%012d-%012d-%012d', (int) $item->purchases, (int) round($item->revenue * 100), (int) $item->visits))
            ->take($limit)
            ->values();
    }

    public function affiliateLeaderboard(int $perPage = 15, string $pageName = 'affiliates_page'): LengthAwarePaginator
    {
        if (!$this->trackingAvailable()) {
            return $this->emptyPaginator($perPage, $pageName);
        }

        $visitsAggregate = ReferralLinkVisit::query()
            ->selectRaw('referrer_user_id, SUM(clicks_count) as clicks, COUNT(*) as visits, SUM(pageviews_count) as pageviews, COUNT(registered_user_id) as registrations, SUM(checkout_started_count) as checkouts, SUM(purchases_count) as purchases, SUM(total_revenue_amount) as revenue, MAX(last_visited_at) as last_activity_at')
            ->whereNotNull('referrer_user_id')
            ->groupBy('referrer_user_id');

        $eventsAggregate = ReferralLinkEvent::query()
            ->selectRaw("
                referrer_user_id,
                SUM(CASE WHEN event_type = 'share' THEN 1 ELSE 0 END) as shares,
                SUM(CASE WHEN event_type = 'reshare' THEN 1 ELSE 0 END) as reshares,
                SUM(CASE WHEN event_type = 'copy' THEN 1 ELSE 0 END) as copies
            ")
            ->whereNotNull('referrer_user_id')
            ->groupBy('referrer_user_id');

        $pointsAggregate = PointsLog::query()
            ->selectRaw('user_id as referrer_user_id, SUM(points) as referral_points')
            ->where('action_key', 'referral')
            ->groupBy('user_id');

        $paginator = User::query()
            ->leftJoinSub($visitsAggregate, 'visit_aggregate', fn ($join) => $join->on('users.id', '=', 'visit_aggregate.referrer_user_id'))
            ->leftJoinSub($eventsAggregate, 'event_aggregate', fn ($join) => $join->on('users.id', '=', 'event_aggregate.referrer_user_id'))
            ->leftJoinSub($pointsAggregate, 'points_aggregate', fn ($join) => $join->on('users.id', '=', 'points_aggregate.referrer_user_id'))
            ->where(function ($query) {
                $query->whereNotNull('visit_aggregate.referrer_user_id')
                    ->orWhereNotNull('event_aggregate.referrer_user_id')
                    ->orWhereNotNull('points_aggregate.referrer_user_id');
            })
            ->select([
                'users.id',
                'users.name',
                'users.email',
                'users.photo',
                'users.referral_code',
                DB::raw('COALESCE(visit_aggregate.clicks, 0) as clicks'),
                DB::raw('COALESCE(visit_aggregate.visits, 0) as visits'),
                DB::raw('COALESCE(visit_aggregate.pageviews, 0) as pageviews'),
                DB::raw('COALESCE(visit_aggregate.registrations, 0) as registrations'),
                DB::raw('COALESCE(visit_aggregate.checkouts, 0) as checkouts'),
                DB::raw('COALESCE(visit_aggregate.purchases, 0) as purchases'),
                DB::raw('COALESCE(visit_aggregate.revenue, 0) as revenue'),
                DB::raw('COALESCE(event_aggregate.shares, 0) as shares'),
                DB::raw('COALESCE(event_aggregate.reshares, 0) as reshares'),
                DB::raw('COALESCE(event_aggregate.copies, 0) as copies'),
                DB::raw('COALESCE(points_aggregate.referral_points, 0) as referral_points'),
                DB::raw('visit_aggregate.last_activity_at as last_activity_at'),
            ])
            ->orderByDesc(DB::raw('COALESCE(visit_aggregate.revenue, 0)'))
            ->orderByDesc(DB::raw('COALESCE(visit_aggregate.purchases, 0)'))
            ->orderByDesc(DB::raw('COALESCE(visit_aggregate.clicks, 0)'))
            ->paginate($perPage, ['*'], $pageName)
            ->withQueryString();

        $paginator->setCollection(
            $paginator->getCollection()->map(function ($item) {
                $lastActivity = data_get($item, 'last_activity_at');
                $lastActivityAt = $lastActivity ? Carbon::parse($lastActivity) : null;

                return (object) [
                    'id' => (int) $item->id,
                    'name' => $item->name,
                    'email' => $item->email,
                    'photo' => $item->photo,
                    'referral_code' => $item->referral_code,
                    'clicks' => (int) $item->clicks,
                    'visits' => (int) $item->visits,
                    'pageviews' => (int) $item->pageviews,
                    'registrations' => (int) $item->registrations,
                    'checkouts' => (int) $item->checkouts,
                    'purchases' => (int) $item->purchases,
                    'revenue' => round((float) $item->revenue, 2),
                    'shares' => (int) $item->shares,
                    'reshares' => (int) $item->reshares,
                    'copies' => (int) $item->copies,
                    'shares_total' => (int) $item->shares + (int) $item->reshares + (int) $item->copies,
                    'referral_points' => (int) round((float) $item->referral_points),
                    'registration_conversion' => (int) ((int) $item->visits > 0 ? min(100, round(((int) $item->registrations / (int) $item->visits) * 100)) : 0),
                    'purchase_conversion' => (int) ((int) $item->visits > 0 ? min(100, round(((int) $item->purchases / (int) $item->visits) * 100)) : 0),
                    'last_activity_at' => $lastActivityAt,
                    'last_activity_label' => $lastActivityAt?->format('d/m/Y H:i') ?? '—',
                    'last_activity_human' => $lastActivityAt?->diffForHumans() ?? 'Sem atividade',
                ];
            })
        );

        return $paginator;
    }

    public function exportDetailedEventsCsv(?int $referrerUserId = null, string $filename = 'rastreio-indicacoes.csv'): StreamedResponse
    {
        $events = $this->trackingAvailable()
            ? $this->eventsQuery($referrerUserId)
                ->with([
                    'visit.registeredUser:id,name,email',
                    'referrer:id,name,email,referral_code',
                    'registeredUser:id,name,email',
                    'actor:id,name,email',
                ])
                ->latest('occurred_at')
                ->get()
            : collect();

        return response()->streamDownload(function () use ($events) {
            $handle = fopen('php://output', 'w');
            $delimiter = ';';

            $this->writeCsvExcelRow($handle, ['Relatório', 'Rastreio de Indicações'], $delimiter);
            $this->writeCsvExcelRow($handle, ['Gerado em', now()->format('d/m/Y H:i:s')], $delimiter);
            $this->writeCsvExcelRow($handle, [], $delimiter);
            $this->writeCsvExcelRow($handle, [
                'Data/Hora',
                'Afiliado',
                'Código',
                'Ação',
                'Canal',
                'Origem exata',
                'Landing page',
                'Landing URL',
                'URL rastreada',
                'Dispositivo',
                'Navegador',
                'Sistema operacional',
                'Cidade/Região/País',
                'Usuário convertido',
                'Pedido/Valor',
            ], $delimiter);

            foreach ($events as $event) {
                $row = $this->serializeDetailedEvent($event);

                $this->writeCsvExcelRow($handle, [
                    $row['occurred_at_label'],
                    $row['referrer_name'],
                    $row['referral_code'],
                    $row['event_label'],
                    $row['channel_label'],
                    $row['source_url'],
                    $row['landing_page_path'],
                    $row['landing_page_url'],
                    $row['tracked_page_url'],
                    $row['device_label'],
                    $row['browser_label'],
                    $row['os_label'],
                    $row['location_label'],
                    $row['result_user_label'],
                    $row['result_value_label'],
                ], $delimiter);
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=Windows-1252',
        ]);
    }

    private function writeCsvExcelRow($handle, array $row, string $delimiter = ';'): void
    {
        if ($row === []) {
            fwrite($handle, PHP_EOL);

            return;
        }

        fputcsv(
            $handle,
            array_map(fn ($value) => $this->encodeCsvExcelValue($value), $row),
            $delimiter
        );
    }

    private function encodeCsvExcelValue(mixed $value): string
    {
        if ($value === null) {
            return '';
        }

        if (!is_scalar($value)) {
            $value = json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        }

        $string = (string) $value;
        $encoded = @iconv('UTF-8', 'Windows-1252//TRANSLIT//IGNORE', $string);

        return $encoded !== false ? $encoded : $string;
    }

    private function buildDailyTrackingChart(?int $referrerUserId = null, int $days = 14): array
    {
        $start = CarbonImmutable::today()->subDays($days - 1)->startOfDay();
        $period = collect(range(0, $days - 1))->map(fn (int $offset) => $start->addDays($offset));

        $series = [];
        foreach ($period as $day) {
            $series[$day->toDateString()] = [
                'label' => $day->format('d/m'),
                'visits' => 0,
                'registrations' => 0,
                'checkouts' => 0,
                'purchases' => 0,
                'revenue' => 0.0,
            ];
        }

        $events = $this->eventsQuery($referrerUserId)
            ->select('event_type', 'amount', 'occurred_at')
            ->whereIn('event_type', ['visit', 'register', 'checkout_started', 'purchase'])
            ->whereNotNull('occurred_at')
            ->where('occurred_at', '>=', $start)
            ->orderBy('occurred_at')
            ->get();

        foreach ($events as $event) {
            $dateKey = optional($event->occurred_at)?->timezone(config('app.timezone', 'America/Sao_Paulo'))->toDateString();
            if (!$dateKey || !isset($series[$dateKey])) {
                continue;
            }

            if ($event->event_type === 'visit') {
                $series[$dateKey]['visits']++;
                continue;
            }

            if ($event->event_type === 'register') {
                $series[$dateKey]['registrations']++;
                continue;
            }

            if ($event->event_type === 'checkout_started') {
                $series[$dateKey]['checkouts']++;
                continue;
            }

            if ($event->event_type === 'purchase') {
                $series[$dateKey]['purchases']++;
                $series[$dateKey]['revenue'] += (float) ($event->amount ?? 0);
            }
        }

        return [
            'labels' => array_column($series, 'label'),
            'visits' => array_column($series, 'visits'),
            'registrations' => array_column($series, 'registrations'),
            'checkouts' => array_column($series, 'checkouts'),
            'purchases' => array_column($series, 'purchases'),
            'revenue' => array_map(static fn ($value) => round((float) $value, 2), array_column($series, 'revenue')),
        ];
    }

    private function buildAcquisitionChannelChart(?int $referrerUserId = null, int $limit = 6): array
    {
        $rows = $this->visitsQuery($referrerUserId)
            ->select('utm_source', 'referrer_url', 'registered_user_id', 'purchases_count', 'total_revenue_amount')
            ->get()
            ->groupBy(fn (ReferralLinkVisit $visit) => $this->resolveAcquisitionChannelLabel($visit))
            ->map(function (Collection $group, string $label) {
                return [
                    'label' => $label,
                    'visits' => (int) $group->count(),
                    'registrations' => (int) $group->whereNotNull('registered_user_id')->count(),
                    'purchases' => (int) $group->sum('purchases_count'),
                    'revenue' => round((float) $group->sum('total_revenue_amount'), 2),
                ];
            })
            ->sortByDesc('visits')
            ->take($limit)
            ->values();

        if ($rows->isEmpty()) {
            return $this->emptyChannelTrackingChart();
        }

        return [
            'labels' => $rows->pluck('label')->all(),
            'visits' => $rows->pluck('visits')->all(),
            'registrations' => $rows->pluck('registrations')->all(),
            'purchases' => $rows->pluck('purchases')->all(),
            'revenue' => $rows->pluck('revenue')->all(),
        ];
    }

    private function buildSharingChannelBreakdown(?int $referrerUserId = null): Collection
    {
        return $this->eventsQuery($referrerUserId)
            ->select('channel', 'event_type')
            ->whereIn('event_type', ['share', 'reshare', 'copy'])
            ->whereNotNull('channel')
            ->get()
            ->groupBy(fn (ReferralLinkEvent $event) => $this->formatChannelLabel($event->channel))
            ->map(function (Collection $group, string $channel) {
                $shareCount = (int) $group->where('event_type', 'share')->count();
                $reshareCount = (int) $group->where('event_type', 'reshare')->count();
                $copyCount = (int) $group->where('event_type', 'copy')->count();

                return (object) [
                    'channel' => $channel,
                    'shares' => $shareCount,
                    'reshares' => $reshareCount,
                    'copies' => $copyCount,
                    'total' => $shareCount + $reshareCount + $copyCount,
                ];
            })
            ->sortByDesc('total')
            ->values();
    }

    private function buildSharingChannelChart(Collection $trackingChannels): array
    {
        if ($trackingChannels->isEmpty()) {
            return $this->emptySharingTrackingChart();
        }

        return [
            'labels' => $trackingChannels->pluck('channel')->all(),
            'shares' => $trackingChannels->pluck('shares')->all(),
            'reshares' => $trackingChannels->pluck('reshares')->all(),
            'copies' => $trackingChannels->pluck('copies')->all(),
        ];
    }

    private function serializeTrackedVisits(Collection $trackedVisits): array
    {
        return $trackedVisits->map(function (ReferralLinkVisit $visit) {
            return [
                'id' => $visit->id,
                'first_visited_at' => $visit->first_visited_at?->format('d/m/Y H:i') ?? '—',
                'first_visited_human' => $visit->first_visited_at?->diffForHumans() ?? 'agora',
                'clicks_count' => (int) $visit->clicks_count,
                'pageviews_count' => (int) $visit->pageviews_count,
                'source_label' => $this->resolveAcquisitionChannelLabel($visit),
                'landing_page_path' => $visit->landing_page_path ?: '/',
                'registered_user_name' => $visit->registeredUser?->name,
                'registered_at_human' => $visit->registered_at?->diffForHumans() ?? 'cadastrado',
                'purchases_count' => (int) $visit->purchases_count,
                'revenue_amount' => round((float) $visit->total_revenue_amount, 2),
                'revenue_amount_formatted' => number_format((float) $visit->total_revenue_amount, 2, ',', '.'),
            ];
        })->all();
    }

    private function serializeDetailedEvent(ReferralLinkEvent $event): array
    {
        $visit = $event->visit;
        $agent = $this->parseUserAgent($visit?->user_agent);
        $eventMeta = $this->eventPresentation($event);
        $sourceUrl = $visit?->referrer_url ?: (string) data_get($event->metadata, 'target_url', '');
        $landingPagePath = $visit?->landing_page_path ?: ($event->page_path ?: '/');
        $landingPageUrl = $visit?->landing_page_url ?: ($event->page_url ?: '');
        $registeredUser = $event->registeredUser ?: $visit?->registeredUser;

        return [
            'id' => $event->id,
            'occurred_at_label' => $event->occurred_at?->format('d/m/Y H:i:s') ?? '—',
            'occurred_at_human' => $event->occurred_at?->diffForHumans() ?? 'agora',
            'event_type' => $event->event_type,
            'event_label' => $eventMeta['label'],
            'event_badge_class' => $eventMeta['badge'],
            'channel_label' => $this->formatChannelLabel($event->channel ?: ($visit ? $this->resolveAcquisitionChannelLabel($visit) : 'Outro')),
            'source_label' => $visit ? $this->resolveAcquisitionChannelLabel($visit) : 'Direto',
            'source_url' => $sourceUrl !== '' ? $sourceUrl : '—',
            'landing_page_path' => $landingPagePath,
            'landing_page_url' => $landingPageUrl !== '' ? $landingPageUrl : '—',
            'tracked_page_path' => $event->page_path ?: '—',
            'tracked_page_url' => $event->page_url ?: '—',
            'device_label' => $agent['device'],
            'browser_label' => $agent['browser'],
            'os_label' => $agent['os'],
            'location_label' => $this->locationLabel($visit),
            'result_user_label' => $registeredUser?->name ?: ($event->actor?->name ?: '—'),
            'result_value_label' => $this->eventValueLabel($event, $visit),
            'referrer_name' => $event->referrer?->name ?: '—',
            'referral_code' => $event->referrer?->referral_code ?: '—',
        ];
    }

    private function eventPresentation(ReferralLinkEvent $event): array
    {
        return match ($event->event_type) {
            'click' => ['label' => 'Clique no link', 'badge' => 'bg-blue-50 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300'],
            'visit' => ['label' => 'Visualização', 'badge' => 'bg-cyan-50 text-cyan-700 dark:bg-cyan-900/30 dark:text-cyan-300'],
            'register' => ['label' => 'Cadastro', 'badge' => 'bg-emerald-50 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300'],
            'checkout_started' => ['label' => 'Checkout iniciado', 'badge' => 'bg-amber-50 text-amber-700 dark:bg-amber-900/30 dark:text-amber-300'],
            'purchase' => ['label' => 'Compra confirmada', 'badge' => 'bg-violet-50 text-violet-700 dark:bg-violet-900/30 dark:text-violet-300'],
            'share' => ['label' => 'Compartilhamento', 'badge' => 'bg-fuchsia-50 text-fuchsia-700 dark:bg-fuchsia-900/30 dark:text-fuchsia-300'],
            'reshare' => ['label' => 'Recompartilhamento', 'badge' => 'bg-rose-50 text-rose-700 dark:bg-rose-900/30 dark:text-rose-300'],
            'copy' => ['label' => 'Cópia do link', 'badge' => 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300'],
            default => ['label' => Str::headline($event->event_type), 'badge' => 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300'],
        };
    }

    private function eventValueLabel(ReferralLinkEvent $event, ?ReferralLinkVisit $visit): string
    {
        if ($event->event_type === 'purchase') {
            return 'R$ ' . number_format((float) ($event->amount ?? 0), 2, ',', '.');
        }

        if ($event->event_type === 'checkout_started') {
            return 'Checkout em andamento';
        }

        if ($event->event_type === 'register') {
            return 'Cadastro concluído';
        }

        if (in_array($event->event_type, ['share', 'reshare', 'copy'], true)) {
            return 'Distribuição do link';
        }

        return $visit ? sprintf('%d clique(s) · %d visualização(ões)', (int) $visit->clicks_count, (int) $visit->pageviews_count) : 'Acompanhamento de tráfego';
    }

    private function parseUserAgent(?string $userAgent): array
    {
        $userAgent = trim((string) $userAgent);
        $normalized = strtolower($userAgent);

        $browser = 'Desconhecido';
        if (str_contains($normalized, 'edg/')) {
            $browser = 'Edge';
        } elseif (str_contains($normalized, 'opr/') || str_contains($normalized, 'opera')) {
            $browser = 'Opera';
        } elseif (str_contains($normalized, 'chrome/')) {
            $browser = 'Chrome';
        } elseif (str_contains($normalized, 'firefox/')) {
            $browser = 'Firefox';
        } elseif (str_contains($normalized, 'safari/')) {
            $browser = 'Safari';
        } elseif (str_contains($normalized, 'trident/') || str_contains($normalized, 'msie')) {
            $browser = 'Internet Explorer';
        } elseif (str_contains($normalized, 'bot') || str_contains($normalized, 'spider') || str_contains($normalized, 'crawler')) {
            $browser = 'Bot';
        }

        $os = 'Desconhecido';
        if (str_contains($normalized, 'windows')) {
            $os = 'Windows';
        } elseif (str_contains($normalized, 'iphone') || str_contains($normalized, 'ipad')) {
            $os = 'iOS';
        } elseif (str_contains($normalized, 'android')) {
            $os = 'Android';
        } elseif (str_contains($normalized, 'mac os') || str_contains($normalized, 'macintosh')) {
            $os = 'macOS';
        } elseif (str_contains($normalized, 'linux')) {
            $os = 'Linux';
        }

        $device = 'Desktop';
        if (str_contains($normalized, 'bot') || str_contains($normalized, 'spider') || str_contains($normalized, 'crawler')) {
            $device = 'Bot';
        } elseif (str_contains($normalized, 'ipad') || str_contains($normalized, 'tablet')) {
            $device = 'Tablet';
        } elseif (str_contains($normalized, 'mobile') || str_contains($normalized, 'iphone') || str_contains($normalized, 'android')) {
            $device = 'Mobile';
        }

        return [
            'browser' => $browser,
            'os' => $os,
            'device' => $device,
        ];
    }

    private function locationLabel(?ReferralLinkVisit $visit): string
    {
        if (!$visit) {
            return 'Não informado';
        }

        $parts = array_filter([
            $visit->city,
            $visit->region,
            $visit->country,
        ], fn ($value) => trim((string) $value) !== '');

        return $parts !== [] ? implode(' / ', $parts) : 'Não informado';
    }

    private function resolveAcquisitionChannelLabel(ReferralLinkVisit $visit): string
    {
        $utmSource = trim((string) ($visit->utm_source ?? ''));
        if ($utmSource !== '') {
            return $this->formatChannelLabel($utmSource);
        }

        $host = trim((string) parse_url((string) ($visit->referrer_url ?? ''), PHP_URL_HOST));
        if ($host !== '') {
            $host = preg_replace('/^www\./i', '', $host) ?: $host;

            return Str::headline($host);
        }

        return 'Direto';
    }

    private function formatChannelLabel(?string $channel): string
    {
        $channel = trim((string) $channel);

        if ($channel === '') {
            return 'Outro';
        }

        return Str::headline(str_replace(['_', '-', '.'], ' ', $channel));
    }

    private function visitsQuery(?int $referrerUserId = null): Builder
    {
        $query = ReferralLinkVisit::query();

        if ($referrerUserId !== null) {
            $query->where('referrer_user_id', $referrerUserId);
        }

        return $query;
    }

    private function eventsQuery(?int $referrerUserId = null): Builder
    {
        $query = ReferralLinkEvent::query();

        if ($referrerUserId !== null) {
            $query->where('referrer_user_id', $referrerUserId);
        }

        return $query;
    }

    private function emptyTrackingSummary(): array
    {
        return [
            'clicks' => 0,
            'visits' => 0,
            'pageviews' => 0,
            'registrations' => 0,
            'checkout_starts' => 0,
            'purchases' => 0,
            'revenue' => 0.0,
            'shares' => 0,
            'reshares' => 0,
            'copies' => 0,
            'registration_conversion' => 0,
            'purchase_conversion' => 0,
        ];
    }

    private function emptyDailyTrackingChart(): array
    {
        return [
            'labels' => [],
            'visits' => [],
            'registrations' => [],
            'checkouts' => [],
            'purchases' => [],
            'revenue' => [],
        ];
    }

    private function emptyChannelTrackingChart(): array
    {
        return [
            'labels' => [],
            'visits' => [],
            'registrations' => [],
            'purchases' => [],
            'revenue' => [],
        ];
    }

    private function emptySharingTrackingChart(): array
    {
        return [
            'labels' => [],
            'shares' => [],
            'reshares' => [],
            'copies' => [],
        ];
    }

    private function emptyPaginator(int $perPage, string $pageName): LengthAwarePaginator
    {
        $paginator = new LengthAwarePaginator(
            collect(),
            0,
            $perPage,
            LengthAwarePaginator::resolveCurrentPage($pageName),
            [
                'path' => request()->url(),
                'pageName' => $pageName,
            ]
        );

        return $paginator->appends(request()->query());
    }
}
