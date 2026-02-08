<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'segment')) {
                $table->string('segment')->nullable()->after('company');
            }
            if (!Schema::hasColumn('users', 'interests')) {
                $table->text('interests')->nullable()->after('segment');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'interests')) {
                $table->dropColumn('interests');
            }
            if (Schema::hasColumn('users', 'segment')) {
                $table->dropColumn('segment');
            }
        });
    }
};
