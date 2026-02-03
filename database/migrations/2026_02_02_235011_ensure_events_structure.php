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
        if (!Schema::hasTable('events')) {
            Schema::create('events', function (Blueprint $table) {
                $table->id();
                $table->string('title');
                $table->string('speaker')->nullable();
                $table->text('description')->nullable();
                $table->dateTime('start_at')->nullable();
                $table->dateTime('end_at')->nullable();
                $table->string('location')->nullable();
                $table->unsignedDecimal('price', 10, 2)->default(0);
                $table->integer('capacity')->nullable();
                $table->boolean('published')->default(false);
                $table->string('color', 7)->nullable()->default('#3788d8');
                $table->boolean('all_day')->default(false);
                $table->timestamps();
            });
        } else {
            Schema::table('events', function (Blueprint $table) {
                if (!Schema::hasColumn('events', 'color')) {
                    $table->string('color', 7)->nullable()->default('#3788d8')->after('end_at');
                }
                if (!Schema::hasColumn('events', 'all_day')) {
                    $table->boolean('all_day')->default(false)->after('color');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Safety: don't drop table in down if it might have existed before
        Schema::table('events', function (Blueprint $table) {
            if (Schema::hasColumn('events', 'color')) {
                $table->dropColumn('color');
            }
            if (Schema::hasColumn('events', 'all_day')) {
                $table->dropColumn('all_day');
            }
        });
    }
};
