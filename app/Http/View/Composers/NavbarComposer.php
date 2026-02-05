<?php

namespace App\Http\View\Composers;

use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;
use App\Models\Connection;
use App\Models\Message;

class NavbarComposer
{
    public function compose(View $view)
    {
        if (Auth::check()) {
            $user = Auth::user();
            
            // Pending Connections (excluding blocks if we had them, but for now just pending)
            $pendingConnectionsQ = Connection::with('requester')
                ->where('requested_id', $user->id)
                ->where('status', 'pending');
                
            $pendingConnectionsCount = $pendingConnectionsQ->count();
            $pendingConnections = $pendingConnectionsQ->latest()->take(5)->get();

            // Unread Messages Grouped by User
            $unreadMessagesGroups = collect();
            $unreadMessagesCount = 0;

            try {
                $allUnread = Message::where('user_id', '!=', $user->id)
                    ->whereHas('conversation', function($q) use ($user) {
                        $q->whereHas('users', function($u) use ($user) {
                            $u->where('users.id', $user->id);
                        });
                    })
                    ->whereNull('read_at')
                    ->with('user')
                    ->get();
                
                $unreadMessagesCount = $allUnread->count();
                
                $unreadMessagesGroups = $allUnread->groupBy('user_id')->map(function ($msgs) {
                    return (object) [
                        'user' => $msgs->first()->user,
                        'count' => $msgs->count(),
                        'latest' => $msgs->sortByDesc('created_at')->first()
                    ];
                });

            } catch (\Exception $e) {
                $unreadMessagesCount = 0;
            }

            $view->with(compact('pendingConnectionsCount', 'pendingConnections', 'unreadMessagesCount', 'unreadMessagesGroups'));
        }
    }
}
