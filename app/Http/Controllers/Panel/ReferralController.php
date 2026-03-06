<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use App\Models\PointsLog;
use App\Models\User;
use App\Services\AffiliateTrackingService;
use App\Services\ReferralAnalyticsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReferralController extends Controller
{
    public function __construct(
        private readonly ReferralAnalyticsService $analytics,
    ) {
    }

    public function index()
    {
        /** @var User $user */
        $user = Auth::user();

        $this->ensureReferralCode($user);

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

        return view('panel.referral.index', array_merge(compact(
            'user',
            'referralLink',
            'referredUsers',
            'referralPointsLogs',
            'convertedUserIds',
            'totalReferralPoints',
            'totalReferred',
            'convertedCount',
            'pendingCount',
            'plansMap'
        ), [
            'channelFunnels' => $this->analytics->buildChannelFunnels($user->id),
            'detailedEvents' => $this->analytics->detailedEventsPaginator($user->id, 20, 'events_page'),
        ], $this->analytics->buildDashboardPayload($user->id)));
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

    public function stats(Request $request)
    {
        /** @var User $user */
        $user = $request->user();

        $this->ensureReferralCode($user);

        $payload = $this->analytics->buildDashboardPayload($user->id);
        $channelFunnels = $this->analytics->buildChannelFunnels($user->id);
        $detailedEvents = $this->analytics->detailedEventsPaginator($user->id, 20, 'events_page');

        return response()->json([
            'ok' => true,
            'trackingAvailable' => $payload['trackingAvailable'],
            'trackingStatusMessage' => $payload['trackingStatusMessage'],
            'trackingStatusTone' => $payload['trackingStatusTone'],
            'trackingUpdatedAt' => $payload['trackingUpdatedAt'],
            'trackingUpdatedAtLabel' => $payload['trackingUpdatedAtLabel'],
            'trackingSummary' => $payload['trackingSummary'],
            'trackingChannels' => $payload['trackingChannels']->values()->all(),
            'trackedVisitsFeed' => $payload['trackedVisitsFeed'],
            'trackingDailyChart' => $payload['trackingDailyChart'],
            'trackingAcquisitionChart' => $payload['trackingAcquisitionChart'],
            'trackingSharingChart' => $payload['trackingSharingChart'],
            'channelFunnelsHtml' => view('panel.referral.partials.channel-funnel', [
                'channelFunnels' => $channelFunnels,
                'title' => 'Funil por canal e origem',
                'subtitle' => 'Veja de onde vieram os cliques, quantas visualizações cada origem gerou e onde realmente converte.',
            ])->render(),
            'detailedEventsHtml' => view('panel.referral.partials.events-log', [
                'detailedEvents' => $detailedEvents,
                'exportUrl' => route('panel.referral.export'),
                'title' => 'Log detalhado de cliques, visitas e compartilhamentos',
                'subtitle' => 'Inclui URL de origem exata, landing page, dispositivo, navegador, cidade/país e o resultado de cada ação.',
                'emptyMessage' => 'Ainda não há cliques, visitas ou compartilhamentos detalhados para este afiliado.',
            ])->render(),
        ]);
    }

    public function export(Request $request)
    {
        /** @var User $user */
        $user = $request->user();

        $this->ensureReferralCode($user);

        return $this->analytics->exportDetailedEventsCsv(
            $user->id,
            sprintf('rastreio-indicacoes-%s-%s.csv', $user->referral_code, now()->format('Ymd-His'))
        );
    }

    private function ensureReferralCode(User $user): void
    {
        if (!empty($user->referral_code)) {
            return;
        }

        do {
            $code = 'UNN' . strtoupper(substr(md5($user->id . microtime()), 0, 7));
        } while (User::where('referral_code', $code)->exists());

        $user->referral_code = $code;
        $user->save();
    }
}
