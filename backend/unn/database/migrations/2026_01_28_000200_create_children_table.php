<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up() {
        Schema::create('children', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('event_id')->nullable();
            $table->unsignedBigInteger('responsible_id')->nullable();
            $table->string('name');
            $table->date('birthdate')->nullable();
            $table->integer('age')->nullable();
            $table->json('sizes')->nullable(); // {shoe: '', clothes:{shirt:'',pants:''}}
            $table->string('toys')->nullable();
            $table->string('phone')->nullable();
            $table->text('address')->nullable();
            $table->string('reference_point')->nullable();
            $table->string('photo')->nullable();
            $table->enum('status', ['available','chosen','purchased','delivered'])->default('available');
            $table->timestamps();

            $table->foreign('event_id')->references('id')->on('events')->onDelete('set null');
            $table->foreign('responsible_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    public function down() {
        Schema::dropIfExists('children');
    }
};