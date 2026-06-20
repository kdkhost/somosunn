<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Events\Verified;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class AdminUserService
{
    public function create(array $data): User
    {
        $emailVerified = (bool) Arr::pull($data, 'email_verified', false);
        Arr::forget($data, 'person_type');

        $data['password'] = Hash::make($data['password']);
        $data['extra_features'] = $data['extra_features'] ?? [];
        $data = $this->normalizePlatformFeeFlag($data);

        $user = new User();
        $user->fill($data);
        $user->forceFill(['email_verified_at' => $emailVerified ? now() : null]);
        $user->save();

        if (!$emailVerified) {
            $this->sendVerificationForNewUser($user);
        }

        return $user;
    }

    public function update(User $user, array $data): User
    {
        $emailVerified = (bool) Arr::pull($data, 'email_verified', false);
        Arr::forget($data, 'person_type');

        if (!empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        $data['extra_features'] = $data['extra_features'] ?? [];
        $data = $this->normalizePlatformFeeFlag($data, $user);
        $emailChanged = mb_strtolower((string) $user->email) !== $data['email'];

        $user->fill($data);
        $user->forceFill([
            'email_verified_at' => $emailVerified
                ? ($emailChanged || !$user->email_verified_at ? now() : $user->email_verified_at)
                : null,
        ]);
        $user->save();

        if ($emailChanged && !$emailVerified) {
            $this->sendVerificationNotification($user);
        }

        return $user->refresh();
    }

    public function verifyEmail(User $user): bool
    {
        if ($user->hasVerifiedEmail()) {
            return false;
        }

        if (!$user->markEmailAsVerified()) {
            return false;
        }

        event(new Verified($user));

        return true;
    }

    private function sendVerificationForNewUser(User $user): void
    {
        try {
            event(new Registered($user));
        } catch (\Throwable $exception) {
            Log::error('Falha ao enviar verificação de e-mail ao novo usuário.', [
                'user_id' => $user->id,
                'error' => $exception->getMessage(),
            ]);
        }
    }

    private function sendVerificationNotification(User $user): void
    {
        try {
            $user->sendEmailVerificationNotification();
        } catch (\Throwable $exception) {
            Log::error('Falha ao enviar verificação do novo e-mail.', [
                'user_id' => $user->id,
                'error' => $exception->getMessage(),
            ]);
        }
    }

    private function normalizePlatformFeeFlag(array $data, ?User $user = null): array
    {
        $role = (string) ($data['role'] ?? $user?->role ?? 'member');
        $isSuperadmin = $role === 'superadmin' || ($data['level'] ?? $user?->level) === 'superadmin';
        $isAdmin = $role === 'admin';
        $isMarketingManager = $user?->isMarketingManager() ?? false;

        if ($isSuperadmin || $isAdmin || $isMarketingManager) {
            $data['platform_fee_enabled'] = false;

            return $data;
        }

        $data['platform_fee_enabled'] = array_key_exists('platform_fee_enabled', $data)
            ? (bool) $data['platform_fee_enabled']
            : (bool) ($user?->platform_fee_enabled ?? true);

        return $data;
    }
}
