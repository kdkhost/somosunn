<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'google_id')) {
                $table->string('google_id')->nullable()->after('email');
            }
            if (!Schema::hasColumn('users', 'facebook_id')) {
                $table->string('facebook_id')->nullable()->after('email');
            }
            if (!Schema::hasColumn('users', 'linkedin_id')) {
                $table->string('linkedin_id')->nullable()->after('email');
            }
            if (!Schema::hasColumn('users', 'avatar')) {
                $table->string('avatar')->nullable()->after('email');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $columnsToDrop = [];
            if (Schema::hasColumn('users', 'google_id')) $columnsToDrop[] = 'google_id';
            if (Schema::hasColumn('users', 'facebook_id')) $columnsToDrop[] = 'facebook_id';
            if (Schema::hasColumn('users', 'linkedin_id')) $columnsToDrop[] = 'linkedin_id';
            if (Schema::hasColumn('users', 'avatar')) $columnsToDrop[] = 'avatar';
            
            if (!empty($columnsToDrop)) {
                $table->dropColumn($columnsToDrop);
            }
        });
    }
};
