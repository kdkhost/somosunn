<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('courses', function (Blueprint $table) {
            if (!Schema::hasColumn('courses', 'full_description')) {
                $table->longText('full_description')->nullable()->after('short_description');
            }
        });
    }

    public function down()
    {
        // No down action to prevent data loss
    }
};
