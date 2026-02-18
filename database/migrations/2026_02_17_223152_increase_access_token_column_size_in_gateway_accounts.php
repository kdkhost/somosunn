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
        Schema::table('gateway_accounts', function (Blueprint $table) {
            $table->text('access_token')->nullable()->change();
            $table->text('public_key')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('gateway_accounts', function (Blueprint $table) {
            $table->string('access_token', 500)->nullable()->change();
            $table->string('public_key', 500)->nullable()->change();
        });
    }
};
