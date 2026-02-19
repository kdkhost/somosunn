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

        return view('notifications.index', compact('notifications'));
    }

    public function markAsRead($id = null)
    {
        $user = Auth::user();

        if ($id) {
            $notification = $user->notifications()->findOrFail($id);
            $notification->markAsRead();
        } else {
            $user->unreadNotifications->markAsRead();
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
