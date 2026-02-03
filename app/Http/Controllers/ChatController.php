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
        $conversations = Auth::user()->conversations()->with('users')->get();
        return view('chat.index', compact('conversations'));
    }

    public function show(Conversation $conversation)
    {
        $this->authorize('view', $conversation);
        $messages = $conversation->messages()->with('user')->latest()->paginate(20);
        return view('chat.show', compact('conversation', 'messages'));
    }

    public function list()
    {
        $userId = Auth::id();
        // Return JSON for AJAX polling or initial load (better for Single Page feel)
        $conversations = Auth::user()->conversations()->with(['users', 'messages' => function($q){
            $q->latest()->limit(1);
        }])->get();
        return response()->json($conversations);
    }

    public function storeMessage(Request $request, Conversation $conversation)
    {
        $this->authorize('view', $conversation);
        
        $msg = $conversation->messages()->create([
            'user_id' => Auth::id(),
            'body' => $request->input('body'),
        ]);
        
        // Broadcast event...
        // broadcast(new MessageSent($msg))->toOthers();

        return response()->json($msg->load('user'));
    }
}
