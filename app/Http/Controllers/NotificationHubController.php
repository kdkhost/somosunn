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
        /** @var User $user */
        $user = Auth::user();

        return $this->noCacheJson($this->buildPayload($user));
    }

    public function acknowledge(Request $request): JsonResponse
    {
        // Keep this strictly empty or redirecting to NotificationController@markAsRead just in case
        // We will change the frontend to use NotificationController@markAsRead directly
        return $this->noCacheJson(['success' => true]);
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
        // Get up to 15 unread individual notifications
        $notifications = $user->unreadNotifications()->latest()->take(15)->get();
        $total = $user->unreadNotifications()->count();

        $items = [];

        foreach ($notifications as $notification) {
            $data = $notification->data;
            
            // Standardize colors and icons like we do in list.blade.php
            $icon = 'fas fa-bell';
            $bgIcon = 'bg-gray-100';
            $textColor = 'text-gray-600';

            $type = (string) $notification->type;
            
            if (str_contains($type, 'Message')) {
                $icon = 'fas fa-comments';
                $bgIcon = 'bg-blue-100';
                $textColor = 'text-blue-600';
            } elseif (str_contains($type, 'Connection')) {
                $icon = 'fas fa-user-plus';
                $bgIcon = 'bg-green-100';
                $textColor = 'text-green-600';
            } elseif (str_contains($type, 'Order') || str_contains($type, 'Sale') || str_contains($type, 'Payment')) {
                $icon = 'fas fa-shopping-cart';
                if (str_contains($type, 'Sale')) $icon = 'fas fa-dollar-sign';
                if (str_contains($type, 'Payment')) $icon = 'fas fa-check-circle';
                $bgIcon = 'bg-amber-100';
                $textColor = 'text-amber-600';
            } elseif (str_contains($type, 'Event')) {
                $icon = 'fas fa-calendar-alt';
                $bgIcon = 'bg-purple-100';
                $textColor = 'text-purple-600';
            } elseif (str_contains($type, 'Reaction')) {
                $icon = 'fas fa-heart';
                $bgIcon = 'bg-red-100';
                $textColor = 'text-red-600';
            } elseif (str_contains($type, 'Comment') || str_contains($type, 'Reply')) {
                $icon = 'fas fa-comment-alt';
                if (str_contains($type, 'Reply')) $icon = 'fas fa-reply';
                $bgIcon = 'bg-indigo-100';
                $textColor = 'text-indigo-600';
            } elseif (str_contains($type, 'Job')) {
                $icon = 'fas fa-briefcase';
                $bgIcon = 'bg-indigo-50';
                $textColor = 'text-indigo-500';
            }

            $items[] = [
                'id' => $notification->id,
                'type' => $type,
                'label' => $data['message'] ?? 'Você tem uma nova notificação',
                'route' => $data['action_url'] ?? route('notifications.index'),
                'icon' => $icon,
                'color' => $textColor,
                'bg' => $bgIcon,
                'time_ago' => $notification->created_at->diffForHumans(),
            ];
        }

        return [
            'total' => $total,
            'items' => $items,
            'last_sync' => now()->toIso8601String(),
        ];
    }
}
