<?php

namespace App\Services;

use App\Models\Setting;
use App\Models\User;

class OrderControlCopyRecipientService
{
    /**
     * Retorna exatamente o administrador principal e o superadministrador.
     *
     * @return array<int, string>
     */
    public function emails(): array
    {
        $admin = $this->configuredAdmin() ?? User::query()
            ->where('role', 'admin')
            ->oldest('id')
            ->first();

        $superadmin = User::query()
            ->where('role', 'superadmin')
            ->oldest('id')
            ->first();

        return collect([$admin?->email, $superadmin?->email])
            ->map(fn ($email) => strtolower(trim((string) $email)))
            ->filter(fn (string $email) => filter_var($email, FILTER_VALIDATE_EMAIL))
            ->unique()
            ->values()
            ->all();
    }

    private function configuredAdmin(): ?User
    {
        foreach (['platform_admin_user_id', 'platform_owner_id'] as $key) {
            $userId = (int) Setting::get($key, 0);
            if ($userId <= 0) {
                continue;
            }

            $user = User::query()->find($userId);
            if ($user && $user->role === 'admin') {
                return $user;
            }
        }

        return null;
    }
}
