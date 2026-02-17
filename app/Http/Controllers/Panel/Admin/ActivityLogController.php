<?php

namespace App\Http\Controllers\Panel\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ActivityLogController extends Controller
{
    public function index(Request $request)
    {
        $this->ensurePermission('logs.view');

        $type = $request->query('type');
        $userId = $request->query('user_id');
        $q = trim((string) $request->query('q', ''));

        $logs = ActivityLog::query()
            ->with('user')
            ->when($type, fn($query) => $query->where('activity_type', $type))
            ->when($userId, fn($query) => $query->where('user_id', $userId))
            ->when($q !== '', function ($query) use ($q) {
                $query->where('description', 'like', '%' . $q . '%')
                    ->orWhere('ip_address', 'like', '%' . $q . '%');
            })
            ->latest()
            ->paginate(30);

        $logs->appends($request->all());

        return view('panel.admin.logs.index', compact('logs', 'type', 'userId', 'q'));
    }

    private function ensurePermission(string $perm)
    {
        if (!Auth::user()->isAdmin() && !Auth::user()->hasPermission($perm)) {
            abort(403);
        }
    }
}
