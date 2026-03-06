<?php

namespace App\Http\Controllers\Panel;

use App\Models\AffiliateApiSandboxRequest;
use App\Http\Controllers\Concerns\ManagesPersonalApiTokens;
use App\Http\Controllers\Controller;
use App\Models\Plan;
use App\Models\PointsLog;
use App\Models\User;
use App\Services\AffiliateShareKitService;
use App\Services\AffiliateTrackingService;
use App\Services\ReferralAnalyticsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

class ReferralController extends Controller
{
    use ManagesPersonalApiTokens;

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

        $sandbox = $this->sandboxData($user);

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
            'sandboxRequests' => $sandbox['requests'],
            'sandboxLatestRequest' => $sandbox['latestRequest'],
            'sandboxApprovedRequest' => $sandbox['approvedRequest'],
            'sandboxAvailable' => $sandbox['available'],
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

    public function storeSandboxRequest(Request $request): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();

        if (!$this->hasSandboxRequestsTable()) {
            return back()->withErrors([
                'sandbox' => 'O sandbox da API ainda não está disponível neste ambiente. Rode as migrations e tente novamente.',
            ]);
        }

        $data = $request->validate([
            'reason' => 'required|string|min:12|max:4000',
            'requested_domain' => 'nullable|string|max:255',
            'requested_ip' => 'nullable|ip',
        ], [
            'reason.required' => 'Explique o motivo da solicitação.',
            'reason.min' => 'Descreva um pouco melhor o uso pretendido para a API.',
            'requested_ip.ip' => 'Informe um IP válido.',
        ]);

        $pending = AffiliateApiSandboxRequest::query()
            ->where('user_id', $user->id)
            ->where('status', 'pending')
            ->latest('id')
            ->first();

        $payload = [
            'reason' => trim((string) $data['reason']),
            'requested_domain' => $this->normalizeDomain($data['requested_domain'] ?? null),
            'requested_ip' => trim((string) ($data['requested_ip'] ?? '')) ?: null,
            'status' => 'pending',
            'admin_notes' => null,
            'reviewed_by' => null,
            'reviewed_at' => null,
        ];

        if ($pending) {
            $pending->update($payload);
        } else {
            AffiliateApiSandboxRequest::query()->create($payload + ['user_id' => $user->id]);
        }

        return redirect()
            ->route('panel.referral.index')
            ->with('success', 'Ticket de acesso ao sandbox enviado. O time vai revisar motivo, IP e domínio informados.');
    }

    public function playground(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        abort_unless($this->hasSandboxRequestsTable(), 503, 'Sandbox indisponível neste ambiente.');

        $approvedRequest = AffiliateApiSandboxRequest::query()
            ->approved()
            ->where('user_id', $user->id)
            ->latest('reviewed_at')
            ->latest('id')
            ->first();

        abort_unless($approvedRequest, 403, 'Sandbox ainda não liberado para este afiliado.');

        $data = $request->validate([
            'endpoint' => 'required|string|in:overview,materials,offers,landing-page,analytics',
            'per_page' => 'nullable|integer|min:1|max:100',
            'visit_limit' => 'nullable|integer|min:1|max:50',
        ]);

        $startedAt = microtime(true);
        $kit = $this->shareKit->buildForUser($user);

        $payload = match ($data['endpoint']) {
            'overview' => [
                'referral' => $kit['referral'],
                'branding' => $kit['branding'],
                'summary' => $this->analytics->buildDashboardPayload($user->id, 5)['trackingSummary'],
                'sandbox' => $kit['sandbox'],
                'playground' => $kit['playground'],
                'api' => $kit['api'],
            ],
            'materials' => [
                'referral' => $kit['referral'],
                'branding' => $kit['branding'],
                'materials' => $kit['materials'],
                'graphic_assets' => $kit['graphic_assets'],
                'embed_widgets' => $kit['embed_widgets'],
                'playground' => $kit['playground'],
                'sandbox' => $kit['sandbox'],
                'social_links' => $kit['branding']['social_links'] ?? [],
            ],
            'offers' => [
                'referral' => $kit['referral'],
                'offers' => $kit['offers'],
            ],
            'landing-page' => [
                'referral' => $kit['referral'],
                'branding' => $kit['branding'],
                'landing_page' => $kit['landing_page'],
                'embed_widgets' => $kit['embed_widgets'],
            ],
            'analytics' => $this->buildAnalyticsPlaygroundPayload($user, $request),
        };

        return response()->json([
            'ok' => true,
            'endpoint' => $data['endpoint'],
            'sandbox_base_url' => url('/api/v1/sandbox/affiliate'),
            'duration_ms' => (int) round((microtime(true) - $startedAt) * 1000),
            'request_url' => $this->buildSandboxRequestUrl($data['endpoint'], $request),
            'payload' => $payload,
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

        $user = $this->shareKit->ensureReferralCode($user);

        return $this->analytics->exportDetailedEventsCsv(
            $user->id,
            sprintf('rastreio-indicacoes-%s-%s.csv', $user->referral_code, now()->format('Ymd-His'))
        );
    }

    private function sandboxData(User $user): array
    {
        if (!$this->hasSandboxRequestsTable()) {
            return [
                'available' => false,
                'requests' => collect(),
                'latestRequest' => null,
                'approvedRequest' => null,
            ];
        }

        $requests = AffiliateApiSandboxRequest::query()
            ->where('user_id', $user->id)
            ->latest('id')
            ->limit(5)
            ->get();

        return [
            'available' => true,
            'requests' => $requests,
            'latestRequest' => $requests->first(),
            'approvedRequest' => $requests->firstWhere('status', 'approved')
                ?: AffiliateApiSandboxRequest::query()->approved()->where('user_id', $user->id)->latest('reviewed_at')->first(),
        ];
    }

    private function hasSandboxRequestsTable(): bool
    {
        try {
            return Schema::hasTable('affiliate_api_sandbox_requests');
        } catch (\Throwable) {
            return false;
        }
    }

    private function normalizeDomain(?string $domain): ?string
    {
        $domain = trim((string) $domain);
        if ($domain === '') {
            return null;
        }

        $domain = preg_replace('#^https?://#i', '', $domain);
        $domain = explode('/', $domain)[0] ?? $domain;
        $domain = strtolower(trim($domain));

        return $domain !== '' ? $domain : null;
    }

    private function buildSandboxRequestUrl(string $endpoint, Request $request): string
    {
        $baseUrl = url('/api/v1/sandbox/affiliate');

        if ($endpoint !== 'analytics') {
            return $baseUrl . '/' . $endpoint;
        }

        return $baseUrl . '/analytics?' . http_build_query(array_filter([
            'per_page' => $request->integer('per_page') ?: 10,
            'visit_limit' => $request->integer('visit_limit') ?: 5,
        ]));
    }

    private function buildAnalyticsPlaygroundPayload(User $user, Request $request): array
    {
        $eventsPerPage = max(1, min((int) $request->input('per_page', 10), 100));
        $visitLimit = max(1, min((int) $request->input('visit_limit', 5), 50));

        $payload = $this->analytics->buildDashboardPayload($user->id, $visitLimit);
        $channelFunnels = $this->analytics->buildChannelFunnels($user->id);
        $detailedEvents = $this->analytics->detailedEventsPaginator($user->id, $eventsPerPage, 'page');

        return [
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
        ];
    }

}
