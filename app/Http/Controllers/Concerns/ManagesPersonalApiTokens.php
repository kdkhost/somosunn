<?php

namespace App\Http\Controllers\Concerns;

use App\Models\User;
use Illuminate\Support\Facades\Schema;
use Laravel\Sanctum\PersonalAccessToken;

trait ManagesPersonalApiTokens
{
    protected function apiTokensForUser(User $user)
    {
        if (!$this->hasPersonalAccessTokensTable()) {
            return collect();
        }

        $columns = ['id', 'name', 'last_used_at', 'created_at'];

        if ($this->hasPersonalAccessTokenColumn('last_used_ip')) {
            $columns[] = 'last_used_ip';
        }

        return $user->tokens()
            ->latest('id')
            ->get($columns)
            ->map(function (PersonalAccessToken $token) {
                if (!isset($token->last_used_ip)) {
                    $token->last_used_ip = null;
                }

                return $token;
            });
    }

    protected function resolveOwnedToken(User $user, int $tokenId): PersonalAccessToken
    {
        abort_unless($this->hasPersonalAccessTokensTable(), 404);

        return $user->tokens()
            ->whereKey($tokenId)
            ->firstOrFail();
    }

    protected function hasPersonalAccessTokensTable(): bool
    {
        try {
            return Schema::hasTable('personal_access_tokens');
        } catch (\Throwable) {
            return false;
        }
    }

    protected function hasPersonalAccessTokenColumn(string $column): bool
    {
        if (!$this->hasPersonalAccessTokensTable()) {
            return false;
        }

        try {
            return Schema::hasColumn('personal_access_tokens', $column);
        } catch (\Throwable) {
            return false;
        }
    }
}
