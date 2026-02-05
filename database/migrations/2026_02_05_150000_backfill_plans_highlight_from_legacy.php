<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('plans')) {
            return;
        }

        if (!Schema::hasColumn('plans', 'highlight')) {
            Schema::table('plans', function (Blueprint $table) {
                $table->boolean('highlight')->default(false);
            });
        }

        $hasIsFeatured = Schema::hasColumn('plans', 'is_featured');
        $hasHighlightLegacy = Schema::hasColumn('plans', 'highlight_legacy');

        // 1) If highlight already exists, keep the most recently updated highlighted plan (prefer active).
        $highlightedId = DB::table('plans')
            ->where('highlight', 1)
            ->orderByDesc('is_active')
            ->orderByDesc('updated_at')
            ->value('id');

        // 2) Otherwise, backfill from is_featured / highlight_legacy (prefer active).
        if (!$highlightedId && $hasIsFeatured) {
            $highlightedId = DB::table('plans')
                ->where('is_featured', 1)
                ->orderByDesc('is_active')
                ->orderByDesc('updated_at')
                ->value('id');
        }

        if (!$highlightedId && $hasHighlightLegacy) {
            $highlightedId = DB::table('plans')
                ->where('highlight_legacy', 1)
                ->orderByDesc('is_active')
                ->orderByDesc('updated_at')
                ->value('id');
        }

        // Ensure a single highlighted plan.
        DB::table('plans')->update(['highlight' => 0]);
        if ($highlightedId) {
            DB::table('plans')->where('id', $highlightedId)->update(['highlight' => 1]);
        }

        // Keep compatibility: is_featured mirrors highlight when present.
        if ($hasIsFeatured) {
            DB::table('plans')->update(['is_featured' => 0]);
            if ($highlightedId) {
                DB::table('plans')->where('id', $highlightedId)->update(['is_featured' => 1]);
            }
        }
    }

    public function down(): void
    {
        // No-op: this migration is a data backfill for consistency.
    }
};
