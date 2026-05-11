<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->timestamp('blocked_until')->nullable()->after('extra_features');
            $table->string('block_reason', 255)->nullable()->after('blocked_until');
            $table->unsignedSmallInteger('events_suspension_remaining')->default(0)->after('block_reason');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['blocked_until', 'block_reason', 'events_suspension_remaining']);
        });
    }
};
