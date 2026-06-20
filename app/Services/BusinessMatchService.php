<?php

namespace App\Services;

use App\Models\BusinessMatch;
use App\Models\User;
use Illuminate\Support\Collection;

class BusinessMatchService
{
    public function __construct(
        private readonly MemberSuggestionService $memberSuggestionService
    ) {
    }

    public function suggestFor(User $user, int $limit = 6): Collection
    {
        $suggested = $this->memberSuggestionService->suggest($user, [], [], $limit);

        if ($suggested->isEmpty()) {
            return collect();
        }

        $items = $suggested->values()->map(function (User $matched) use ($user) {
            $score = $this->calculateScore($user, $matched);

            if (BusinessMatch::tableAvailable()) {
                BusinessMatch::query()->updateOrCreate(
                    [
                        'user_id' => $user->id,
                        'matched_user_id' => $matched->id,
                    ],
                    [
                        'score' => $score,
                        'status' => 'suggested',
                    ]
                );
            }

            return [
                'user' => $matched,
                'score' => $score,
            ];
        });

        return $items->sortByDesc('score')->values();
    }

    public function calculateScore(User $user, User $matched): int
    {
        $score = 0;

        if (filled($user->city) && strcasecmp((string) $user->city, (string) $matched->city) === 0) {
            $score += 20;
        }

        if (filled($user->state) && strcasecmp((string) $user->state, (string) $matched->state) === 0) {
            $score += 10;
        }

        if (filled($user->segment) && strcasecmp((string) $user->segment, (string) $matched->segment) === 0) {
            $score += 25;
        }

        $userInterests = collect(preg_split('/[,\s;]+/', (string) $user->interests) ?: [])
            ->filter()
            ->map(fn($item) => mb_strtolower(trim($item)))
            ->unique();
        $matchedInterests = collect(preg_split('/[,\s;]+/', (string) $matched->interests) ?: [])
            ->filter()
            ->map(fn($item) => mb_strtolower(trim($item)))
            ->unique();

        $sharedInterests = $userInterests->intersect($matchedInterests)->count();
        $score += min(30, $sharedInterests * 5);

        if (filled($user->company) && strcasecmp((string) $user->company, (string) $matched->company) === 0) {
            $score += 5;
        }

        if (filled($user->occupation) && strcasecmp((string) $user->occupation, (string) $matched->occupation) === 0) {
            $score += 10;
        }

        return min(100, $score);
    }
}
