<?php

namespace App\Http\Controllers;

use App\Models\Interaction;
use App\Models\Satisfaction;
use App\Services\RankingService;
use Illuminate\Http\Request;

class SatisfactionController extends Controller
{
    public function __construct(private RankingService $rankingService)
    {
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'interaction_id' => 'required|integer|exists:interactions,id',
            'rating' => 'required|integer|min:1|max:5',
            'feedback' => 'nullable|string|max:600',
            'whatsapp_notified' => 'sometimes|boolean',
        ]);

        $interaction = Interaction::with(['userFrom', 'userTo'])->findOrFail($data['interaction_id']);

        if ($interaction->satisfaction) {
            return response()->json(['message' => 'Satisfação já registrada para essa conexão'], 422);
        }

        $satisfaction = Satisfaction::create([
            'interaction_id' => $interaction->id,
            'rating' => $data['rating'],
            'feedback' => $data['feedback'] ?? null,
            'whatsapp_notified' => (bool) ($data['whatsapp_notified'] ?? false),
        ]);

        $this->rankingService->refreshFromInteraction($interaction);

        return response()->json([
            'message' => 'Pesquisa registrada',
            'satisfaction' => $satisfaction,
        ], 201);
    }
}
