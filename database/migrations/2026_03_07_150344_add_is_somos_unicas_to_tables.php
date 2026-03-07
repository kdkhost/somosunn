<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('events', function (Blueprint $table) {
            if (!Schema::hasColumn('events', 'is_somos_unicas')) {
                $table->boolean('is_somos_unicas')->default(false)->after('title');
            }
        });

        Schema::table('courses', function (Blueprint $table) {
            if (!Schema::hasColumn('courses', 'is_somos_unicas')) {
                $table->boolean('is_somos_unicas')->default(false)->after('title');
            }
        });

        Schema::table('mentorships', function (Blueprint $table) {
            if (!Schema::hasColumn('mentorships', 'is_somos_unicas')) {
                $table->boolean('is_somos_unicas')->default(false)->after('title');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn('is_somos_unicas');
        });

        Schema::table('courses', function (Blueprint $table) {
            $table->dropColumn('is_somos_unicas');
        });

        Schema::table('mentorships', function (Blueprint $table) {
            $table->dropColumn('is_somos_unicas');
        });
    }
};
