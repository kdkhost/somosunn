<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // 1. Ensure short_description exists
        if (!Schema::hasColumn('courses', 'short_description')) {
            Schema::table('courses', function (Blueprint $table) {
                $after = Schema::hasColumn('courses', 'thumbnail') ? 'thumbnail' : 'id';
                if ($after !== 'id') {
                    $table->text('short_description')->nullable()->after($after);
                } else {
                    $table->text('short_description')->nullable();
                }
            });
        }

        // 2. Ensure full_description exists
        if (!Schema::hasColumn('courses', 'full_description')) {
            Schema::table('courses', function (Blueprint $table) {
                $after = Schema::hasColumn('courses', 'short_description') ? 'short_description' : 'id';
                 if ($after !== 'id') {
                    $table->longText('full_description')->nullable()->after($after);
                } else {
                    $table->longText('full_description')->nullable();
                }
            });
        }
    }

    public function down()
    {
        // No down action to prevent data loss
    }
};
