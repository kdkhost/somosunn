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
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['message' => 'Não autenticado.'], 401);
            }
            return redirect()->route('login');
        }

        // If the user is an admin, they bypass the connection requirement
        if (Auth::user()->isAdmin()) {
            return $next($request);
        }

        // Check for conversation if applicable
        $conversation = $request->route('conversation');
        if ($conversation) {
            // Allow if user is part of the conversation (especially for groups)
            if ($conversation->users->contains(Auth::id())) {
                return $next($request);
            }

            $otherUser = $conversation->users()->where('users.id', '!=', Auth::id())->first();
            if ($otherUser && !Auth::user()->isConnectedWith($otherUser->id)) {
                if ($request->expectsJson() || $request->ajax()) {
                    return response()->json(['message' => 'Você precisa de uma conexão aceita para conversar com este membro.'], 403);
                }
                return redirect()->route('chat.index')->with('error', 'Você precisa de uma conexão aceita para conversar com este membro.');
            }
        }

        // Check for direct user chat start if applicable
        $userId = $request->route('user');
        if ($userId) {
            $id = $userId instanceof User ? $userId->id : $userId;

            // Allow if a conversation already exists
            $hasConversation = Auth::user()->conversations()->whereHas('users', function ($q) use ($id) {
                $q->where('users.id', $id);
            })->exists();

            if (!$hasConversation && !Auth::user()->isConnectedWith($id)) {
                if ($request->expectsJson() || $request->ajax()) {
                    return response()->json(['message' => 'Solicite uma conexão primeiro.'], 403);
                }
                return redirect()->route('chat.index')->with('error', 'Solicite uma conexão primeiro.');
            }
        }

        return $next($request);
    }
}
