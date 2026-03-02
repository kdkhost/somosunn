<?php

namespace App\Services;

use App\Models\PointsRule;
use App\Models\PointsLog;
use App\Models\User;

class PointsService
{
    /**
     * Award points to a user for the given action key.
     * Enforces repeatable=false deduplication and max_daily limits defined in the rules table.
     *
     * @return bool  true if points were awarded, false if blocked by a guard or rule not found.
     */
    public function award(User $user, string $actionKey, $meta = null): bool
    {
        $rule = PointsRule::where('key', $actionKey)->where('active', true)->first();
        if (!$rule) {
            return false;
        }

        // Guard: non-repeatable actions can only be awarded once per user lifetime
        if (!$rule->repeatable) {
            if (PointsLog::where('user_id', $user->id)->where('action_key', $actionKey)->exists()) {
                return false;
            }
        }

        // Guard: max_daily limits how many times an action can be awarded on a single calendar day
        if ($rule->max_daily) {
            $todayCount = PointsLog::where('user_id', $user->id)
                ->where('action_key', $actionKey)
                ->whereDate('created_at', now()->toDateString())
                ->count();

            if ($todayCount >= (int) $rule->max_daily) {
                return false;
            }
        }

        $points = (int) $rule->points;

        PointsLog::create([
            'user_id'    => $user->id,
            'action_key' => $actionKey,
            'points'     => $points,
            'meta'       => is_null($meta) ? null : json_encode($meta),
        ]);

        $user->increment('points', $points);

        return true;
    }

    /**
     * Revoke points for the most recent matching log entry.
     */
    public function revoke(User $user, string $actionKey, $meta = null): bool
    {
        $log = PointsLog::where('user_id', $user->id)->where('action_key', $actionKey)->latest()->first();
        if (!$log) {
            return false;
        }

        $user->decrement('points', $log->points);
        $log->delete();

        return true;
    }

    /**
     * Calculate the current login streak (consecutive days ending today/yesterday)
     * based on daily_login entries in the points log.
     */
    public function calculateLoginStreak(User $user): int
    {
        $dates = PointsLog::where('user_id', $user->id)
            ->where('action_key', 'daily_login')
            ->selectRaw('DATE(created_at) as login_date')
            ->groupBy('login_date')
            ->orderByDesc('login_date')
            ->pluck('login_date')
            ->map(fn ($d) => \Carbon\Carbon::parse($d)->startOfDay())
            ->values();

        if ($dates->isEmpty()) {
            return 0;
        }

        $streak = 1;

        for ($i = 1; $i < $dates->count(); $i++) {
            if ((int) $dates[$i - 1]->diffInDays($dates[$i]) === 1) {
                $streak++;
            } else {
                break;
            }
        }

        return $streak;
    }
}