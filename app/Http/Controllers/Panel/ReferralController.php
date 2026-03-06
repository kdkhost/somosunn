<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use App\Models\PointsLog;
use App\Models\ReferralLinkEvent;
use App\Models\ReferralLinkVisit;
use App\Models\User;
use App\Services\AffiliateTrackingService;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class ReferralController extends Controller
{
    public function index()
    {
        /** @var User $user */
        $user = Auth::user();

        if (empty($user->referral_code)) {
            do {
                $code = 'UNN' . strtoupper(substr(md5($user->id . microtime()), 0, 7));
            } while (User::where('referral_code', $code)->exists());

            $user->referral_code = $code;
            $user->save();
        }

        $referralLink = route('register') . '?ref=' . $user->referral_code;

        $referralPointsLogs = PointsLog::where('user_id', $user->id)
            ->where('action_key', 'referral')
            ->latest()
            ->get();

        $totalReferralPoints = $referralPointsLogs->sum('points');

        $convertedUserIds = $referralPointsLogs
            ->map(fn ($log) => json_decode($log->meta ?? '{}', true)['new_user_id'] ?? null)
            ->filter()
            ->unique()
            ->values()
            ->toArray();

        $referredUsers = User::where('referred_by', $user->id)
            ->with('plan:id,name,price,is_free')
            ->select('id', 'name', 'email', 'photo', 'created_at', 'plan_id', 'plan_expires_at')
            ->latest()
            ->paginate(20, ['*'], 'members_page');

        $totalReferred = User::where('referred_by', $user->id)->count();
        $convertedCount = count(array_unique($convertedUserIds));
        $pendingCount = max(0, $totalReferred - $convertedCount);

        $plansMap = Plan::whereIn('id', $referredUsers->pluck('plan_id')->filter()->unique()->values())
            ->pluck('name', 'id');

        $trackingAvailable = Schema::hasTable('referral_link_visits') && Schema::hasTable('referral_link_events');
        $trackingSummary = [
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
        $trackingChannels = collect();
        $trackedVisits = collect();
        $trackingDailyChart = $this->emptyDailyTrackingChart();
        $trackingAcquisitionChart = $this->emptyChannelTrackingChart();
        $trackingSharingChart = $this->emptySharingTrackingChart();

        if ($trackingAvailable) {
            $visitsQuery = ReferralLinkVisit::query()->where('referrer_user_id', $user->id);
            $eventsQuery = ReferralLinkEvent::query()->where('referrer_user_id', $user->id);

            $trackingSummary = [
                'clicks' => (int) (clone $visitsQuery)->sum('clicks_count'),
                'visits' => (int) (clone $visitsQuery)->count(),
                'pageviews' => (int) (clone $visitsQuery)->sum('pageviews_count'),
                'registrations' => (int) (clone $visitsQuery)->whereNotNull('registered_user_id')->count(),
                'checkout_starts' => (int) (clone $visitsQuery)->sum('checkout_started_count'),
                'purchases' => (int) (clone $visitsQuery)->sum('purchases_count'),
                'revenue' => (float) (clone $visitsQuery)->sum('total_revenue_amount'),
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

            $trackingChannels = $this->buildSharingChannelBreakdown($user->id);
            $trackingDailyChart = $this->buildDailyTrackingChart($user->id);
            $trackingAcquisitionChart = $this->buildAcquisitionChannelChart($user->id);
            $trackingSharingChart = $this->buildSharingChannelChart($trackingChannels);

            $trackedVisits = ReferralLinkVisit::query()
                ->with('registeredUser:id,name,email,photo')
                ->where('referrer_user_id', $user->id)
                ->latest('first_visited_at')
                ->paginate(15, ['*'], 'tracking_page');
        }

        return view('panel.referral.index', compact(
            'user',
            'referralLink',
            'referredUsers',
            'referralPointsLogs',
            'convertedUserIds',
            'totalReferralPoints',
            'totalReferred',
            'convertedCount',
            'pendingCount',
            'plansMap',
            'trackingAvailable',
            'trackingSummary',
            'trackingChannels',
            'trackingDailyChart',
            'trackingAcquisitionChart',
            'trackingSharingChart',
            'trackedVisits'
        ));
    }

    public function track(Request $request, AffiliateTrackingService $tracking)
    {
        $data = $request->validate([
            'action' => 'required|string|in:copy,share',
            'channel' => 'nullable|string|max:40',
            'context' => 'nullable|string|max:80',
            'target_url' => 'nullable|string|max:2048',
        ]);

        $eventType = $tracking->recordShareAction(
            $request->user(),
            (string) $data['action'],
            $data['channel'] ?? null,
            [
                'context' => $data['context'] ?? 'panel_referral',
                'target_url' => $data['target_url'] ?? null,
            ]
        );

        return response()->json([
            'ok' => true,
            'event_type' => $eventType,
        ]);
    }

    private function buildDailyTrackingChart(int $referrerUserId, int $days = 14): array
    {
        $start = CarbonImmutable::today()->subDays($days - 1)->startOfDay();
        $period = collect(range(0, $days - 1))->map(
            fn (int $offset) => $start->addDays($offset)
        );

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

        $events = ReferralLinkEvent::query()
            ->select('event_type', 'amount', 'occurred_at')
            ->where('referrer_user_id', $referrerUserId)
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

    private function buildAcquisitionChannelChart(int $referrerUserId, int $limit = 6): array
    {
        $rows = ReferralLinkVisit::query()
            ->select('utm_source', 'referrer_url', 'registered_user_id', 'purchases_count', 'total_revenue_amount')
            ->where('referrer_user_id', $referrerUserId)
            ->get()
            ->groupBy(function (ReferralLinkVisit $visit) {
                return $this->resolveAcquisitionChannelLabel($visit);
            })
            ->map(function ($group, $label) {
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

    private function buildSharingChannelBreakdown(int $referrerUserId)
    {
        return ReferralLinkEvent::query()
            ->select('channel', 'event_type')
            ->where('referrer_user_id', $referrerUserId)
            ->whereIn('event_type', ['share', 'reshare', 'copy'])
            ->whereNotNull('channel')
            ->get()
            ->groupBy(fn (ReferralLinkEvent $event) => $this->formatChannelLabel($event->channel))
            ->map(function ($group, $channel) {
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

    private function buildSharingChannelChart($trackingChannels): array
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
}
