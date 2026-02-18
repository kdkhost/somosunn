<?php

namespace App\Http\Controllers;

use App\Models\Connection;
use App\Models\Message;
use App\Models\Order;
use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationHubController extends Controller
{
    /**
     * Get consolidated notifications for the current user.
     */
    public function index()
    {
        $user = Auth::user();

        // 1. Unread Messages Count (Filtering out blocked users)
        $blockedUserIds = Connection::where('status', 'blocked')
            ->where(function ($q) use ($user) {
                $q->where('requester_id', $user->id)->orWhere('requested_id', $user->id);
            })
            ->get()
            ->map(function ($connection) use ($user) {
                return $connection->requester_id === $user->id
                    ? $connection->requested_id
                    : $connection->requester_id;
            })
            ->toArray();

        $unreadMessagesCount = Message::where('user_id', '!=', $user->id)
            ->whereNotIn('user_id', $blockedUserIds)
            ->whereHas('conversation', function ($q) use ($user) {
                $q->whereHas('users', function ($uq) use ($user) {
                    $uq->where('users.id', $user->id);
                });
            })
            ->whereNull('read_at')
            ->count();

        // 2. Pending Connection Requests
        $pendingConnectionsCount = Connection::where('requested_id', $user->id)
            ->where('status', 'pending')
            ->count();

        // 3. New Sales (Last 24h)
        $newSalesCount = Order::where('seller_id', $user->id)
            ->where('status', 'paid')
            ->where('created_at', '>=', now()->subDay())
            ->count();

        // 4. Upcoming Events (Starting within 24h)
        $upcomingEventsCount = Event::where('user_id', $user->id)
            ->where('start_at', '>=', now())
            ->where('start_at', '<=', now()->addDay())
            ->count();

        // 5. Expiring Plan Alert
        $planExpiresSoon = false;
        if ($user->plan_expires_at) {
            $planExpiresSoon = $user->plan_expires_at->isFuture() && $user->plan_expires_at->diffInDays(now()) <= 7;
        }

        // Consolidated response
        return response()->json([
            'total' => $unreadMessagesCount + $pendingConnectionsCount + $newSalesCount + $upcomingEventsCount + ($planExpiresSoon ? 1 : 0),
            'items' => [
                [
                    'type' => 'messages',
                    'count' => $unreadMessagesCount,
                    'label' => 'novas mensagens',
                    'icon' => 'fas fa-comments',
                    'color' => 'text-blue-500',
                    'bg' => 'bg-blue-50',
                    'route' => route('chat.index')
                ],
                [
                    'type' => 'connections',
                    'count' => $pendingConnectionsCount,
                    'label' => 'pedidos de conexão',
                    'icon' => 'fas fa-user-plus',
                    'color' => 'text-green-500',
                    'bg' => 'bg-green-50',
                    'route' => route('social.feed')
                ],
                [
                    'type' => 'sales',
                    'count' => $newSalesCount,
                    'label' => 'vendas (24h)',
                    'icon' => 'fas fa-shopping-cart',
                    'color' => 'text-amber-500',
                    'bg' => 'bg-amber-50',
                    'route' => route('panel.marketplace.sales')
                ],
                [
                    'type' => 'events',
                    'count' => $upcomingEventsCount,
                    'label' => 'eventos próximos',
                    'icon' => 'fas fa-calendar-alt',
                    'color' => 'text-purple-500',
                    'bg' => 'bg-purple-50',
                    'route' => route('events.index')
                ],
                [
                    'type' => 'plan',
                    'count' => $planExpiresSoon ? 1 : 0,
                    'label' => 'vencimento plano',
                    'icon' => 'fas fa-exclamation-triangle',
                    'color' => 'text-red-500',
                    'bg' => 'bg-red-50',
                    'route' => route('premium')
                ]
            ],
            'last_sync' => now()->toIso8601String()
        ]);
    }
}
