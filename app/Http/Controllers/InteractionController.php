<?php

namespace App\Http\Controllers;

use App\Models\Interaction;
use App\Models\User;
use Illuminate\Http\Request;

class InteractionController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'user_from_id' => 'required|integer|exists:users,id',
            'user_to_id' => 'required|integer|exists:users,id|different:user_from_id',
            'message' => 'nullable|string|max:500',
            'meta' => 'nullable|array',
        ]);

        $from = User::findOrFail($data['user_from_id']);
        $to = User::findOrFail($data['user_to_id']);

        if ($from->level !== $to->level) {
            return response()->json([
                'message' => 'Conexão somente entre empreendedores do mesmo nível.',
            ], 422);
        }

        $interaction = Interaction::create([
            'user_from_id' => $from->id,
            'user_to_id' => $to->id,
            'level' => $from->level,
            'message' => $data['message'] ?? null,
            'meta' => $data['meta'] ?? null,
        ]);

        // Notificar o destinatário da interação
        $to->notify(new \App\Notifications\AppNotification([
            'message' => $from->name . ' registrou uma nova conexão com você.',
            'type' => 'NewInteraction',
            'action_url' => route('social.feed'),
            'action_label' => 'Ver detalhes'
        ]));

        return response()->json([
            'message' => 'Conexão registrada',
            'interaction' => $interaction,
        ], 201);
    }
}
