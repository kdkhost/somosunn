<?php

namespace App\Services;

use App\Models\Certificate;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\EventRegistration;
use App\Models\ItemReview;
use App\Models\Mentorship;
use App\Models\PointsLog;
use App\Models\PointsRule;
use App\Models\User;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class LegacyMemberPointsBackfillService
{
    private const ONE_TIME_ACTIONS = [
        'signup',
        'complete_profile',
        'first_course',
        'mentor',
    ];

    private const RECORD_ACTIONS = [
        'complete_course',
        'earn_certificate',
        'attend_event',
        'attend_mentorship',
        'review',
    ];

    private const ACTIONS = [
        ...self::ONE_TIME_ACTIONS,
        ...self::RECORD_ACTIONS,
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

            $existingTokens = $this->existingLogTokens($chunkUserIds);
            $completedCourses = $this->completedCoursesByUser($chunkUserIds);
            $mentorUsers = $this->mentorUsers($chunkUserIds);
            $courseCertificates = $this->courseCertificatesByUser($chunkUserIds);
            $eventAttendances = $this->eventAttendancesByUser($chunkUserIds);
            $mentorshipAttendances = $this->mentorshipAttendancesByUser($chunkUserIds);
            $reviews = $this->reviewsByUser($chunkUserIds);

            foreach ($users as $user) {
                $summary['users_scanned']++;
                $awardedForUser = 0;

                $awardedForUser += $this->awardIfEligible($user, 'signup', true, $existingTokens, $rulePoints, $dryRun, $pointsService, $summary);
                $awardedForUser += $this->awardIfEligible($user, 'complete_profile', $user->isProfileComplete(), $existingTokens, $rulePoints, $dryRun, $pointsService, $summary);
                $awardedForUser += $this->awardIfEligible($user, 'first_course', !empty($completedCourses[$user->id]), $existingTokens, $rulePoints, $dryRun, $pointsService, $summary);
                $awardedForUser += $this->awardIfEligible($user, 'mentor', isset($mentorUsers[$user->id]), $existingTokens, $rulePoints, $dryRun, $pointsService, $summary);

                foreach ($completedCourses[$user->id] ?? [] as $record) {
                    $awardedForUser += $this->awardRecordAction($user, 'complete_course', $record, $existingTokens, $rulePoints, $dryRun, $summary);
                }

                foreach ($courseCertificates[$user->id] ?? [] as $record) {
                    $awardedForUser += $this->awardRecordAction($user, 'earn_certificate', $record, $existingTokens, $rulePoints, $dryRun, $summary);
                }

                foreach ($eventAttendances[$user->id] ?? [] as $record) {
                    $awardedForUser += $this->awardRecordAction($user, 'attend_event', $record, $existingTokens, $rulePoints, $dryRun, $summary);
                }

                foreach ($mentorshipAttendances[$user->id] ?? [] as $record) {
                    $awardedForUser += $this->awardRecordAction($user, 'attend_mentorship', $record, $existingTokens, $rulePoints, $dryRun, $summary);
                }

                foreach ($reviews[$user->id] ?? [] as $record) {
                    $awardedForUser += $this->awardRecordAction($user, 'review', $record, $existingTokens, $rulePoints, $dryRun, $summary);
                }

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

    private function existingLogTokens(Collection $userIds): array
    {
        $tokens = [];

        $logs = PointsLog::query()
            ->whereIn('user_id', $userIds)
            ->whereIn('action_key', self::ACTIONS)
            ->get(['user_id', 'action_key', 'meta']);

        foreach ($logs as $log) {
            $userId = (int) $log->user_id;
            $token = $this->existingTokenForLog((string) $log->action_key, $log->meta);

            if ($token === null) {
                continue;
            }

            $tokens[$userId][$token] = true;
        }

        return $tokens;
    }

    private function existingTokenForLog(string $actionKey, mixed $meta): ?string
    {
        $data = [];

        if (is_string($meta) && $meta !== '') {
            $decoded = json_decode($meta, true);
            $data = is_array($decoded) ? $decoded : [];
        } elseif (is_array($meta)) {
            $data = $meta;
        }

        return match ($actionKey) {
            'signup',
            'complete_profile',
            'first_course',
            'mentor' => $actionKey,
            'complete_course',
            'earn_certificate' => isset($data['course_id']) ? "{$actionKey}:course_id:" . (int) $data['course_id'] : "{$actionKey}:*",
            'attend_event' => isset($data['event_id']) ? "{$actionKey}:event_id:" . (int) $data['event_id'] : "{$actionKey}:*",
            'attend_mentorship' => isset($data['mentorship_id']) ? "{$actionKey}:mentorship_id:" . (int) $data['mentorship_id'] : "{$actionKey}:*",
            'review' => isset($data['reviewable_type'], $data['reviewable_id'])
                ? "{$actionKey}:" . trim((string) $data['reviewable_type']) . ':' . (int) $data['reviewable_id']
                : "{$actionKey}:*",
            default => null,
        };
    }

    private function awardIfEligible(
        User $user,
        string $actionKey,
        bool $eligible,
        array &$existingTokens,
        array $rulePoints,
        bool $dryRun,
        PointsService $pointsService,
        array &$summary
    ): int {
        if (!$eligible || !isset($rulePoints[$actionKey])) {
            return 0;
        }

        $token = $actionKey;
        if (($existingTokens[$user->id][$token] ?? false) === true) {
            return 0;
        }

        if ($dryRun) {
            $existingTokens[$user->id][$token] = true;
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

        $existingTokens[$user->id][$token] = true;
        $summary['actions_awarded'][$actionKey]++;
        $summary['points_added'] += (int) $rulePoints[$actionKey];

        return (int) $rulePoints[$actionKey];
    }

    private function awardRecordAction(
        User $user,
        string $actionKey,
        array $record,
        array &$existingTokens,
        array $rulePoints,
        bool $dryRun,
        array &$summary
    ): int {
        if (!isset($rulePoints[$actionKey])) {
            return 0;
        }

        $token = $this->tokenForRecord($actionKey, $record);
        if ($token === null) {
            return 0;
        }

        $wildcardToken = "{$actionKey}:*";
        if (($existingTokens[$user->id][$wildcardToken] ?? false) === true || ($existingTokens[$user->id][$token] ?? false) === true) {
            return 0;
        }

        if ($dryRun) {
            $existingTokens[$user->id][$token] = true;
            $summary['actions_awarded'][$actionKey]++;
            $summary['points_added'] += (int) $rulePoints[$actionKey];

            return (int) $rulePoints[$actionKey];
        }

        $points = (int) $rulePoints[$actionKey];
        $meta = $this->metaForRecord($actionKey, $record);
        $happenedAt = $this->normalizeTimestamp($record['happened_at'] ?? null);

        DB::transaction(function () use ($user, $actionKey, $points, $meta, $happenedAt) {
            $log = new PointsLog([
                'user_id' => $user->id,
                'action_key' => $actionKey,
                'points' => $points,
                'meta' => json_encode($meta, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            ]);

            $log->created_at = $happenedAt;
            $log->updated_at = $happenedAt;
            $log->save();

            $user->increment('points', $points);
        });

        $existingTokens[$user->id][$token] = true;
        $summary['actions_awarded'][$actionKey]++;
        $summary['points_added'] += $points;

        return $points;
    }

    private function tokenForRecord(string $actionKey, array $record): ?string
    {
        return match ($actionKey) {
            'complete_course',
            'earn_certificate' => isset($record['course_id']) ? "{$actionKey}:course_id:" . (int) $record['course_id'] : null,
            'attend_event' => isset($record['event_id']) ? "{$actionKey}:event_id:" . (int) $record['event_id'] : null,
            'attend_mentorship' => isset($record['mentorship_id']) ? "{$actionKey}:mentorship_id:" . (int) $record['mentorship_id'] : null,
            'review' => isset($record['reviewable_type'], $record['reviewable_id'])
                ? "{$actionKey}:" . trim((string) $record['reviewable_type']) . ':' . (int) $record['reviewable_id']
                : null,
            default => null,
        };
    }

    private function metaForRecord(string $actionKey, array $record): array
    {
        $base = [
            'source' => 'legacy_member_points_backfill',
            'reason' => 'reconciliacao_historica',
        ];

        return match ($actionKey) {
            'complete_course',
            'earn_certificate' => $base + ['course_id' => (int) $record['course_id']],
            'attend_event' => $base + ['event_id' => (int) $record['event_id']],
            'attend_mentorship' => $base + ['mentorship_id' => (int) $record['mentorship_id']],
            'review' => $base + [
                'reviewable_type' => trim((string) $record['reviewable_type']),
                'reviewable_id' => (int) $record['reviewable_id'],
            ],
            default => $base,
        };
    }

    private function normalizeTimestamp(mixed $value): CarbonInterface
    {
        if ($value instanceof CarbonInterface) {
            return $value;
        }

        if (is_string($value) && trim($value) !== '') {
            return Carbon::parse($value);
        }

        return now();
    }

    private function completedCoursesByUser(Collection $userIds): array
    {
        if (!Schema::hasTable('enrollments')) {
            return [];
        }

        $records = [];

        Enrollment::query()
            ->whereIn('user_id', $userIds)
            ->where('enrollable_type', Course::class)
            ->where(function ($query) {
                $query->where('status', 'completed')
                    ->orWhereNotNull('completed_at');
            })
            ->orderBy('user_id')
            ->get(['user_id', 'enrollable_id', 'completed_at', 'updated_at', 'created_at'])
            ->each(function (Enrollment $enrollment) use (&$records) {
                $userId = (int) $enrollment->user_id;
                $courseId = (int) $enrollment->enrollable_id;
                if ($courseId <= 0 || isset($records[$userId][$courseId])) {
                    return;
                }

                $records[$userId][$courseId] = [
                    'course_id' => $courseId,
                    'happened_at' => $enrollment->completed_at ?? $enrollment->updated_at ?? $enrollment->created_at,
                ];
            });

        return array_map(fn (array $items) => array_values($items), $records);
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

    private function courseCertificatesByUser(Collection $userIds): array
    {
        if (!Schema::hasTable('certificates') || !Schema::hasColumn('certificates', 'course_id')) {
            return [];
        }

        $records = [];

        Certificate::query()
            ->whereIn('user_id', $userIds)
            ->whereNotNull('course_id')
            ->orderBy('user_id')
            ->get(['user_id', 'course_id', 'issued_at', 'created_at', 'updated_at'])
            ->each(function (Certificate $certificate) use (&$records) {
                $userId = (int) $certificate->user_id;
                $courseId = (int) $certificate->course_id;
                if ($courseId <= 0 || isset($records[$userId][$courseId])) {
                    return;
                }

                $records[$userId][$courseId] = [
                    'course_id' => $courseId,
                    'happened_at' => $certificate->issued_at ?? $certificate->created_at ?? $certificate->updated_at,
                ];
            });

        return array_map(fn (array $items) => array_values($items), $records);
    }

    private function eventAttendancesByUser(Collection $userIds): array
    {
        if (!Schema::hasTable('event_registrations')) {
            return [];
        }

        $records = [];

        EventRegistration::query()
            ->whereIn('user_id', $userIds)
            ->whereIn('status', EventRegistration::COUNTED_STATUSES)
            ->orderBy('user_id')
            ->get(['user_id', 'event_id', 'created_at', 'updated_at'])
            ->each(function (EventRegistration $registration) use (&$records) {
                $userId = (int) $registration->user_id;
                $eventId = (int) $registration->event_id;
                if ($eventId <= 0 || isset($records[$userId][$eventId])) {
                    return;
                }

                $records[$userId][$eventId] = [
                    'event_id' => $eventId,
                    'happened_at' => $registration->updated_at ?? $registration->created_at,
                ];
            });

        return array_map(fn (array $items) => array_values($items), $records);
    }

    private function mentorshipAttendancesByUser(Collection $userIds): array
    {
        if (!Schema::hasTable('enrollments')) {
            return [];
        }

        $records = [];

        Enrollment::query()
            ->whereIn('user_id', $userIds)
            ->where('enrollable_type', Mentorship::class)
            ->orderBy('user_id')
            ->get(['user_id', 'enrollable_id', 'started_at', 'created_at', 'updated_at'])
            ->each(function (Enrollment $enrollment) use (&$records) {
                $userId = (int) $enrollment->user_id;
                $mentorshipId = (int) $enrollment->enrollable_id;
                if ($mentorshipId <= 0 || isset($records[$userId][$mentorshipId])) {
                    return;
                }

                $records[$userId][$mentorshipId] = [
                    'mentorship_id' => $mentorshipId,
                    'happened_at' => $enrollment->started_at ?? $enrollment->created_at ?? $enrollment->updated_at,
                ];
            });

        return array_map(fn (array $items) => array_values($items), $records);
    }

    private function reviewsByUser(Collection $userIds): array
    {
        if (!Schema::hasTable('item_reviews')) {
            return [];
        }

        $records = [];

        ItemReview::query()
            ->whereIn('user_id', $userIds)
            ->orderBy('user_id')
            ->get(['user_id', 'reviewable_type', 'reviewable_id', 'created_at'])
            ->each(function (ItemReview $review) use (&$records) {
                $userId = (int) $review->user_id;
                $reviewableId = (int) $review->reviewable_id;
                $reviewableType = trim((string) $review->reviewable_type);
                $key = $reviewableType . ':' . $reviewableId;

                if ($reviewableId <= 0 || $reviewableType === '' || isset($records[$userId][$key])) {
                    return;
                }

                $records[$userId][$key] = [
                    'reviewable_type' => $reviewableType,
                    'reviewable_id' => $reviewableId,
                    'happened_at' => $review->created_at,
                ];
            });

        return array_map(fn (array $items) => array_values($items), $records);
    }
}
