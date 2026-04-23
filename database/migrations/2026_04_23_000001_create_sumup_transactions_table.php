<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sumup_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
            $table->string('checkout_id')->unique();
            $table->string('transaction_id')->nullable()->index();
            $table->string('status', 50)->default('PENDING')->index();
            $table->string('payment_type', 50)->default('CARD');
            $table->decimal('amount', 10, 2);
            $table->string('currency', 10)->default('BRL');
            $table->string('webhook_token', 64)->unique();
            $table->string('webhook_url', 500);
            $table->json('raw_response')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sumup_transactions');
    }
};
