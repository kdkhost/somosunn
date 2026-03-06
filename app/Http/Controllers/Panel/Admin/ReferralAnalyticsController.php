<?php

namespace App\Http\Controllers\Panel\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\ReferralAnalyticsService;
use Illuminate\Http\Request;

class ReferralAnalyticsController extends Controller
{
    public function __construct(
        private readonly ReferralAnalyticsService $analytics,
    ) {
    }

    public function index(Request $request)
    {
        $selectedReferrerId = $request->integer('referrer');
        $selectedReferrer = $selectedReferrerId > 0
            ? User::query()->select('id', 'name', 'email', 'photo', 'referral_code')->find($selectedReferrerId)
            : null;

        $scopeReferrerId = $selectedReferrer?->id;

        return view('panel.admin.referrals.index', array_merge([
            'selectedReferrer' => $selectedReferrer,
            'channelFunnels' => $this->analytics->buildChannelFunnels($scopeReferrerId),
            'detailedEvents' => $this->analytics->detailedEventsPaginator($scopeReferrerId, 20, 'events_page'),
            'affiliateLeaderboard' => $this->analytics->affiliateLeaderboard(15, 'affiliates_page'),
        ], $this->analytics->buildDashboardPayload($scopeReferrerId)));
    }

    public function export(Request $request)
    {
        $selectedReferrerId = $request->integer('referrer');
        $selectedReferrer = $selectedReferrerId > 0
            ? User::query()->select('id', 'name', 'referral_code')->find($selectedReferrerId)
            : null;

        $filename = $selectedReferrer
            ? sprintf('rastreio-afiliado-%s-%s.csv', $selectedReferrer->referral_code ?: $selectedReferrer->id, now()->format('Ymd-His'))
            : sprintf('rastreio-afiliados-global-%s.csv', now()->format('Ymd-His'));

        return $this->analytics->exportDetailedEventsCsv($selectedReferrer?->id, $filename);
    }
}
