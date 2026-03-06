<?php

namespace App\Services;

use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Mentorship;
use App\Models\PointsLog;
use App\Models\PointsRule;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;

class LegacyMemberPointsBackfillService
{
    private const ACTIONS = [
        'signup',
        'complete_profile',
        'first_course',
        'mentor',
    ];

    public function run(bool $dryRun = false, int $chunkSize = 200, array $userIds = []): array
    {
        $summary = [
            'dry_run' => $dryRun,
            'users_scanned' => 0,
            'users_affected' => 0,
            'points_added' => 0,
            'actions_awarded' => array_fill_keys(self::ACTIONS, 0),
        ];

        if (!$this->canRun()) {
            $summary['skipped'] = true;

            return $summary;
        }

        $rulePoints = PointsRule::query()
            ->where('active', true)
            ->whereIn('key', self::ACTIONS)
            ->pluck('points', 'key')
            ->map(fn ($points) => (int) $points)
            ->all();

        if ($rulePoints === []) {
            return $summary;
        }

        $query = User::query()->orderBy('id');

        if ($userIds !== []) {
            $query->whereIn('id', array_values(array_unique(array_map('intval', $userIds))));
        }

        $pointsService = app(PointsService::class);

        $query->chunkById($chunkSize, function (Collection $users) use (&$summary, $dryRun, $pointsService, $rulePoints) {
            $chunkUserIds = $users->pluck('id')->map(fn ($id) => (int) $id)->values();

            $existingLogs = PointsLog::query()
                ->whereIn('user_id', $chunkUserIds)
                ->whereIn('action_key', self::ACTIONS)
                ->get(['user_id', 'action_key'])
                ->groupBy('user_id')
                ->map(function (Collection $logs) {
                    return $logs->pluck('action_key')->flip();
                });

            $completedCourseUsers = $this->completedCourseUsers($chunkUserIds);
            $mentorUsers = $this->mentorUsers($chunkUserIds);

            foreach ($users as $user) {
                $summary['users_scanned']++;
                $awardedForUser = 0;
                $awardedForUser += $this->awardIfEligible($user, 'signup', true, $existingLogs, $rulePoints, $dryRun, $pointsService, $summary);
                $awardedForUser += $this->awardIfEligible($user, 'complete_profile', $user->isProfileComplete(), $existingLogs, $rulePoints, $dryRun, $pointsService, $summary);
                $awardedForUser += $this->awardIfEligible($user, 'first_course', isset($completedCourseUsers[$user->id]), $existingLogs, $rulePoints, $dryRun, $pointsService, $summary);
                $awardedForUser += $this->awardIfEligible($user, 'mentor', isset($mentorUsers[$user->id]), $existingLogs, $rulePoints, $dryRun, $pointsService, $summary);

                if ($awardedForUser > 0) {
                    $summary['users_affected']++;
                }
            }
        }, 'id');

        return $summary;
    }

    private function canRun(): bool
    {
        foreach (['users', 'points_rules', 'points_logs'] as $table) {
            if (!Schema::hasTable($table)) {
                return false;
            }
        }

        return Schema::hasColumn('users', 'points');
    }

    private function awardIfEligible(
        User $user,
        string $actionKey,
        bool $eligible,
        Collection $existingLogs,
        array $rulePoints,
        bool $dryRun,
        PointsService $pointsService,
        array &$summary
    ): int {
        if (!$eligible || !isset($rulePoints[$actionKey])) {
            return 0;
        }

        $userLogs = $existingLogs->get($user->id);
        if ($userLogs instanceof Collection && $userLogs->has($actionKey)) {
            return 0;
        }

        if ($dryRun) {
            $summary['actions_awarded'][$actionKey]++;
            $summary['points_added'] += (int) $rulePoints[$actionKey];

            return (int) $rulePoints[$actionKey];
        }

        $awarded = $pointsService->award($user, $actionKey, [
            'source' => 'legacy_member_points_backfill',
            'reason' => 'reconciliacao_historica',
        ]);

        if (!$awarded) {
            return 0;
        }

        $summary['actions_awarded'][$actionKey]++;
        $summary['points_added'] += (int) $rulePoints[$actionKey];

        return (int) $rulePoints[$actionKey];
    }

    private function completedCourseUsers(Collection $userIds): array
    {
        if (!Schema::hasTable('enrollments')) {
            return [];
        }

        return Enrollment::query()
            ->whereIn('user_id', $userIds)
            ->where('enrollable_type', Course::class)
            ->where(function ($query) {
                $query->where('status', 'completed')
                    ->orWhereNotNull('completed_at');
            })
            ->distinct()
            ->pluck('user_id')
            ->mapWithKeys(fn ($userId) => [(int) $userId => true])
            ->all();
    }

    private function mentorUsers(Collection $userIds): array
    {
        if (!Schema::hasTable('mentorships')) {
            return [];
        }

        return Mentorship::query()
            ->whereIn('mentor_id', $userIds)
            ->distinct()
            ->pluck('mentor_id')
            ->mapWithKeys(fn ($userId) => [(int) $userId => true])
            ->all();
    }
}
