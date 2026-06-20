<?php

namespace App\Policies;

use App\Models\Sponsor;
use App\Models\User;

class SponsorPolicy
{
    public function view(User $user, Sponsor $sponsor): bool
    {
        return $user->isAdmin()
            || $sponsor->company?->users()->where('users.id', $user->id)->exists();
    }

    public function accessPanel(User $user, Sponsor $sponsor): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        if (!$sponsor->company) {
            return false;
        }

        return $sponsor->company->memberships()
            ->where('user_id', $user->id)
            ->whereIn('role', ['owner', 'manager'])
            ->exists();
    }
}
