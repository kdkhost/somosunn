<?php

namespace App\Http\Middleware;

use Closure;

class CheckPermission
{
    public function handle($request, Closure $next, $permission)
    {
        $user = auth()->user();
        if(!$user || !$user->hasPermission($permission)){
            abort(403, 'Permissão negada.');
        }
        return $next($request);
    }
}
