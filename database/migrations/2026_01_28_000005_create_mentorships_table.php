<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('mentorships', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->foreignId('mentor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('description')->nullable();
            $table->unsignedDecimal('price', 10, 2)->default(0);
            $table->integer('slots')->nullable();
            $table->json('schedule')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('mentorships');
    }
};