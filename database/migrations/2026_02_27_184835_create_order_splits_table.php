<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('order_splits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->onDelete('cascade');
            $table->enum('receiver_type', ['seller', 'platform', 'traffic', 'superadmin']);
            $table->foreignId('receiver_id')->nullable()->constrained('users')->onDelete('set null'); // Preenchido apenas para 'seller' ou 'superadmin'
            $table->decimal('amount', 15, 2);
            $table->decimal('percentage', 5, 2);
            $table->enum('status', ['pending', 'paid'])->default('pending');
            $table->string('pix_key')->nullable(); // Chave PIX utilizada no momento da divisão
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_splits');
    }
};
