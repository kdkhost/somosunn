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

            $table->string('code', 40)->unique();
            $table->string('name')->nullable();
            $table->text('description')->nullable();

            $table->string('discount_type', 20); // percent|fixed
            $table->decimal('discount_value', 10, 2)->default(0);

            $table->boolean('is_active')->default(true);

            $table->string('applies_to', 20)->default('all'); // all|event|course|mentorship
            $table->unsignedBigInteger('applies_to_id')->nullable();

            $table->decimal('min_amount', 10, 2)->nullable();
            $table->unsignedInteger('max_uses')->nullable();
            $table->unsignedInteger('max_uses_per_user')->nullable();

            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();

            $table->timestamps();

            $table->index(['is_active', 'code']);
            $table->index(['applies_to', 'applies_to_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('coupons');
    }
};

