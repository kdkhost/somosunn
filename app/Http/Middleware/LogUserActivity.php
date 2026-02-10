<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\ActivityLog;

class LogUserActivity
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        // Only log for authenticated users and non-GET requests (usually actions)
        // Or specific GET requests if critical. For now, let's log modifying actions.
        if (Auth::check() && !in_array($request->method(), ['GET', 'HEAD', 'OPTIONS'])) {
            try {
                ActivityLog::create([
                    'user_id' => Auth::id(),
                    'action' => $request->method() . ' ' . $request->path(),
                    'description' => 'User performed an action.',
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                    'properties' => $request->all(), // Be careful with sensitive data!
                ]);
            } catch (\Exception $e) {
                // Fail silently to not impact user experience
            }
        }

        return $response;
    }
}
