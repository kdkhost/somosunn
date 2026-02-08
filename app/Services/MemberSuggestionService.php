<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Collection;

class MemberSuggestionService
{
    public function suggest(User $authUser, array $blockedUserIds, array $connectedUserIds, int $limit = 6): Collection
    {
        $excludedIds = array_unique(array_merge([$authUser->id], $blockedUserIds, $connectedUserIds));

        $query = User::whereNotIn('id', $excludedIds)
            ->where('role', '!=', 'superadmin');

        if (!$authUser->isAdmin()) {
            $query->where(function ($q) use ($connectedUserIds) {
                $q->where('hide_profile', false)
                    ->orWhereIn('id', $connectedUserIds);
            });
        }

        $candidates = $query
            ->limit(max($limit * 5, 20))
            ->get([
                'id',
                'name',
                'photo',
                'occupation',
                'company',
                'city',
                'state',
                'segment',
                'interests',
            ]);

        if ($candidates->isEmpty()) {
            return collect();
        }

        $authProfile = $this->buildProfileTokens($authUser);
        $scored = $candidates->map(function (User $user) use ($authProfile) {
            $score = $this->scoreProfile($authProfile, $user);
            return [
                'user' => $user,
                'score' => $score,
            ];
        });

        $maxScore = (int) $scored->max('score');
        if ($maxScore <= 0) {
            return $candidates->shuffle()->take($limit)->values();
        }

        $grouped = $scored->groupBy('score')->sortKeysDesc();
        $ordered = collect();
        foreach ($grouped as $group) {
            $ordered = $ordered->merge($group->shuffle());
        }

        $primaryCount = (int) ceil($limit * 0.7);
        $primary = $ordered->pluck('user')->take($primaryCount);

        $primaryIds = $primary->pluck('id')->all();
        $secondary = $candidates
            ->reject(function (User $user) use ($primaryIds) {
                return in_array($user->id, $primaryIds, true);
            })
            ->shuffle()
            ->take(max(0, $limit - $primary->count()));

        return $primary->merge($secondary)->take($limit)->values();
    }

    private function buildProfileTokens(User $user): array
    {
        return [
            'city' => $this->normalize($user->city ?? ''),
            'state' => $this->normalize($user->state ?? ''),
            'occupation' => $this->normalize($user->occupation ?? ''),
            'company' => $this->normalize($user->company ?? ''),
            'segment' => $this->normalize($user->segment ?? ''),
            'interests' => $this->tokenize((string) ($user->interests ?? '')),
        ];
    }

    private function scoreProfile(array $authProfile, User $user): int
    {
        $score = 0;

        $city = $this->normalize($user->city ?? '');
        $state = $this->normalize($user->state ?? '');
        $occupation = $this->normalize($user->occupation ?? '');
        $company = $this->normalize($user->company ?? '');
        $segment = $this->normalize($user->segment ?? '');
        $interests = $this->tokenize((string) ($user->interests ?? ''));

        if ($authProfile['city'] !== '' && $authProfile['city'] === $city) {
            $score += 4;
        }
        if ($authProfile['state'] !== '' && $authProfile['state'] === $state) {
            $score += 2;
        }
        if ($authProfile['occupation'] !== '' && $authProfile['occupation'] === $occupation) {
            $score += 3;
        }
        if ($authProfile['company'] !== '' && $authProfile['company'] === $company) {
            $score += 2;
        }
        if ($authProfile['segment'] !== '' && $authProfile['segment'] === $segment) {
            $score += 3;
        }

        if (!empty($authProfile['interests']) && !empty($interests)) {
            $shared = array_intersect($authProfile['interests'], $interests);
            $score += min(3, count($shared));
        }

        return $score;
    }

    private function normalize(string $value): string
    {
        return strtolower(trim($value));
    }

    private function tokenize(string $value): array
    {
        $value = strtolower($value);
        $value = preg_replace('/[^a-z0-9,;\s\-]/', ' ', $value);
        $parts = preg_split('/[;,\n\r]+/', $value) ?: [];
        $tokens = [];

        foreach ($parts as $part) {
            $chunk = trim($part);
            if ($chunk === '') {
                continue;
            }
            $words = preg_split('/\s+/', $chunk) ?: [];
            foreach ($words as $word) {
                $word = trim($word);
                if (strlen($word) < 2) {
                    continue;
                }
                $tokens[] = $word;
            }
        }

        $tokens = array_values(array_unique($tokens));

        return $tokens;
    }
}
