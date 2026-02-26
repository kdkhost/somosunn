<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('courses') || !Schema::hasColumn('courses', 'user_id')) {
            return;
        }

        if (Schema::hasColumn('courses', 'created_by')) {
            DB::statement("
                UPDATE courses
                SET user_id = created_by
                WHERE (user_id IS NULL OR user_id = 1)
                  AND created_by IS NOT NULL
                  AND created_by > 0
            ");
        }

        $candidates = DB::table('courses')
            ->select('id', 'user_id', 'author_name')
            ->where(function ($query) {
                $query->whereNull('user_id')
                    ->orWhere('user_id', 1);
            })
            ->whereNotNull('author_name')
            ->get();

        foreach ($candidates as $course) {
            $authorName = trim((string) ($course->author_name ?? ''));
            if ($authorName === '') {
                continue;
            }

            $matchingUserIds = DB::table('users')
                ->where('name', $authorName)
                ->pluck('id');

            if ($matchingUserIds->count() !== 1) {
                continue;
            }

            $targetUserId = (int) $matchingUserIds->first();
            if ($targetUserId <= 0) {
                continue;
            }

            if ((int) ($course->user_id ?? 0) === $targetUserId) {
                continue;
            }

            DB::table('courses')
                ->where('id', $course->id)
                ->update(['user_id' => $targetUserId]);
        }
    }

    public function down(): void
    {
        // No down action: owner backfill is data repair.
    }
};

