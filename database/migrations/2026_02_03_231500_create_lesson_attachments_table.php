<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('lesson_attachments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lesson_id')->constrained()->cascadeOnDelete();
            $table->string('file_path');
            $table->string('file_name'); // Display name (renamable)
            $table->string('file_type')->nullable(); // Mime type extension
            $table->integer('file_size')->nullable(); // Bytes
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('lesson_attachments');
    }
};
