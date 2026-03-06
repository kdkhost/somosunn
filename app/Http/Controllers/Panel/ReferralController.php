<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use App\Models\PointsLog;
use App\Models\User;
use App\Services\AffiliateShareKitService;
use App\Services\AffiliateTrackingService;
use App\Services\ReferralAnalyticsService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Laravel\Sanctum\PersonalAccessToken;

class ReferralController extends Controller
{
    public function __construct(
        private readonly ReferralAnalyticsService $analytics,
        private readonly AffiliateShareKitService $shareKit,
    ) {
    }

    public function index()
    {
        /** @var User $user */
        $user = Auth::user();

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
            'affiliateShareKit' => $this->shareKit->buildForUser($user),
            'apiTokens' => $this->apiTokensForUser($user),
            'apiTokenPlainText' => session('api_token_plain_text'),
            'apiTokenDeviceName' => session('api_token_device_name'),
            'apiTokensEnabled' => $this->hasPersonalAccessTokensTable(),
            'apiTokenIpTrackingEnabled' => $this->hasPersonalAccessTokenColumn('last_used_ip'),
        ], $this->analytics->buildDashboardPayload($user->id)));
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
            ->route('panel.referral.index')
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
            ->route('panel.referral.index')
            ->with('success', 'Token renomeado com sucesso.');
    }

    public function destroyToken(Request $request, int $tokenId): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();
        $token = $this->resolveOwnedToken($user, $tokenId);

        $token->delete();

        return redirect()
            ->route('panel.referral.index')
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

        $user = $this->shareKit->ensureReferralCode($user);

        $payload = $this->analytics->buildDashboardPayload($user->id);
        $channelFunnels = $this->analytics->buildChannelFunnels($user->id);
        $detailedEvents = $this->analytics->detailedEventsPaginator($user->id, 20, 'events_page');
        $affiliateShareKit = $this->shareKit->buildForUser($user);

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
            'shareKitHtml' => view('panel.referral.partials.share-kit', [
                'affiliateShareKit' => $affiliateShareKit,
            ])->render(),
        ]);
    }

    public function export(Request $request)
    {
        /** @var User $user */
        $user = $request->user();

        $user = $this->shareKit->ensureReferralCode($user);

        return $this->analytics->exportDetailedEventsCsv(
            $user->id,
            sprintf('rastreio-indicacoes-%s-%s.csv', $user->referral_code, now()->format('Ymd-His'))
        );
    }

    private function apiTokensForUser(User $user)
    {
        if (!$this->hasPersonalAccessTokensTable()) {
            return collect();
        }

        $columns = ['id', 'name', 'last_used_at', 'created_at'];

        if ($this->hasPersonalAccessTokenColumn('last_used_ip')) {
            $columns[] = 'last_used_ip';
        }

        return $user->tokens()
            ->latest('id')
            ->get($columns)
            ->map(function (PersonalAccessToken $token) {
                if (!isset($token->last_used_ip)) {
                    $token->last_used_ip = null;
                }

                return $token;
            });
    }

    private function resolveOwnedToken(User $user, int $tokenId): PersonalAccessToken
    {
        abort_unless($this->hasPersonalAccessTokensTable(), 404);

        return $user->tokens()
            ->whereKey($tokenId)
            ->firstOrFail();
    }

    private function hasPersonalAccessTokensTable(): bool
    {
        try {
            return Schema::hasTable('personal_access_tokens');
        } catch (\Throwable) {
            return false;
        }
    }

    private function hasPersonalAccessTokenColumn(string $column): bool
    {
        if (!$this->hasPersonalAccessTokensTable()) {
            return false;
        }

        try {
            return Schema::hasColumn('personal_access_tokens', $column);
        } catch (\Throwable) {
            return false;
        }
    }
}
