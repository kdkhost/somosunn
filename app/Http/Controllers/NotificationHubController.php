<?php

namespace App\Http\Controllers;

use App\Models\Connection;
use App\Models\Event;
use App\Models\Message;
use App\Models\Order;
use App\Models\ShareRequest;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

class NotificationHubController extends Controller
{
    public function index(): JsonResponse
    {
        return $this->noCacheJson($this->buildPayload(Auth::user()));
    }

    public function acknowledge(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = Auth::user();

        $data = $request->validate([
            'type' => ['required', 'string'],
        ]);

        $snapshots = $this->buildSnapshots($user);
        $type = (string) $data['type'];

        if (isset($snapshots[$type])) {
            $this->persistAcknowledgement($user, $type, $snapshots[$type]['ack']);
            $user->refresh();
        }

        return $this->noCacheJson([
            'success' => true,
        ] + $this->buildPayload($user));
    }

    protected function noCacheJson(array $payload): JsonResponse
    {
        return response()
            ->json($payload)
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
            ->header('Pragma', 'no-cache')
            ->header('Expires', 'Sat, 01 Jan 2000 00:00:00 GMT');
    }

    protected function buildPayload(User $user): array
    {
        $snapshots = $this->buildSnapshots($user);
        $items = [];
        $total = 0;

        foreach ($snapshots as $snapshot) {
            $items[] = [
                'type' => $snapshot['type'],
                'count' => $snapshot['count'],
                'label' => $snapshot['label'],
                'icon' => $snapshot['icon'],
                'color' => $snapshot['color'],
                'bg' => $snapshot['bg'],
                'route' => $snapshot['route'],
            ];

            $total += $snapshot['count'];
        }

        return [
            'total' => $total,
            'items' => $items,
            'last_sync' => now()->toIso8601String(),
        ];
    }

    protected function buildSnapshots(User $user): array
    {
        $acknowledged = $this->acknowledgedState($user);

        $blockedUserIds = Connection::where('status', 'blocked')
            ->where(function (Builder $query) use ($user) {
                $query->where('requester_id', $user->id)->orWhere('requested_id', $user->id);
            })
            ->get()
            ->map(function (Connection $connection) use ($user) {
                return $connection->requester_id === $user->id
                    ? $connection->requested_id
                    : $connection->requester_id;
            })
            ->toArray();

        $messagesBaseQuery = Message::query()
            ->where('user_id', '!=', $user->id)
            ->whereNotIn('user_id', $blockedUserIds)
            ->whereHas('conversation', function (Builder $query) use ($user) {
                $query->whereHas('users', function (Builder $userQuery) use ($user) {
                    $userQuery->where('users.id', $user->id);
                });
            })
            ->whereNull('read_at');

        $connectionsBaseQuery = Connection::query()
            ->where('requested_id', $user->id)
            ->where('status', 'pending');

        $shareRequestsBaseQuery = ShareRequest::pending()
            ->where('to_user_id', $user->id);

        $salesBaseQuery = Order::query()
            ->where('seller_id', $user->id)
            ->where('status', 'paid')
            ->where('created_at', '>=', now()->subDay());

        $eventsBaseQuery = Event::query()
            ->where('user_id', $user->id)
            ->where('start_at', '>=', now())
            ->where('start_at', '<=', now()->addDay());

        $jobNotificationsBaseQuery = $user->unreadNotifications()
            ->where('type', 'App\Notifications\JobVacancyPublished');

        $planExpiresSoon = false;
        if ($user->plan_expires_at) {
            $planExpiresSoon = $user->plan_expires_at->isFuture()
                && $user->plan_expires_at->diffInDays(now()) <= 7;
        }

        return [
            'messages' => $this->buildIdTrackedSnapshot(
                'messages',
                $messagesBaseQuery,
                $acknowledged['messages'] ?? [],
                [
                    'label' => 'novas mensagens',
                    'icon' => 'fas fa-comments',
                    'color' => 'text-blue-500',
                    'bg' => 'bg-blue-50',
                    'route' => route('chat.index'),
                ]
            ),
            'connections' => $this->buildIdTrackedSnapshot(
                'connections',
                $connectionsBaseQuery,
                $acknowledged['connections'] ?? [],
                [
                    'label' => 'pedidos de conexao',
                    'icon' => 'fas fa-user-plus',
                    'color' => 'text-green-500',
                    'bg' => 'bg-green-50',
                    'route' => route('social.feed'),
                ]
            ),
            'share_requests' => $this->buildIdTrackedSnapshot(
                'share_requests',
                $shareRequestsBaseQuery,
                $acknowledged['share_requests'] ?? [],
                [
                    'label' => 'compartilhamentos pendentes',
                    'icon' => 'fas fa-share-alt',
                    'color' => 'text-violet-500',
                    'bg' => 'bg-violet-50',
                    'route' => route('social.share-requests.index'),
                ]
            ),
            'sales' => $this->buildIdTrackedSnapshot(
                'sales',
                $salesBaseQuery,
                $acknowledged['sales'] ?? [],
                [
                    'label' => 'vendas (24h)',
                    'icon' => 'fas fa-shopping-cart',
                    'color' => 'text-amber-500',
                    'bg' => 'bg-amber-50',
                    'route' => route('panel.marketplace.sales'),
                ]
            ),
            'events' => $this->buildIdTrackedSnapshot(
                'events',
                $eventsBaseQuery,
                $acknowledged['events'] ?? [],
                [
                    'label' => 'eventos proximos',
                    'icon' => 'fas fa-calendar-alt',
                    'color' => 'text-purple-500',
                    'bg' => 'bg-purple-50',
                    'route' => route('events.index'),
                ]
            ),
            'plan' => $this->buildPlanSnapshot($user, $planExpiresSoon, $acknowledged['plan'] ?? []),
            'jobs' => $this->buildTimestampTrackedSnapshot(
                'jobs',
                $jobNotificationsBaseQuery,
                $acknowledged['jobs'] ?? [],
                [
                    'label' => 'novas vagas',
                    'icon' => 'fas fa-briefcase',
                    'color' => 'text-indigo-500',
                    'bg' => 'bg-indigo-50',
                    'route' => route('notifications.index', ['type' => 'JobVacancyPublished']),
                ]
            ),
        ];
    }

    protected function buildIdTrackedSnapshot(string $type, Builder|Relation $baseQuery, array $acknowledged, array $meta): array
    {
        $currentMaxId = (int) ((clone $baseQuery)->max('id') ?: 0);
        $lastSeenId = max(0, (int) ($acknowledged['last_seen_id'] ?? 0));
        $filteredQuery = clone $baseQuery;

        if ($lastSeenId > 0) {
            $filteredQuery->where('id', '>', $lastSeenId);
        }

        return $meta + [
            'type' => $type,
            'count' => (int) $filteredQuery->count(),
            'ack' => [
                'last_seen_id' => $currentMaxId,
                'acknowledged_at' => now()->toIso8601String(),
            ],
        ];
    }

    protected function buildTimestampTrackedSnapshot(string $type, Builder|Relation $baseQuery, array $acknowledged, array $meta): array
    {
        $currentMaxCreatedAt = (clone $baseQuery)->max('created_at');
        $lastSeenAt = $acknowledged['last_seen_at'] ?? null;
        $filteredQuery = clone $baseQuery;

        if (is_string($lastSeenAt) && $lastSeenAt !== '') {
            $filteredQuery->where('created_at', '>', $lastSeenAt);
        }

        return $meta + [
            'type' => $type,
            'count' => (int) $filteredQuery->count(),
            'ack' => [
                'last_seen_at' => $currentMaxCreatedAt,
                'acknowledged_at' => now()->toIso8601String(),
            ],
        ];
    }

    protected function buildPlanSnapshot(User $user, bool $planExpiresSoon, array $acknowledged): array
    {
        $signature = $planExpiresSoon
            ? implode('|', [
                (string) $user->plan_id,
                optional($user->plan_expires_at)->toIso8601String(),
            ])
            : null;

        return [
            'type' => 'plan',
            'count' => $planExpiresSoon && ($acknowledged['signature'] ?? null) !== $signature ? 1 : 0,
            'label' => 'vencimento plano',
            'icon' => 'fas fa-exclamation-triangle',
            'color' => 'text-red-500',
            'bg' => 'bg-red-50',
            'route' => route('planos'),
            'ack' => [
                'signature' => $signature,
                'acknowledged_at' => now()->toIso8601String(),
            ],
        ];
    }

    protected function acknowledgedState(User $user): array
    {
        if (!Schema::hasColumn($user->getTable(), 'extra_features')) {
            return [];
        }

        $extraFeatures = $user->extra_features;

        if (!is_array($extraFeatures)) {
            return [];
        }

        $hubState = $extraFeatures['notification_hub_ack'] ?? [];

        return is_array($hubState) ? $hubState : [];
    }

    protected function persistAcknowledgement(User $user, string $type, array $state): void
    {
        if (!Schema::hasColumn($user->getTable(), 'extra_features')) {
            return;
        }

        $extraFeatures = $user->extra_features;
        if (!is_array($extraFeatures)) {
            $extraFeatures = [];
        }

        $hubState = $extraFeatures['notification_hub_ack'] ?? [];
        if (!is_array($hubState)) {
            $hubState = [];
        }

        $hubState[$type] = $state;
        $extraFeatures['notification_hub_ack'] = $hubState;

        $user->forceFill([
            'extra_features' => $extraFeatures,
        ])->save();
    }
}
