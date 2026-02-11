<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('mentorships')) {
            return;
        }

        Schema::table('mentorships', function (Blueprint $table) {
            if (!Schema::hasColumn('mentorships', 'image')) {
                $table->string('image')->nullable()->after('description');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('mentorships')) {
            return;
        }

        Schema::table('mentorships', function (Blueprint $table) {
            if (Schema::hasColumn('mentorships', 'image')) {
                $table->dropColumn('image');
            }
        });
    }
};

