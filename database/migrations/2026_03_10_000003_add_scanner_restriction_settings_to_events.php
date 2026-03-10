<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('events')) {
            return;
        }

        Schema::table('events', function (Blueprint $table) {
            if (!Schema::hasColumn('events', 'scanner_restriction_mode')) {
                $table->string('scanner_restriction_mode', 20)->nullable()->after('is_ticket_enabled');
            }

            if (!Schema::hasColumn('events', 'scanner_radius_meters')) {
                $table->unsignedInteger('scanner_radius_meters')->nullable()->after('scanner_restriction_mode');
            }
        });

        if (Schema::hasColumn('events', 'scanner_restriction_mode')) {
            if (Schema::hasColumn('events', 'latitude') && Schema::hasColumn('events', 'longitude')) {
                DB::table('events')
                    ->whereNull('scanner_restriction_mode')
                    ->whereNotNull('latitude')
                    ->whereNotNull('longitude')
                    ->update([
                        'scanner_restriction_mode' => 'radius',
                        'scanner_radius_meters' => 50,
                    ]);
            }

            DB::table('events')
                ->whereNull('scanner_restriction_mode')
                ->update([
                    'scanner_restriction_mode' => 'disabled',
                ]);
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable('events')) {
            return;
        }

        Schema::table('events', function (Blueprint $table) {
            if (Schema::hasColumn('events', 'scanner_radius_meters')) {
                $table->dropColumn('scanner_radius_meters');
            }

            if (Schema::hasColumn('events', 'scanner_restriction_mode')) {
                $table->dropColumn('scanner_restriction_mode');
            }
        });
    }
};
