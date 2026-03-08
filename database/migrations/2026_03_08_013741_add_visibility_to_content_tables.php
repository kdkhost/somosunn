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
        Schema::table('courses', function (Blueprint $table) {
            if (!Schema::hasColumn('courses', 'visibility')) {
                $table->string('visibility', 20)->default('ambos')->after('status');
            }
        });

        Schema::table('events', function (Blueprint $table) {
            if (!Schema::hasColumn('events', 'visibility')) {
                $table->string('visibility', 20)->default('ambos')->after('published');
            }
        });

        Schema::table('mentorships', function (Blueprint $table) {
            if (!Schema::hasColumn('mentorships', 'visibility')) {
                $table->string('visibility', 20)->default('ambos')->after('is_somos_unicas');
            }
        });

        // Migrate existing datad
        \Illuminate\Support\Facades\DB::table('courses')->where('is_somos_unicas', 1)->update(['visibility' => 'somos_unicas']);
        \Illuminate\Support\Facades\DB::table('events')->where('is_somos_unicas', 1)->update(['visibility' => 'somos_unicas']);
        \Illuminate\Support\Facades\DB::table('mentorships')->where('is_somos_unicas', 1)->update(['visibility' => 'somos_unicas']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            $table->dropColumn('visibility');
        });
        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn('visibility');
        });
        Schema::table('mentorships', function (Blueprint $table) {
            $table->dropColumn('visibility');
        });
    }
};
