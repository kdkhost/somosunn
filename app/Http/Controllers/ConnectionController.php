<?php

namespace App\Http\Controllers;

use App\Models\Connection;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ConnectionController extends Controller
{
    /**
     * Send connection request
     */
    public function connect($userId)
    {
        try {
            $user = Auth::user();
            
            if ($user->id == $userId) {
                return response()->json(['success' => false, 'message' => 'Ação inválida.']);
            }

            $targetUser = User::find($userId);
            if (!$targetUser || $targetUser->role === 'superadmin') {
                return response()->json(['success' => false, 'message' => 'Usuário não disponível para conexão.'], 404);
            }

            // Check if connection exists
            $existing = Connection::where(function($q) use ($user, $userId) {
                $q->where('requester_id', $user->id)->where('requested_id', $userId);
            })->orWhere(function($q) use ($user, $userId) {
                $q->where('requester_id', $userId)->where('requested_id', $user->id);
            })->first();

            if ($existing) {
                if ($existing->status == 'accepted') {
                    return response()->json(['success' => false, 'message' => 'Vocês já são conectados.'], 400);
                }
                if ($existing->status == 'pending') {
                    return response()->json(['success' => false, 'message' => 'Solicitação já existente.'], 400);
                }
                if ($existing->status == 'rejected') {
                    // Update to pending again
                    $existing->update([
                        'requester_id' => $user->id,
                        'requested_id' => $userId,
                        'status' => 'pending'
                    ]);
                    return response()->json(['success' => true, 'message' => 'Nova solicitação enviada!']);
                }
            }

            Connection::create([
                'requester_id' => $user->id,
                'requested_id' => $userId,
                'status' => 'pending'
            ]);

            return response()->json(['success' => true, 'message' => 'Solicitação enviada!', 'status' => 'pending']);

        } catch (\Exception $e) {
            \Log::error('Connection error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Erro ao conectar.'], 500);
        }
    }
    
    /**
     * Accept connection request
     */
    public function accept($userId)
    {
        $user = Auth::user();
        
        $connection = Connection::where('requester_id', $userId)
            ->where('requested_id', $user->id)
            ->where('status', 'pending')
            ->first();

        if ($connection) {
            $connection->update(['status' => 'accepted']);
            return response()->json(['success' => true, 'message' => 'Conexão aceita!']);
        }
        
        return response()->json(['success' => false, 'message' => 'Solicitação não encontrada.'], 404);
    }

    /**
     * Remove/Reject connection
     */
    public function remove($userId)
    {
        $user = Auth::user();
        
        $connection = Connection::where(function($q) use ($user, $userId) {
            $q->where('requester_id', $user->id)->where('requested_id', $userId);
        })->orWhere(function($q) use ($user, $userId) {
            $q->where('requester_id', $userId)->where('requested_id', $user->id);
        })->first();

        if ($connection) {
            $connection->delete();
            return response()->json(['success' => true, 'message' => 'Conexão removida.']);
        }
        
        return response()->json(['success' => false, 'message' => 'Conexão não encontrada.'], 404);
    }
}
