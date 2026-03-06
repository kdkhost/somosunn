<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Concerns\ManagesPersonalApiTokens;
use App\Http\Controllers\Controller;
use App\Models\AffiliateApiSandboxRequest;
use App\Models\Plan;
use App\Models\PointsLog;
use App\Models\User;
use App\Services\AffiliateShareKitService;
use App\Services\AffiliateTrackingService;
use App\Services\ReferralAnalyticsService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class ReferralController extends Controller
{
    use ManagesPersonalApiTokens;

    public function __construct(
        private readonly ReferralAnalyticsService $analytics,
        private readonly AffiliateShareKitService $shareKit,
    ) {
    }

    public function index(Request $request)
    {
        /** @var User $user */
        $user = $request->user();

        $user = $this->shareKit->ensureReferralCode($user);

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
            ->paginate(10, ['*'], 'members_page');

        $totalReferred = User::where('referred_by', $user->id)->count();
        $convertedCount = count(array_unique($convertedUserIds));
        $pendingCount = max(0, $totalReferred - $convertedCount);

        $plansMap = Plan::whereIn('id', $referredUsers->pluck('plan_id')->filter()->unique()->values())
            ->pluck('name', 'id');

        $selectedReferrerId = $request->integer('referrer');
        $selectedReferrer = $selectedReferrerId > 0
            ? User::query()->select('id', 'name', 'email', 'photo', 'referral_code')->find($selectedReferrerId)
            : null;

        $scopeReferrerId = $selectedReferrer?->id;

        return view('admin.referrals.index', array_merge(compact(
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
            'selectedReferrer'
        ), [
            'channelFunnels' => $this->analytics->buildChannelFunnels($scopeReferrerId),
            'detailedEvents' => $this->analytics->detailedEventsPaginator($scopeReferrerId, 20, 'events_page'),
            'affiliateLeaderboard' => $this->analytics->affiliateLeaderboard(15, 'affiliates_page'),
            'sandboxRequestsAvailable' => $this->hasSandboxRequestsTable(),
            'sandboxRequests' => $this->sandboxRequestsPaginator(),
            'apiTokens' => $this->apiTokensForUser($user),
            'apiTokenPlainText' => session('api_token_plain_text'),
            'apiTokenDeviceName' => session('api_token_device_name'),
            'apiTokensEnabled' => $this->hasPersonalAccessTokensTable(),
            'apiTokenIpTrackingEnabled' => $this->hasPersonalAccessTokenColumn('last_used_ip'),
        ], $this->analytics->buildDashboardPayload($scopeReferrerId)));
    }

    public function export(Request $request)
    {
        $selectedReferrerId = $request->integer('referrer');
        $selectedReferrer = $selectedReferrerId > 0
            ? User::query()->select('id', 'name', 'referral_code')->find($selectedReferrerId)
            : null;

        $filename = $selectedReferrer
            ? sprintf('rastreio-afiliado-admin-%s-%s.csv', $selectedReferrer->referral_code ?: $selectedReferrer->id, now()->format('Ymd-His'))
            : sprintf('rastreio-afiliados-admin-global-%s.csv', now()->format('Ymd-His'));

        return $this->analytics->exportDetailedEventsCsv($selectedReferrer?->id, $filename);
    }

    public function storeToken(Request $request): RedirectResponse
    {
        if (!$this->hasPersonalAccessTokensTable()) {
            return back()->withErrors([
                'api_tokens' => 'A tabela de tokens da API ainda não está disponível. Rode as migrations e tente novamente.',
            ]);
        }

        $data = $request->validate([
            'device_name' => 'required|string|max:120',
        ], [
            'device_name.required' => 'Informe o nome do dispositivo ou integração.',
        ]);

        /** @var User $user */
        $user = $request->user();

        $plainTextToken = $user->createToken($data['device_name'])->plainTextToken;

        return redirect()
            ->route('admin.referrals.index')
            ->with('success', 'Token da API gerado com sucesso.')
            ->with('api_token_plain_text', $plainTextToken)
            ->with('api_token_device_name', $data['device_name']);
    }

    public function updateToken(Request $request, int $tokenId): RedirectResponse
    {
        $data = $request->validate([
            'device_name' => 'required|string|max:120',
        ], [
            'device_name.required' => 'Informe o nome do dispositivo para renomear o token.',
        ]);

        /** @var User $user */
        $user = $request->user();
        $token = $this->resolveOwnedToken($user, $tokenId);

        $token->forceFill([
            'name' => $data['device_name'],
        ])->save();

        return redirect()
            ->route('admin.referrals.index')
            ->with('success', 'Token renomeado com sucesso.');
    }

    public function destroyToken(Request $request, int $tokenId): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();
        $token = $this->resolveOwnedToken($user, $tokenId);

        $token->delete();

        return redirect()
            ->route('admin.referrals.index')
            ->with('success', 'Token revogado com sucesso.');
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
                'context' => $data['context'] ?? 'admin_legacy_referral',
                'target_url' => $data['target_url'] ?? null,
            ]
        );

        return response()->json([
            'ok' => true,
            'event_type' => $eventType,
        ]);
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
            ->route('admin.referrals.index')
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
