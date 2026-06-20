<?php

namespace App\Services;

use App\Models\CrmScore;
use App\Models\User;

class CrmScoreService
{
    public const CATEGORY_COLD = 'Lead Frio';
    public const CATEGORY_WARM = 'Lead Morno';
    public const CATEGORY_HOT = 'Lead Quente';
    public const CATEGORY_CLIENT = 'Cliente';
    public const CATEGORY_AMBASSADOR = 'Embaixador';

    public const POINTS = [
        'cadastro' => 10,
        'login' => 10,
        'conexao' => 20,
        'evento' => 30,
        'compra' => 40,
        'curso' => 50,
        'marketplace' => 60,
        'patrocinador' => 100,
    ];

    public function record(User $user, string $activity, ?int $points = null): CrmScore
    {
        $score = CrmScore::query()->firstOrCreate(
            ['user_id' => $user->id],
            ['score' => 0, 'category' => self::CATEGORY_COLD]
        );

        $score->score += $points ?? (self::POINTS[$activity] ?? 0);
        $score->last_activity = now();
        $score->category = $this->categoryFor($score->score);
        $score->save();

        return $score;
    }

    public function categoryFor(int $score): string
    {
        return match (true) {
            $score >= 200 => self::CATEGORY_AMBASSADOR,
            $score >= 120 => self::CATEGORY_CLIENT,
            $score >= 80 => self::CATEGORY_HOT,
            $score >= 30 => self::CATEGORY_WARM,
            default => self::CATEGORY_COLD,
        };
    }
}
