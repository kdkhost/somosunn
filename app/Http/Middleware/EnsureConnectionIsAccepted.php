<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class EnsureConnectionIsAccepted
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        // If the user is an admin, they bypass the connection requirement
        if (Auth::user()->isAdmin()) {
            return $next($request);
        }

        // Check for conversation if applicable
        $conversation = $request->route('conversation');
        if ($conversation) {
            $otherUser = $conversation->users()->where('users.id', '!=', Auth::id())->first();
            if ($otherUser && !Auth::user()->isConnectedWith($otherUser->id)) {
                return redirect()->route('portal')->with('error', 'Você precisa de uma conexão aceita para conversar com este membro.');
            }
        }

        // Check for direct user chat start if applicable
        $userId = $request->route('user');
        if ($userId) {
            $otherUser = User::find($userId);
            if ($otherUser && !Auth::user()->isConnectedWith($otherUser->id)) {
                return redirect()->route('portal')->with('error', 'Solicite uma conexão primeiro.');
            }
        }

        return $next($request);
    }
}
