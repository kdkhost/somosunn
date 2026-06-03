<?php

namespace App\Http\Controllers;

use App\Models\Connection;
use App\Models\User;
use App\Services\Mail\SystemMailTemplateService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ConnectionController extends Controller
{
    /**
     * Enviar solicitação de conexão.
     */
    public function connect(User $user)
    {
        $requesterId = Auth::id();
        $requestedId = $user->id;

        if ($requesterId === $requestedId) {
            return response()->json(['success' => false, 'message' => 'Você não pode se conectar consigo mesmo.']);
        }

        // Verifica se já existe solicitação ou conexão
        $existing = Connection::where(function ($q) use ($requesterId, $requestedId) {
            $q->where('requester_id', $requesterId)->where('requested_id', $requestedId);
        })->orWhere(function ($q) use ($requesterId, $requestedId) {
            $q->where('requester_id', $requestedId)->where('requested_id', $requesterId);
        })->first();

        if ($existing) {
            return response()->json(['success' => false, 'message' => 'Já existe uma solicitação ou conexão ativa.']);
        }

        Connection::create([
            'requester_id' => $requesterId,
            'requested_id' => $requestedId,
            'status' => 'pending'
        ]);

        // Notificar via Painel Geral
        $user->notify(new \App\Notifications\AppNotification([
            'message' => Auth::user()->name . ' enviou uma solicitação de conexão para você.',
            'type' => 'ConnectionRequest',
            'action_url' => route('social.feed'),
            'action_label' => 'Ver solicitações'
        ]));

        if (!empty($user->email)) {
            try {
                $requesterName = Auth::user()->name;
                $profileUrl = route('social.profile', $requesterId);
                app(SystemMailTemplateService::class)->send('connection_request', $user->email, [
                    'user' => ['name' => $user->name],
                    'requester' => ['name' => $requesterName, 'profile_url' => $profileUrl],
                ], [
                    'name' => 'Solicitacao de Conexao',
                    'category' => 'conta',
                    'subject' => 'Nova solicitacao de conexao',
                    'body' => '<h2>Ola, {{user.name}}!</h2><p><strong>{{requester.name}}</strong> enviou uma solicitacao de conexao.</p><p><a href="{{requester.profile_url}}">Ver perfil e responder</a></p>',
                ]);
            } catch (\Throwable $e) {
                \Log::warning('Falha ao enviar email de conexao: ' . $e->getMessage());
            }
        }

        return response()->json(['success' => true, 'message' => 'Solicitação de conexão enviada!']);
    }

    /**
     * Aceitar solicitação de conexão.
     */
    public function accept(User $user)
    {
        $connection = Connection::where('requester_id', $user->id)
            ->where('requested_id', Auth::id())
            ->where('status', 'pending')
            ->firstOrFail();

        $connection->update([
            'status' => 'accepted',
            'responded_at' => now()
        ]);

        // Cria conversa privada se não existir
        $me = Auth::user();
        $conversation = \App\Models\Conversation::query()
            ->privateBetween((int) $me->id, (int) $user->id)
            ->first();

        if (!$conversation) {
            $conversation = \App\Models\Conversation::create([
                'type' => 'private',
                'title' => $user->name
            ]);
            $conversation->users()->attach([$me->id, $user->id]);
        }

        // Notificar quem solicitou que foi aceito
        $user->notify(new \App\Notifications\AppNotification([
            'message' => $me->name . ' aceitou seu pedido de conexão!',
            'type' => 'ConnectionAccepted',
            'action_url' => route('chat.show', $conversation->id),
            'action_label' => 'Conversar agora'
        ]));

        return response()->json(['success' => true, 'message' => 'Conexão aceita! Agora vocês podem conversar.']);
    }

    /**
     * Remover conexão ou recusar solicitação.
     */
    public function remove(User $user)
    {
        $requesterId = Auth::id();
        $requestedId = $user->id;

        $connection = Connection::where(function ($q) use ($requesterId, $requestedId) {
            $q->where('requester_id', $requesterId)->where('requested_id', $requestedId);
        })->orWhere(function ($q) use ($requesterId, $requestedId) {
            $q->where('requester_id', $requestedId)->where('requested_id', $requesterId);
        })->first();

        if ($connection) {
            $connection->delete();
            return response()->json(['success' => true, 'message' => 'Conexão removida.']);
        }

        return response()->json(['success' => false, 'message' => 'Conexão não encontrada.']);
    }

    /**
     * Bloquear um usuário.
     */
    public function block(User $user)
    {
        $requesterId = Auth::id();
        $requestedId = $user->id;

        $connection = Connection::where(function ($q) use ($requesterId, $requestedId) {
            $q->where('requester_id', $requesterId)->where('requested_id', $requestedId);
        })->orWhere(function ($q) use ($requesterId, $requestedId) {
            $q->where('requester_id', $requestedId)->where('requested_id', $requesterId);
        })->first();

        if ($connection) {
            $connection->update([
                'status' => 'blocked',
                'responded_at' => now()
            ]);
        } else {
            Connection::create([
                'requester_id' => $requesterId,
                'requested_id' => $requestedId,
                'status' => 'blocked',
                'responded_at' => now()
            ]);
        }

        return response()->json(['success' => true, 'message' => 'Usuário bloqueado.']);
    }

    /**
     * Lista de usuarios bloqueados pelo usuario autenticado.
     */
    public function blockedUsers()
    {
        $userId = Auth::id();

        $blockedConnections = Connection::where('status', 'blocked')
            ->where(function ($q) use ($userId) {
                $q->where('requester_id', $userId)->orWhere('requested_id', $userId);
            })
            ->get();

        $blockedUsers = $blockedConnections->map(function ($conn) use ($userId) {
            $blockedId = $conn->requester_id === $userId ? $conn->requested_id : $conn->requester_id;
            $user = User::find($blockedId);
            if (!$user) return null;

            return (object) [
                'connection_id' => $conn->id,
                'user'          => $user,
                'blocked_at'    => $conn->responded_at ?? $conn->updated_at,
            ];
        })->filter()->values();

        return view('panel.connections.blocked', compact('blockedUsers'));
    }

    /**
     * Desbloquear um usuario.
     */
    public function unblock(User $user)
    {
        $requesterId = Auth::id();
        $requestedId = $user->id;

        $connection = Connection::where('status', 'blocked')
            ->where(function ($q) use ($requesterId, $requestedId) {
                $q->where('requester_id', $requesterId)->where('requested_id', $requestedId);
            })->orWhere(function ($q) use ($requesterId, $requestedId) {
                $q->where('requester_id', $requestedId)->where('requested_id', $requesterId);
            })->first();

        if (!$connection) {
            return response()->json(['success' => false, 'message' => 'Conexão bloqueada não encontrada.']);
        }

        // Remove a conexão completamente (volta ao estado "sem conexão")
        $connection->delete();

        return response()->json(['success' => true, 'message' => 'Usuário desbloqueado com sucesso.']);
    }

    /**
     * Get pending notifications count.
     */
    public function notifications()
    {
        $connectionsCount = Connection::where('requested_id', Auth::id())
            ->where('status', 'pending')
            ->count();

        $messagesCount = \App\Models\Message::where('user_id', '!=', Auth::id())
            ->whereHas('conversation', function ($q) {
                $q->whereHas('users', function ($uq) {
                    $uq->where('users.id', Auth::id());
                });
            })
            ->whereNull('read_at')
            ->count();

        return response()->json([
            'count' => $connectionsCount + $messagesCount,
            'connections' => $connectionsCount,
            'messages' => $messagesCount
        ]);
    }
}
