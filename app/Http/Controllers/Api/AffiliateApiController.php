<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\AffiliateShareKitService;
use App\Services\ReferralAnalyticsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AffiliateApiController extends Controller
{
    public function __construct(
        private readonly AffiliateShareKitService $shareKit,
        private readonly ReferralAnalyticsService $analytics,
    ) {
    }

    public function overview(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        $kit = $this->shareKit->buildForUser($user);
        $tracking = $this->analytics->buildDashboardPayload($user->id, 5);

        return response()->json([
            'referral' => $kit['referral'],
            'branding' => $kit['branding'],
            'summary' => $tracking['trackingSummary'],
            'api' => $kit['api'],
        ]);
    }

    public function materials(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        $kit = $this->shareKit->buildForUser($user);

        return response()->json([
            'referral' => $kit['referral'],
            'branding' => $kit['branding'],
            'materials' => $kit['materials'],
            'social_links' => $kit['branding']['social_links'],
        ]);
    }

    public function offers(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        $kit = $this->shareKit->buildForUser($user);

        return response()->json([
            'referral' => $kit['referral'],
            'offers' => $kit['offers'],
        ]);
    }

    public function landingPage(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        $kit = $this->shareKit->buildForUser($user);

        return response()->json([
            'referral' => $kit['referral'],
            'branding' => $kit['branding'],
            'landing_page' => $kit['landing_page'],
        ]);
    }

    public function analytics(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        $eventsPerPage = max(1, min((int) $request->query('per_page', 25), 100));
        $visitLimit = max(1, min((int) $request->query('visit_limit', 10), 50));

        $payload = $this->analytics->buildDashboardPayload($user->id, $visitLimit);
        $channelFunnels = $this->analytics->buildChannelFunnels($user->id);
        $detailedEvents = $this->analytics->detailedEventsPaginator($user->id, $eventsPerPage, 'page');

        return response()->json([
            'tracking_available' => $payload['trackingAvailable'],
            'tracking_status_message' => $payload['trackingStatusMessage'],
            'tracking_status_tone' => $payload['trackingStatusTone'],
            'updated_at' => $payload['trackingUpdatedAt'],
            'updated_at_label' => $payload['trackingUpdatedAtLabel'],
            'summary' => $payload['trackingSummary'],
            'channels' => $payload['trackingChannels']->values()->all(),
            'latest_visits' => $payload['trackedVisitsFeed'],
            'daily_chart' => $payload['trackingDailyChart'],
            'acquisition_chart' => $payload['trackingAcquisitionChart'],
            'sharing_chart' => $payload['trackingSharingChart'],
            'channel_funnels' => $channelFunnels->values()->all(),
            'detailed_events' => [
                'data' => collect($detailedEvents->items())->map(fn ($item) => (array) $item)->all(),
                'meta' => [
                    'current_page' => $detailedEvents->currentPage(),
                    'last_page' => $detailedEvents->lastPage(),
                    'per_page' => $detailedEvents->perPage(),
                    'total' => $detailedEvents->total(),
                ],
            ],
        ]);
    }
}
