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
            $table->boolean('is_ticket_enabled')->default(false)->after('visibility');
        });

        Schema::table('event_registrations', function (Blueprint $table) {
            $table->string('ticket_code')->nullable()->unique()->after('status');
            $table->timestamp('check_in_at')->nullable()->after('ticket_code');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn('is_ticket_enabled');
        });

        Schema::table('event_registrations', function (Blueprint $table) {
            $table->dropColumn(['ticket_code', 'check_in_at']);
        });
    }
};
