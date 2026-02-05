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

    public function start($userId)
    {
        $targetUser = User::findOrFail($userId);
        $me = Auth::user();

        if ($targetUser->id == $me->id) {
            return redirect()->route('chat.index')->with('error', 'Você não pode conversar com você mesmo.');
        }

        // Tenta encontrar conversa existente (privada)
        // Lógica simplificada: encontrar conversa onde ambos estão e é tipo 'private' (se existir tipo)
        // Ou apenas onde ambos estão e apenas eles
        
        $conversation = $me->conversations()->whereHas('users', function($q) use ($targetUser) {
            $q->where('users.id', $targetUser->id);
        })->get()->filter(function($c) {
            return $c->users()->count() == 2;
        })->first();

        if ($conversation) {
            return redirect()->route('chat.show', $conversation->id);
        }

        // Cria nova
        $conversation = Conversation::create([
            'type' => 'private', 
            'title' => $targetUser->name // Titulo inicial
        ]);
        
        $conversation->users()->attach([$me->id, $targetUser->id]);

        return redirect()->route('chat.show', $conversation->id);
    }
    
    public function show(Conversation $conversation)
    {
        // Verifica se usuário pertence à conversa
        if (!$conversation->users->contains(Auth::id())) {
            abort(403);
        }
        
        $messages = $conversation->messages()->with('user')->latest()->limit(50)->get(); // Limit for perf
        $conversations = Auth::user()->conversations()->with('users')->latest()->get();
        
        return view('chat.show', compact('conversation', 'messages', 'conversations'));
    }

    public function list()
    {
        // ... (mantido)
        $userId = Auth::id();
        $conversations = Auth::user()->conversations()->with(['users', 'messages' => function($q){
            $q->latest()->limit(1);
        }])->get();
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
}
