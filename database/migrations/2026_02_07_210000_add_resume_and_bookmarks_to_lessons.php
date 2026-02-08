<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('lesson_progress')) {
            if (!Schema::hasColumn('lesson_progress', 'current_time_seconds')) {
                Schema::table('lesson_progress', function (Blueprint $table) {
                    $table->unsignedInteger('current_time_seconds')->nullable()->after('completed_at');
                });
            }

            if (!Schema::hasColumn('lesson_progress', 'last_position_at')) {
                Schema::table('lesson_progress', function (Blueprint $table) {
                    $table->timestamp('last_position_at')->nullable()->after('current_time_seconds');
                });
            }
        }

        if (!Schema::hasTable('lesson_bookmarks')) {
            Schema::create('lesson_bookmarks', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->foreignId('course_id')->constrained()->cascadeOnDelete();
                $table->foreignId('lesson_id')->constrained()->cascadeOnDelete();
                $table->unsignedInteger('position_seconds');
                $table->string('note', 1000);
                $table->timestamps();

                $table->index(['user_id', 'lesson_id']);
                $table->index(['course_id', 'lesson_id']);
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('lesson_bookmarks')) {
            Schema::dropIfExists('lesson_bookmarks');
        }

        if (Schema::hasTable('lesson_progress')) {
            if (Schema::hasColumn('lesson_progress', 'last_position_at')) {
                Schema::table('lesson_progress', function (Blueprint $table) {
                    $table->dropColumn('last_position_at');
                });
            }

            if (Schema::hasColumn('lesson_progress', 'current_time_seconds')) {
                Schema::table('lesson_progress', function (Blueprint $table) {
                    $table->dropColumn('current_time_seconds');
                });
            }
        }
    }
};

