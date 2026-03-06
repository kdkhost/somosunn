<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use App\Models\PointsLog;
use App\Models\ReferralLinkEvent;
use App\Models\ReferralLinkVisit;
use App\Models\User;
use App\Services\AffiliateTrackingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

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

            $trackingChannels = ReferralLinkEvent::query()
                ->select('channel', DB::raw('COUNT(*) as total'))
                ->where('referrer_user_id', $user->id)
                ->whereIn('event_type', ['share', 'reshare', 'copy'])
                ->whereNotNull('channel')
                ->groupBy('channel')
                ->orderByDesc('total')
                ->get();

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
}
