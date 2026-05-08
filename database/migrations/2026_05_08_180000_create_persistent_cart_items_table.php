<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('seller_product_cart_items')) {
            Schema::create('seller_product_cart_items', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id')->nullable()->index();
                $table->string('session_id', 80)->nullable()->index();
                $table->unsignedBigInteger('product_id');
                $table->unsignedBigInteger('seller_id');
                $table->unsignedBigInteger('store_id');
                $table->unsignedInteger('quantity')->default(1);
                $table->timestamp('expires_at')->nullable()->index();
                $table->timestamps();

                $table->index(['user_id', 'product_id']);
                $table->index(['session_id', 'product_id']);

                $table->foreign('product_id')
                    ->references('id')
                    ->on('seller_products')
                    ->cascadeOnDelete();
            });
        }

        // Configuração: horas de expiração do carrinho
        $exists = \DB::table('settings')->where('key', 'cart_expiration_hours')->exists();
        if (!$exists) {
            \DB::table('settings')->insert([
                'key' => 'cart_expiration_hours',
                'value' => '24',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('seller_product_cart_items');
        \DB::table('settings')->where('key', 'cart_expiration_hours')->delete();
    }
};
