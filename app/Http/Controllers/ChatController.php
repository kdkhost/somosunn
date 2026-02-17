<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use App\Models\Message;
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
                }
            ])
            ->latest()
            ->get();
        return view('chat.index', compact('conversations'));
    }

    public function start($userId)
    {
        $targetUser = User::findOrFail($userId);
        $me = Auth::user();

        if ($targetUser->id == $me->id) {
            return redirect()->route('chat.index')->with('error', 'Você não pode conversar com você mesmo.');
        }

        $conversation = $me->conversations()->whereHas('users', function ($q) use ($targetUser) {
            $q->where('users.id', $targetUser->id);
        })->get()->filter(function ($c) {
            return $c->users()->count() == 2;
        })->first();

        if ($conversation) {
            return redirect()->route('chat.show', $conversation->id);
        }

        $conversation = Conversation::create([
            'type' => 'private',
            'title' => $targetUser->name
        ]);

        $conversation->users()->attach([$me->id, $targetUser->id]);

        return redirect()->route('chat.show', $conversation->id);
    }

    public function show(Conversation $conversation)
    {
        if (!$conversation->users->contains(Auth::id())) {
            abort(403);
        }

        // Mark messages as read
        $conversation->messages()
            ->where('user_id', '!=', Auth::id())
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        $messages = $conversation->messages()->with('user')->latest()->limit(50)->get();

        if (request()->ajax() || request()->expectsJson()) {
            return view('chat.partials.conversation', compact('conversation', 'messages'))->render();
        }

        $conversations = Auth::user()->conversations()
            ->with(['users'])
            ->withCount([
                'messages as unread_count' => function ($q) {
                    $q->where('user_id', '!=', Auth::id())->whereNull('read_at');
                }
            ])
            ->latest()
            ->get();

        return view('chat.show', compact('conversation', 'messages', 'conversations'));
    }

    public function list()
    {
        // Se não for requisição AJAX, redireciona para a página de chat
        if (!request()->expectsJson() && !request()->ajax()) {
            return redirect()->route('chat.index');
        }

        $userId = Auth::id();
        $conversations = Auth::user()->conversations()->with([
            'users',
            'messages' => function ($q) {
                $q->latest()->limit(1);
            }
        ])
            ->withCount([
                'messages as unread_count' => function ($q) {
                    $q->where('user_id', '!=', Auth::id())->whereNull('read_at');
                }
            ])
            ->get()
            ->map(function ($conv) {
                // Garante que conv é objeto, não Collection
                return $conv;
            })
            ->values();
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
            'type' => 'text'
        ]);

        return response()->json($msg->load('user'));
    }

    public function getMessages(Conversation $conversation)
    {
        if (!$conversation->users->contains(Auth::id())) {
            abort(403);
        }

        // Mark messages as read if polling while in the active chat
        $conversation->messages()
            ->where('user_id', '!=', Auth::id())
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        $messages = $conversation->messages()->with('user')->latest()->limit(50)->get();
        return response()->json($messages);
    }

    /**
     * Floating Chat: Get or start conversation and load messages.
     */
    public function withUser(User $user)
    {
        $me = Auth::user();

        $conversation = $me->conversations()->whereHas('users', function ($q) use ($user) {
            $q->where('users.id', $user->id);
        })->get()->filter(function ($c) {
            return $c->users()->count() == 2;
        })->first();

        if (!$conversation) {
            $conversation = Conversation::create([
                'type' => 'private',
                'title' => $user->name
            ]);
            $conversation->users()->attach([$me->id, $user->id]);
        }

        // Mark as read
        $conversation->messages()
            ->where('user_id', '!=', Auth::id())
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        $messages = $conversation->messages()
            ->with('user')
            ->latest()
            ->limit(30)
            ->get();

        return response()->json([
            'conversation' => $conversation,
            'messages' => $messages->map(fn($m) => [
                'id' => $m->id,
                'content' => $m->body,
                'type' => $m->type,
                'media_path' => $m->media_path,
                'is_mine' => $m->user_id === $me->id,
                'created_at' => $m->created_at
            ])
        ]);
    }

    /**
     * Floating Chat: Send message by user_id.
     */
    public function storeMessageWithUser(Request $request, User $user)
    {
        $me = Auth::user();

        $conversation = $me->conversations()->whereHas('users', function ($q) use ($user) {
            $q->where('users.id', $user->id);
        })->get()->filter(function ($c) {
            return $c->users()->count() == 2;
        })->first();

        if (!$conversation) {
            return response()->json(['success' => false, 'message' => 'Conversa não iniciada.']);
        }

        $request->validate(['message' => 'required']);

        $msg = $conversation->messages()->create([
            'user_id' => $me->id,
            'body' => $request->input('message'),
            'type' => 'text'
        ]);

        return response()->json(['success' => true, 'message' => $msg]);
    }
}
