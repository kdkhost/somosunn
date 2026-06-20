<?php

namespace App\Services;

use App\Models\Sponsor;
use App\Models\SponsorLead;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class SponsorLeadService
{
    public function register(Sponsor $sponsor, User $user, array $payload = []): SponsorLead
    {
        return SponsorLead::query()->firstOrCreate(
            [
                'sponsor_id' => $sponsor->id,
                'user_id' => $user->id,
                'event_id' => $payload['event_id'] ?? null,
                'source' => $payload['source'] ?? 'manual',
            ],
            [
                'consent' => (bool) ($payload['consent'] ?? false),
            ]
        );
    }

    public function paginatedForSponsor(Sponsor $sponsor, int $perPage = 15): LengthAwarePaginator
    {
        return $sponsor->leads()
            ->with(['user', 'event'])
            ->latest('id')
            ->paginate($perPage);
    }
}
