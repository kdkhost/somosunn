<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('mentorships', function (Blueprint $table) {
            $table->string('type')->default('online')->after('schedule'); // online, presencial
            $table->string('video_platform')->nullable()->after('type'); // zoom, google_meet, etc
            $table->string('video_link')->nullable()->after('video_platform');
            $table->string('demo_link')->nullable()->after('video_link');
        });
    }

    public function down()
    {
        Schema::table('mentorships', function (Blueprint $table) {
            $table->dropColumn(['type', 'video_platform', 'video_link', 'demo_link']);
        });
    }
};
