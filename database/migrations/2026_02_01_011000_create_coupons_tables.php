<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('coupons', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->enum('type', ['percent','fixed'])->default('percent');
            $table->decimal('value', 10, 2);
            $table->integer('max_uses')->nullable();
            $table->integer('max_uses_per_user')->nullable();
            $table->boolean('auto_generate')->default(false);
            $table->boolean('active')->default(true);
            $table->string('target_type')->nullable(); // plan, course, event, mentorship, global
            $table->unsignedBigInteger('target_id')->nullable();
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->timestamps();
        });

        Schema::create('coupon_usages', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('coupon_id');
            $table->unsignedBigInteger('user_id');
            $table->timestamp('used_at');
            $table->foreign('coupon_id')->references('id')->on('coupons')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('coupon_usages');
        Schema::dropIfExists('coupons');
    }
};
