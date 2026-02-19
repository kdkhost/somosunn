<?php

namespace App\Http\Controllers;

use App\Models\Connection;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

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
                Mail::raw(
                    "Voce recebeu uma solicitacao de conexao de {$requesterName}.\n\nAcesse o perfil para aceitar ou recusar: {$profileUrl}",
                    function ($message) use ($user) {
                        $message->to($user->email)->subject('Nova solicitacao de conexao');
                    }
                );
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

        // Notificar quem solicitou que foi aceito
        $user->notify(new \App\Notifications\AppNotification([
            'message' => Auth::user()->name . ' aceitou seu pedido de conexão!',
            'type' => 'ConnectionAccepted',
            'action_url' => route('chat.index'),
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
