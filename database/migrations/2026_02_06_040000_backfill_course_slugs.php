<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('courses')) {
            return;
        }

        if (!Schema::hasColumn('courses', 'slug') || !Schema::hasColumn('courses', 'title')) {
            return;
        }

        DB::table('courses')
            ->select(['id', 'title', 'slug'])
            ->where(function ($q) {
                $q->whereNull('slug')->orWhere('slug', '');
            })
            ->orderBy('id')
            ->chunkById(100, function ($rows) {
                foreach ($rows as $row) {
                    $title = trim((string) ($row->title ?? ''));
                    $base = Str::slug($title);
                    if ($base === '') {
                        $base = 'curso';
                    }

                    // Keep room for suffixes
                    $base = substr($base, 0, 180);

                    $slug = $base . '-' . (int) $row->id;

                    $suffix = 2;
                    while (
                        DB::table('courses')
                            ->where('slug', $slug)
                            ->where('id', '!=', (int) $row->id)
                            ->exists()
                    ) {
                        $slug = $base . '-' . (int) $row->id . '-' . $suffix;
                        $suffix++;
                    }

                    DB::table('courses')
                        ->where('id', (int) $row->id)
                        ->update(['slug' => $slug]);
                }
            });
    }

    public function down(): void
    {
        // no-op: do not remove slugs
    }
};

