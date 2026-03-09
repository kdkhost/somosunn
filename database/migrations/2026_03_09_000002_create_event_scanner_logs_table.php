<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('event_scanner_logs')) {
            return;
        }

        Schema::create('event_scanner_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('event_id')->nullable()->index();
            $table->unsignedBigInteger('event_registration_id')->nullable()->index();
            $table->unsignedBigInteger('scanner_user_id')->nullable()->index();
            $table->string('ticket_code')->nullable()->index();
            $table->string('scanner_context', 50)->index();
            $table->string('outcome', 20)->index();
            $table->string('status_code', 60)->index();
            $table->text('message');
            $table->decimal('distance_meters', 10, 2)->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamp('attempted_at')->nullable()->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('event_scanner_logs');
    }
};
