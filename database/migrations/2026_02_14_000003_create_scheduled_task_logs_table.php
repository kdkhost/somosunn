<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('scheduled_task_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('scheduled_task_id');
            $table->timestamp('executed_at');
            $table->text('output')->nullable();
            $table->boolean('success')->default(true);
            $table->timestamps();
            $table->foreign('scheduled_task_id')->references('id')->on('scheduled_tasks')->onDelete('cascade');
        });
    }
    public function down()
    {
        Schema::dropIfExists('scheduled_task_logs');
    }
};
