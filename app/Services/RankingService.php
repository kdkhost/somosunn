<?php

namespace App\Services;

use App\Models\Interaction;
use App\Models\Ranking;
use App\Models\Satisfaction;
use App\Models\User;

class RankingService
{
    public function refreshFor(User $user): Ranking
    {
        $stats = Satisfaction::query()
            ->whereHas('interaction', fn ($query) => $query->where('user_to_id', $user->id))
            ->selectRaw('count(*) as total, avg(rating) as average')
            ->first();

        $total = (int) ($stats->total ?? 0);
        $average = (float) ($stats->average ?? 0);
        $score = $this->calculateScore($total, $average);

        return Ranking::updateOrCreate(
            ['user_id' => $user->id],
            [
                'level' => $user->level,
                'interactions_count' => $total,
                'average_rating' => number_format($average, 2, '.', ''),
                'score' => number_format($score, 2, '.', ''),
            ]
        );
    }

    public function refreshFromInteraction(Interaction $interaction): array
    {
        $updated = [];
        foreach ([$interaction->user_from, $interaction->user_to] as $user) {
            if ($user) {
                $updated[] = $this->refreshFor($user);
            }
        }
        return $updated;
    }

    private function calculateScore(int $total, float $average): float
    {
        if ($total === 0 || $average === 0) {
            return 0.0;
        }
        return ($average * 20) + (log($total + 1) * 5);
    }
}
