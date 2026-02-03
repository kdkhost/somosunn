<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('points_rules', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique(); // e.g., signup, publish, comment
            $table->string('label');
            $table->integer('points')->default(0);
            $table->boolean('active')->default(true);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('points_rules');
    }
};