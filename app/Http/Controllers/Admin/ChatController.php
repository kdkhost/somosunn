<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Conversation;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ChatController extends Controller
{
    public function index()
    {
        $conversations = Auth::user()->conversations()
            ->with(['users'])
            ->withCount([
                'messages as unread_count' => function ($q) {
                    $q->where('user_id', '!=', Auth::id())->whereNull('read_at');
                },
            ])
            ->latest()
            ->get();

        return view('chat.index', [
            'conversations' => $conversations,
            'extends' => 'admin.layouts.app',
            'routeNamePrefix' => 'admin.chat',
        ]);
    }

    public function start($userId)
    {
        $targetUser = User::findOrFail($userId);
        $me = Auth::user();

        if ($targetUser->id == $me->id) {
            return redirect()->route('admin.chat.index')->with('error', 'Você não pode conversar com você mesmo.');
        }

        $conversation = $me->conversations()
            ->whereHas('users', function ($q) use ($targetUser) {
                $q->where('users.id', $targetUser->id);
            })
            ->get()
            ->filter(function ($c) {
                return $c->users()->count() == 2;
            })
            ->first();

        if ($conversation) {
            return redirect()->route('admin.chat.show', $conversation->id);
        }

        $conversation = Conversation::create([
            'type' => 'private',
            'title' => $targetUser->name,
        ]);

        $conversation->users()->attach([$me->id, $targetUser->id]);

        return redirect()->route('admin.chat.show', $conversation->id);
    }

    public function show(Conversation $conversation)
    {
        if (!$conversation->users->contains(Auth::id())) {
            abort(403);
        }

        $conversation->messages()
            ->where('user_id', '!=', Auth::id())
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        $messages = $conversation->messages()->with('user')->latest()->limit(50)->get();

        $conversations = Auth::user()->conversations()
            ->with(['users'])
            ->withCount([
                'messages as unread_count' => function ($q) {
                    $q->where('user_id', '!=', Auth::id())->whereNull('read_at');
                },
            ])
            ->latest()
            ->get();

        return view('chat.show', [
            'conversation' => $conversation,
            'messages' => $messages,
            'conversations' => $conversations,
            'extends' => 'admin.layouts.app',
            'routeNamePrefix' => 'admin.chat',
        ]);
    }

    public function list()
    {
        $conversations = Auth::user()->conversations()
            ->with([
                'users',
                'messages' => function ($q) {
                    $q->latest()->limit(1);
                },
            ])
            ->withCount([
                'messages as unread_count' => function ($q) {
                    $q->where('user_id', '!=', Auth::id())->whereNull('read_at');
                },
            ])
            ->get();

        return response()->json($conversations);
    }

    public function storeMessage(Request $request, Conversation $conversation)
    {
        if (!$conversation->users->contains(Auth::id())) {
            abort(403);
        }

        $request->validate(['body' => 'required']);

        $msg = $conversation->messages()->create([
            'user_id' => Auth::id(),
            'body' => $request->input('body'),
            'type' => 'text',
        ]);

        return response()->json($msg->load('user'));
    }

    public function getMessages(Conversation $conversation)
    {
        if (!$conversation->users->contains(Auth::id())) {
            abort(403);
        }

        $conversation->messages()
            ->where('user_id', '!=', Auth::id())
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        $messages = $conversation->messages()->with('user')->latest()->limit(50)->get();

        return response()->json($messages);
    }
}

