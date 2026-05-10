<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('events', 'slug')) {
            return;
        }

        $events = DB::table('events')
            ->where(function ($q) {
                $q->whereNull('slug')->orWhere('slug', '');
            })
            ->select('id', 'title')
            ->get();

        foreach ($events as $event) {
            $base = Str::slug($event->title ?: 'evento');
            $slug = $base . '-' . substr(uniqid(), -6);

            // Garante unicidade
            $attempts = 0;
            while (DB::table('events')->where('slug', $slug)->where('id', '!=', $event->id)->exists()) {
                $slug = $base . '-' . substr(uniqid(), -6);
                if (++$attempts > 5) break;
            }

            DB::table('events')->where('id', $event->id)->update(['slug' => $slug]);
        }
    }

    public function down(): void
    {
        // Não reverte slugs gerados (seria destrutivo).
    }
};
