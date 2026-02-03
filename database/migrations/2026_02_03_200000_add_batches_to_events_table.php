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
        Schema::table('events', function (Blueprint $table) {
            $table->decimal('batch_1_price', 10, 2)->nullable();
            $table->dateTime('batch_1_deadline')->nullable();
            
            $table->decimal('batch_2_price', 10, 2)->nullable();
            $table->dateTime('batch_2_deadline')->nullable();
            
            $table->decimal('batch_3_price', 10, 2)->nullable();
            $table->dateTime('batch_3_deadline')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn([
                'batch_1_price', 'batch_1_deadline',
                'batch_2_price', 'batch_2_deadline',
                'batch_3_price', 'batch_3_deadline'
            ]);
        });
    }
};
