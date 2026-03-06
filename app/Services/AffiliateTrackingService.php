<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Plan;
use App\Models\ReferralLinkEvent;
use App\Models\ReferralLinkVisit;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class AffiliateTrackingService
{
    private const COOKIE_KEY = 'affiliate_visitor';
    private const SESSION_KEY = 'affiliate_tracking.current';
    private const PAGEVIEW_DEDUPE_KEY = 'affiliate_tracking.last_pageview';

    public function captureIncomingReferral(Request $request): void
    {
        if (!$this->isAvailable()) {
            return;
        }

        $referrer = $this->resolveReferrerByCode($request->query('ref'));
        if (!$referrer) {
            return;
        }

        if (auth()->id() && (int) auth()->id() === (int) $referrer->id) {
            return;
        }

        $now = now();
        $visit = $this->resolveCurrentVisit($request);
        $sessionId = $request->session()->getId();
        $visitorToken = $this->ensureVisitorToken($request);
        $ipHash = $this->hashIp($request);

        if (
            !$visit
            || (int) $visit->referrer_user_id !== (int) $referrer->id
            || (string) $visit->session_id !== (string) $sessionId
        ) {
            $visit = ReferralLinkVisit::create([
                'referrer_user_id' => $referrer->id,
                'referral_code' => $referrer->referral_code,
                'session_id' => $sessionId,
                'visitor_token' => $visitorToken,
                'landing_page_path' => $this->normalizePath($request),
                'landing_page_url' => $request->fullUrl(),
                'last_page_path' => $this->normalizePath($request),
                'last_page_url' => $request->fullUrl(),
                'referrer_url' => (string) $request->headers->get('referer', ''),
                'utm_source' => $this->nullableString($request->query('utm_source')),
                'utm_medium' => $this->nullableString($request->query('utm_medium')),
                'utm_campaign' => $this->nullableString($request->query('utm_campaign')),
                'utm_content' => $this->nullableString($request->query('utm_content')),
                'utm_term' => $this->nullableString($request->query('utm_term')),
                'ip_hash' => $ipHash,
                'user_agent' => $this->nullableString($request->userAgent()),
                'country' => $this->nullableString($request->header('CF-IPCountry')),
                'clicks_count' => 1,
                'pageviews_count' => 0,
                'checkout_started_count' => 0,
                'purchases_count' => 0,
                'first_visited_at' => $now,
                'last_visited_at' => $now,
            ]);

            $this->storeCurrentVisit($request, $visit);
            $this->logEvent($visit, 'click', null, [
                'page_path' => $this->normalizePath($request),
                'page_url' => $request->fullUrl(),
                'metadata' => [
                    'source' => 'query_ref',
                    'utm_source' => $visit->utm_source,
                    'utm_medium' => $visit->utm_medium,
                    'utm_campaign' => $visit->utm_campaign,
                    'utm_content' => $visit->utm_content,
                    'utm_term' => $visit->utm_term,
                ],
            ]);
            $this->logEvent($visit, 'visit', null, [
                'page_path' => $this->normalizePath($request),
                'page_url' => $request->fullUrl(),
                'metadata' => ['source' => 'session_start'],
            ]);

            return;
        }

        $visit->forceFill([
            'clicks_count' => (int) $visit->clicks_count + 1,
            'last_page_path' => $this->normalizePath($request),
            'last_page_url' => $request->fullUrl(),
            'last_visited_at' => $now,
            'referrer_url' => $visit->referrer_url ?: (string) $request->headers->get('referer', ''),
            'utm_source' => $visit->utm_source ?: $this->nullableString($request->query('utm_source')),
            'utm_medium' => $visit->utm_medium ?: $this->nullableString($request->query('utm_medium')),
            'utm_campaign' => $visit->utm_campaign ?: $this->nullableString($request->query('utm_campaign')),
            'utm_content' => $visit->utm_content ?: $this->nullableString($request->query('utm_content')),
            'utm_term' => $visit->utm_term ?: $this->nullableString($request->query('utm_term')),
        ])->save();

        $this->storeCurrentVisit($request, $visit);
        $this->logEvent($visit, 'click', null, [
            'page_path' => $this->normalizePath($request),
            'page_url' => $request->fullUrl(),
            'metadata' => ['source' => 'query_ref_repeat'],
        ]);
    }

    public function trackPageView(Request $request, mixed $response = null): void
    {
        if (!$this->isAvailable() || !$this->shouldTrackPageView($request, $response)) {
            return;
        }

        $visit = $this->resolveCurrentVisit($request);
        if (!$visit) {
            return;
        }

        $path = $this->normalizePath($request);
        $now = time();
        $dedupe = (array) $request->session()->get(self::PAGEVIEW_DEDUPE_KEY, []);

        if (($dedupe['visit_id'] ?? null) == $visit->id && ($dedupe['path'] ?? null) === $path && ($now - (int) ($dedupe['ts'] ?? 0)) < 15) {
            return;
        }

        $request->session()->put(self::PAGEVIEW_DEDUPE_KEY, [
            'visit_id' => $visit->id,
            'path' => $path,
            'ts' => $now,
        ]);

        $timestamp = now();
        $updates = [
            'last_page_path' => $path,
            'last_page_url' => $request->fullUrl(),
            'last_visited_at' => $timestamp,
            'pageviews_count' => (int) $visit->pageviews_count + 1,
        ];

        if (!$visit->first_page_path) {
            $updates['first_page_path'] = $path;
            $updates['first_page_url'] = $request->fullUrl();
        }

        $visit->forceFill($updates)->save();
    }

    public function currentReferralCode(Request $request): ?string
    {
        $code = $this->nullableString($request->query('ref'))
            ?: $this->nullableString(data_get($request->session()->get(self::SESSION_KEY, []), 'referral_code'))
            ?: $this->nullableString($request->session()->get('social_ref'));

        return $code;
    }

    public function resolveReferrerByCode(mixed $referralCode): ?User
    {
        $referralCode = $this->nullableString($referralCode);
        if (!$referralCode || !Schema::hasColumn('users', 'referral_code')) {
            return null;
        }

        return User::query()
            ->where('referral_code', $referralCode)
            ->first();
    }

    public function attachRegisteredUser(Request $request, User $user, ?string $referralCode = null): void
    {
        if (!$this->isAvailable()) {
            return;
        }

        $referrer = $this->resolveReferrerByCode($referralCode ?: $this->currentReferralCode($request));
        if (!$referrer || (int) $referrer->id === (int) $user->id) {
            return;
        }

        if (!$user->referred_by && Schema::hasColumn('users', 'referred_by')) {
            $user->forceFill(['referred_by' => $referrer->id])->save();
        }

        $visit = $this->resolveCurrentVisit($request)
            ?: ReferralLinkVisit::query()
                ->where('referrer_user_id', $referrer->id)
                ->where(function ($query) use ($request) {
                    $query->where('session_id', $request->session()->getId())
                        ->orWhere('visitor_token', $this->ensureVisitorToken($request));
                })
                ->latest('id')
                ->first();

        if (!$visit) {
            return;
        }

        $alreadyRegistered = ReferralLinkEvent::query()
            ->where('referral_link_visit_id', $visit->id)
            ->where('event_type', 'register')
            ->where('registered_user_id', $user->id)
            ->exists();

        $visit->forceFill([
            'registered_user_id' => $user->id,
            'registered_at' => $visit->registered_at ?: now(),
        ])->save();

        $this->storeCurrentVisit($request, $visit);

        if (!$alreadyRegistered) {
            $this->logEvent($visit, 'register', null, [
                'registered_user_id' => $user->id,
                'actor_user_id' => $user->id,
                'page_path' => $this->normalizePath($request),
                'page_url' => $request->fullUrl(),
                'metadata' => [
                    'email' => $user->email,
                    'source' => 'registration',
                ],
            ]);
        }
    }

    public function recordShareAction(User $user, string $action, ?string $channel = null, array $metadata = []): string
    {
        if (!$this->isAvailable()) {
            return $action === 'copy' ? 'copy' : 'share';
        }

        $channel = $this->nullableString($channel) ?: ($action === 'copy' ? 'copy' : 'other');

        $eventType = $action === 'copy'
            ? 'copy'
            : (
                ReferralLinkEvent::query()
                    ->where('referrer_user_id', $user->id)
                    ->whereIn('event_type', ['share', 'reshare'])
                    ->where('channel', $channel)
                    ->exists()
                    ? 'reshare'
                    : 'share'
            );

        ReferralLinkEvent::create([
            'referrer_user_id' => $user->id,
            'actor_user_id' => $user->id,
            'event_type' => $eventType,
            'channel' => $channel,
            'metadata' => $metadata,
            'occurred_at' => now(),
        ]);

        return $eventType;
    }

    public function recordCheckoutStarted(Request $request, Order $order, ?Plan $plan = null): void
    {
        if (!$this->isAvailable()) {
            return;
        }

        $visit = $this->resolveCurrentVisit($request);
        if (!$visit) {
            $visit = $this->resolveVisitForOrder($order);
        }

        if (!$visit) {
            return;
        }

        if ($order->user_id && !$visit->registered_user_id) {
            $visit->registered_user_id = $order->user_id;
        }

        $trackingMeta = $this->trackingMetadataForVisit($visit);
        $trackingMeta['registered_user_id'] = $order->user_id ?: $visit->registered_user_id;

        $metadata = $order->metadata ?? [];
        $metadata['referral_tracking'] = $trackingMeta;
        $order->metadata = $metadata;
        $order->save();

        $alreadyLogged = ReferralLinkEvent::query()
            ->where('order_id', $order->id)
            ->where('event_type', 'checkout_started')
            ->exists();

        if ($alreadyLogged) {
            return;
        }

        $visit->forceFill([
            'checkout_started_count' => (int) $visit->checkout_started_count + 1,
            'first_checkout_started_at' => $visit->first_checkout_started_at ?: now(),
            'first_order_id' => $visit->first_order_id ?: $order->id,
            'first_plan_id' => $visit->first_plan_id ?: ($plan?->id),
        ])->save();

        $this->logEvent($visit, 'checkout_started', null, [
            'registered_user_id' => $order->user_id ?: $visit->registered_user_id,
            'actor_user_id' => $order->user_id ?: null,
            'order_id' => $order->id,
            'plan_id' => $plan?->id,
            'amount' => $order->total_amount,
            'metadata' => [
                'context' => data_get($order->metadata, 'context'),
                'gateway' => $order->gateway,
            ],
        ]);
    }

    public function recordPaidOrder(Order $order, ?Plan $plan = null): void
    {
        if (!$this->isAvailable()) {
            return;
        }

        $visit = $this->resolveVisitForOrder($order);
        if (!$visit) {
            return;
        }

        $alreadyLogged = ReferralLinkEvent::query()
            ->where('order_id', $order->id)
            ->where('event_type', 'purchase')
            ->exists();

        if ($alreadyLogged) {
            return;
        }

        $planId = $plan?->id ?: (int) data_get($order->metadata, 'referral_tracking.plan_id', 0) ?: null;
        if (!$planId) {
            $planId = optional($order->items()->where('item_type', 'plan')->first())->item_id ?: null;
        }

        $paidAt = $order->paid_at ?: now();

        $visit->forceFill([
            'purchases_count' => (int) $visit->purchases_count + 1,
            'first_purchase_at' => $visit->first_purchase_at ?: $paidAt,
            'last_purchase_at' => $paidAt,
            'first_paid_order_id' => $visit->first_paid_order_id ?: $order->id,
            'first_plan_id' => $visit->first_plan_id ?: $planId,
            'total_revenue_amount' => (float) $visit->total_revenue_amount + (float) $order->total_amount,
        ])->save();

        $this->logEvent($visit, 'purchase', null, [
            'registered_user_id' => $order->user_id ?: $visit->registered_user_id,
            'actor_user_id' => $order->user_id ?: null,
            'order_id' => $order->id,
            'plan_id' => $planId,
            'amount' => $order->total_amount,
            'occurred_at' => $paidAt,
            'metadata' => [
                'context' => data_get($order->metadata, 'context'),
                'sale_type' => data_get($order->metadata, 'sale_type'),
                'gateway' => $order->gateway,
                'status' => $order->status,
            ],
        ]);
    }

    public function isAvailable(): bool
    {
        return Schema::hasTable('referral_link_visits') && Schema::hasTable('referral_link_events');
    }

    private function resolveCurrentVisit(Request $request): ?ReferralLinkVisit
    {
        $visitId = (int) data_get($request->session()->get(self::SESSION_KEY, []), 'visit_id', 0);
        if ($visitId <= 0) {
            return null;
        }

        return ReferralLinkVisit::query()->find($visitId);
    }

    private function resolveVisitForOrder(Order $order): ?ReferralLinkVisit
    {
        $tracking = data_get($order->metadata, 'referral_tracking', []);
        $visitId = (int) data_get($tracking, 'visit_id', 0);

        if ($visitId > 0) {
            $visit = ReferralLinkVisit::query()->find($visitId);
            if ($visit) {
                return $visit;
            }
        }

        $referrerUserId = (int) data_get($tracking, 'referrer_user_id', 0);
        $registeredUserId = (int) ($order->user_id ?: data_get($tracking, 'registered_user_id', 0));

        if ($referrerUserId > 0 && $registeredUserId > 0) {
            $visit = ReferralLinkVisit::query()
                ->where('referrer_user_id', $referrerUserId)
                ->where('registered_user_id', $registeredUserId)
                ->latest('registered_at')
                ->latest('id')
                ->first();

            if ($visit) {
                return $visit;
            }
        }

        if ($order->user_id) {
            $buyer = $order->relationLoaded('user') ? $order->user : $order->user()->first();
            if ($buyer && $buyer->referred_by) {
                return ReferralLinkVisit::query()
                    ->where('referrer_user_id', $buyer->referred_by)
                    ->where('registered_user_id', $buyer->id)
                    ->latest('registered_at')
                    ->latest('id')
                    ->first();
            }
        }

        return null;
    }

    private function logEvent(ReferralLinkVisit $visit, string $eventType, ?string $channel = null, array $payload = []): void
    {
        ReferralLinkEvent::create([
            'referral_link_visit_id' => $visit->id,
            'referrer_user_id' => $visit->referrer_user_id,
            'actor_user_id' => $payload['actor_user_id'] ?? null,
            'registered_user_id' => $payload['registered_user_id'] ?? $visit->registered_user_id,
            'event_type' => $eventType,
            'channel' => $channel ?: ($payload['channel'] ?? null),
            'page_path' => $payload['page_path'] ?? null,
            'page_url' => $payload['page_url'] ?? null,
            'order_id' => $payload['order_id'] ?? null,
            'plan_id' => $payload['plan_id'] ?? null,
            'amount' => $payload['amount'] ?? null,
            'metadata' => $payload['metadata'] ?? null,
            'occurred_at' => $payload['occurred_at'] ?? now(),
        ]);
    }

    private function trackingMetadataForVisit(ReferralLinkVisit $visit): array
    {
        return [
            'visit_id' => $visit->id,
            'referrer_user_id' => $visit->referrer_user_id,
            'referral_code' => $visit->referral_code,
            'visitor_token' => $visit->visitor_token,
            'session_id' => $visit->session_id,
            'plan_id' => $visit->first_plan_id,
        ];
    }

    private function storeCurrentVisit(Request $request, ReferralLinkVisit $visit): void
    {
        $request->session()->put(self::SESSION_KEY, [
            'visit_id' => $visit->id,
            'referrer_user_id' => $visit->referrer_user_id,
            'referral_code' => $visit->referral_code,
            'visitor_token' => $visit->visitor_token,
            'registered_user_id' => $visit->registered_user_id,
        ]);
        $request->session()->put('social_ref', $visit->referral_code);
    }

    private function ensureVisitorToken(Request $request): string
    {
        $token = $this->nullableString($request->cookie(self::COOKIE_KEY))
            ?: $this->nullableString($request->session()->get(self::COOKIE_KEY));

        if (!$token) {
            $token = (string) Str::uuid();
            Cookie::queue(Cookie::make(self::COOKIE_KEY, $token, 60 * 24 * 90));
            $request->session()->put(self::COOKIE_KEY, $token);
        }

        return $token;
    }

    private function hashIp(Request $request): ?string
    {
        $ip = (string) ($request->header('CF-Connecting-IP') ?: $request->ip() ?: '');
        if ($ip === '') {
            return null;
        }

        return hash('sha256', $ip . '|' . (string) config('app.key', ''));
    }

    private function nullableString(mixed $value): ?string
    {
        $value = is_scalar($value) ? trim((string) $value) : '';

        return $value !== '' ? $value : null;
    }

    private function normalizePath(Request $request): string
    {
        return '/' . ltrim($request->path(), '/');
    }

    private function shouldTrackPageView(Request $request, mixed $response = null): bool
    {
        if (!$request->isMethod('GET')) {
            return false;
        }

        if ($request->expectsJson() || $request->ajax()) {
            return false;
        }

        if ($request->is('admin*') || $request->is('panel*') || $request->is('api*')) {
            return false;
        }

        if ($request->is('storage*') || $request->is('img*') || $request->is('uploads*')) {
            return false;
        }

        $path = $this->normalizePath($request);
        if (in_array($path, ['/favicon.ico', '/service-worker.js', '/manifest.webmanifest'], true)) {
            return false;
        }

        if (!$response) {
            return true;
        }

        try {
            $status = method_exists($response, 'getStatusCode') ? (int) $response->getStatusCode() : 200;
            if ($status < 200 || $status >= 300) {
                return false;
            }

            $contentType = (string) ($response->headers->get('Content-Type') ?? '');

            return $contentType === '' || str_contains(strtolower($contentType), 'text/html');
        } catch (\Throwable) {
            return true;
        }
    }
}
