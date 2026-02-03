<?php

namespace App\Services;

use App\Models\PointsRule;
use App\Models\PointsLog;
use App\Models\User;

class PointsService
{
    public function award(User $user, string $actionKey, $meta = null)
    {
        $rule = PointsRule::where('key', $actionKey)->where('active', true)->first();
        if(!$rule) return false;

        $points = (int) $rule->points;
        // create log and update user
        PointsLog::create([ 'user_id' => $user->id, 'action_key' => $actionKey, 'points' => $points, 'meta' => json_encode($meta) ]);
        $user->increment('points', $points);
        return true;
    }

    public function revoke(User $user, string $actionKey, $meta = null)
    {
        // find last log
        $log = PointsLog::where('user_id', $user->id)->where('action_key', $actionKey)->latest()->first();
        if(!$log) return false;
        $user->decrement('points', $log->points);
        $log->delete();
        return true;
    }
}