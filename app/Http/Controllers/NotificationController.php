<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

        // --- Data for Sidebars (Copied from SocialController) ---

        // 1. Blocked Users
        $blockedUserIds = \App\Models\Connection::where('status', 'blocked')
            ->where(function ($q) use ($user) {
                $q->where('requester_id', $user->id)->orWhere('requested_id', $user->id);
            })
            ->get()
            ->map(function ($connection) use ($user) {
                return $connection->requester_id === $user->id
                    ? $connection->requested_id
                    : $connection->requester_id;
            })
            ->unique()
            ->values()
            ->toArray();

        // 2. Connected Users
        $connectedUserIds = \App\Models\Connection::where('status', 'accepted')
            ->where(function ($q) use ($user) {
                $q->where('requester_id', $user->id)->orWhere('requested_id', $user->id);
            })
            ->get()
            ->map(function ($connection) use ($user) {
                return $connection->requester_id === $user->id
                    ? $connection->requested_id
                    : $connection->requester_id;
            })
            ->unique()
            ->values()
            ->toArray();

        // 3. Recommended Users
        $recommendedUsers = collect();
        if ($user) {
            $recommendedUsers = app(\App\Services\MemberSuggestionService::class)
                ->suggest($user, $blockedUserIds, $connectedUserIds, 5);
        }

        // 4. Pending Requests
        $pendingRequests = collect();
        if ($user) {
            $pendingRequests = \App\Models\Connection::where('requested_id', $user->id)
                ->where('status', 'pending')
                ->with('requester')
                ->get();
        }

        // 5. Connection Map
        $connectionMap = [];
        if ($user) {
            $authId = $user->id;
            // Map received pending requests
            foreach ($pendingRequests as $conn) {
                $connectionMap[$conn->requester_id] = $conn;
            }
            // Map recommended users connections
            if ($recommendedUsers->isNotEmpty()) {
                $recommendedIds = $recommendedUsers->pluck('id')->all();
                $connections = \App\Models\Connection::where(function ($q) use ($authId, $recommendedIds) {
                    $q->where('requester_id', $authId)->whereIn('requested_id', $recommendedIds);
                })->orWhere(function ($q) use ($authId, $recommendedIds) {
                    $q->where('requested_id', $authId)->whereIn('requester_id', $recommendedIds);
                })->get();

                foreach ($connections as $connection) {
                    $otherId = $connection->requester_id === $authId
                        ? $connection->requested_id
                        : $connection->requester_id;
                    $connectionMap[$otherId] = $connection;
                }
            }
        }

        // 6. Ads Configuration
        $adsEnabled = (string) \App\Models\Setting::get('ads_enabled', '0') === '1';
        $adsCode = (string) \App\Models\Setting::get('ads_code_html', '');
        $adsensePublisherId = (string) \App\Models\Setting::get('adsense_publisher_id', '');
        $adsenseSlotId = (string) \App\Models\Setting::get('adsense_slot_id', '');
        $adsenseFormat = (string) \App\Models\Setting::get('adsense_format', 'auto');
        $adsenseFrequency = (int) \App\Models\Setting::get('adsense_frequency', 5);

        $adsConfig = [
            'enabled' => $adsEnabled,
            'customCode' => $adsCode,
            'publisherId' => $adsensePublisherId,
            'slotId' => $adsenseSlotId,
            'format' => $adsenseFormat,
            'frequency' => $adsenseFrequency,
        ];

        // --- End Sidebar Data ---

        $query = $user->notifications();

        if ($request->get('filter') === 'unread') {
            $query->whereNull('read_at');
        }

        if ($request->has('type') && $request->type !== 'all') {
            $query->where('type', 'LIKE', '%' . $request->type . '%');
        }

        $notifications = $query->latest()->paginate(15);

        if ($request->ajax()) {
            return view('notifications.partials.list', compact('notifications'))->render();
        }

        return view('notifications.index', compact(
            'notifications',
            'recommendedUsers',
            'pendingRequests',
            'connectionMap',
            'adsEnabled',
            'adsCode',
            'adsConfig',
            'adsensePublisherId',
            'adsenseSlotId',
            'adsenseFormat',
            'adsenseFrequency'
        ));
    }

    public function markAsRead($id = null)
    {
        $user = Auth::user();

        if ($id) {
            $notification = $user->notifications()->findOrFail($id);
            $notification->markAsRead();
        } else {
            $user->unreadNotifications()->update([
                'read_at' => now(),
            ]);
        }

        return response()->json(['success' => true]);
    }

    public function destroy($id)
    {
        $user = Auth::user();
        $notification = $user->notifications()->findOrFail($id);
        $notification->delete();

        return response()->json(['success' => true]);
    }
}
