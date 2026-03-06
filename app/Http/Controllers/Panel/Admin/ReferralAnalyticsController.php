<?php

namespace App\Http\Controllers\Panel\Admin;

use App\Http\Controllers\Controller;
use App\Models\AffiliateApiSandboxRequest;
use App\Models\User;
use App\Services\ReferralAnalyticsService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

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
            'sandboxRequestsAvailable' => $this->hasSandboxRequestsTable(),
            'sandboxRequests' => $this->sandboxRequestsPaginator(),
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

    public function updateSandboxRequest(Request $request, AffiliateApiSandboxRequest $sandboxRequest): RedirectResponse
    {
        abort_unless($this->hasSandboxRequestsTable(), 404);

        $data = $request->validate([
            'status' => 'required|string|in:approved,rejected,revoked',
            'admin_notes' => 'nullable|string|max:4000',
        ]);

        $sandboxRequest->forceFill([
            'status' => $data['status'],
            'admin_notes' => trim((string) ($data['admin_notes'] ?? '')) ?: null,
            'reviewed_by' => $request->user()?->id,
            'reviewed_at' => now(),
        ])->save();

        return redirect()
            ->route('panel.admin.referrals.index')
            ->with('success', 'Ticket de sandbox atualizado com sucesso.');
    }

    private function sandboxRequestsPaginator()
    {
        if (!$this->hasSandboxRequestsTable()) {
            return collect();
        }

        return AffiliateApiSandboxRequest::query()
            ->with(['user:id,name,email,referral_code', 'reviewer:id,name'])
            ->latest('id')
            ->paginate(12, ['*'], 'sandbox_page');
    }

    private function hasSandboxRequestsTable(): bool
    {
        try {
            return Schema::hasTable('affiliate_api_sandbox_requests');
        } catch (\Throwable) {
            return false;
        }
    }
}
