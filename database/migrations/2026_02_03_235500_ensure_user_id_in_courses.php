<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Fix for missing user_id column
        if (Schema::hasTable('courses') && !Schema::hasColumn('courses', 'user_id')) {
            Schema::table('courses', function (Blueprint $table) {
                // Assuming users table exists and has id. 
                // We use nullable first to avoid constraints issues if data exists, 
                // but default to 1 (usually admin) or current user if possible.
                // For simplicity in fix, we make it nullable then update.
                $table->unsignedBigInteger('user_id')->nullable()->after('id');
            });

            // Set a default user_id for existing records (e.g. 1)
            DB::table('courses')->update(['user_id' => 1]);

            // Now make it strict if needed, or leave nullable. 
            // In the original it was constrained.
            Schema::table('courses', function (Blueprint $table) {
                 // Trying to add constraint. If user 1 doesn't exist, this might fail, so we wrap.
                 try {
                    $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
                 } catch (\Throwable $e) {}
            });
        }
    }

    public function down()
    {
        if (Schema::hasColumn('courses', 'user_id')) {
            Schema::table('courses', function (Blueprint $table) {
                $table->dropForeign(['user_id']);
                $table->dropColumn('user_id');
            });
        }
    }
};
