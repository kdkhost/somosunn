<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('permission_plan', function (Blueprint $table) {
            $table->unsignedBigInteger('plan_id');
            $table->unsignedBigInteger('permission_id');
            $table->primary(['plan_id','permission_id']);
            $table->foreign('plan_id')->references('id')->on('plans')->onDelete('cascade');
            $table->foreign('permission_id')->references('id')->on('permissions')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('permission_plan');
    }
};
