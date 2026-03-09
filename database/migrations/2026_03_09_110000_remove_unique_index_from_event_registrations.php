<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('event_registrations')) {
            return;
        }

        $driver = DB::connection()->getDriverName();

        if ($driver === 'mysql') {
            $index = DB::selectOne(
                "SHOW INDEX FROM event_registrations WHERE Key_name = 'event_registrations_event_id_user_id_unique'"
            );

            if ($index) {
                DB::statement('ALTER TABLE event_registrations DROP INDEX event_registrations_event_id_user_id_unique');
            }

            return;
        }

        if ($driver === 'sqlite') {
            $indexes = DB::select("PRAGMA index_list('event_registrations')");

            foreach ($indexes as $index) {
                if (($index->name ?? null) === 'event_registrations_event_id_user_id_unique') {
                    DB::statement('DROP INDEX event_registrations_event_id_user_id_unique');
                    break;
                }
            }

            return;
        }

        Schema::table('event_registrations', function (Blueprint $table) {
            $table->dropUnique('event_registrations_event_id_user_id_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (!Schema::hasTable('event_registrations')) {
            return;
        }

        $driver = DB::connection()->getDriverName();

        if ($driver === 'mysql') {
            $index = DB::selectOne(
                "SHOW INDEX FROM event_registrations WHERE Key_name = 'event_registrations_event_id_user_id_unique'"
            );

            if (!$index) {
                DB::statement('ALTER TABLE event_registrations ADD UNIQUE event_registrations_event_id_user_id_unique (event_id, user_id)');
            }

            return;
        }

        if ($driver === 'sqlite') {
            $indexes = DB::select("PRAGMA index_list('event_registrations')");

            foreach ($indexes as $index) {
                if (($index->name ?? null) === 'event_registrations_event_id_user_id_unique') {
                    return;
                }
            }

            DB::statement('CREATE UNIQUE INDEX event_registrations_event_id_user_id_unique ON event_registrations (event_id, user_id)');
            return;
        }

        Schema::table('event_registrations', function (Blueprint $table) {
            $table->unique(['event_id', 'user_id']);
        });
    }
};
