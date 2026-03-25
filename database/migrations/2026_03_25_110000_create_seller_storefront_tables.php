<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('seller_stores')) {
            Schema::create('seller_stores', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
                $table->string('slug')->nullable()->unique();
                $table->string('brand_name');
                $table->string('tagline')->nullable();
                $table->string('logo_path')->nullable();
                $table->string('banner_path')->nullable();
                $table->string('primary_color', 20)->default('#1F5EDB');
                $table->string('accent_color', 20)->default('#0F172A');
                $table->text('bio')->nullable();
                $table->string('support_email')->nullable();
                $table->string('support_phone', 40)->nullable();
                $table->string('whatsapp', 40)->nullable();
                $table->string('website_url')->nullable();
                $table->string('instagram_url')->nullable();
                $table->string('facebook_url')->nullable();
                $table->string('youtube_url')->nullable();
                $table->boolean('is_published')->default(false);
                $table->boolean('is_blocked')->default(false);
                $table->text('blocked_reason')->nullable();
                $table->timestamp('published_at')->nullable();
                $table->timestamp('slug_locked_at')->nullable();
                $table->json('settings')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('seller_products')) {
            Schema::create('seller_products', function (Blueprint $table) {
                $table->id();
                $table->foreignId('seller_store_id')->constrained('seller_stores')->cascadeOnDelete();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->string('slug');
                $table->string('sku', 80)->nullable();
                $table->string('type', 20)->default('digital');
                $table->string('title');
                $table->string('excerpt', 280)->nullable();
                $table->longText('description')->nullable();
                $table->string('cover_path')->nullable();
                $table->decimal('price', 10, 2)->default(0);
                $table->decimal('sale_price', 10, 2)->nullable();
                $table->timestamp('sale_price_ends_at')->nullable();
                $table->unsignedInteger('stock')->nullable();
                $table->unsignedInteger('weight_grams')->nullable();
                $table->unsignedInteger('height_cm')->nullable();
                $table->unsignedInteger('width_cm')->nullable();
                $table->unsignedInteger('length_cm')->nullable();
                $table->string('status', 20)->default('draft');
                $table->boolean('is_featured')->default(false);
                $table->string('digital_delivery_type', 20)->nullable();
                $table->string('digital_file_path')->nullable();
                $table->string('digital_file_name')->nullable();
                $table->text('digital_url')->nullable();
                $table->text('digital_instructions')->nullable();
                $table->json('metadata')->nullable();
                $table->timestamp('published_at')->nullable();
                $table->timestamps();

                $table->unique(['seller_store_id', 'slug']);
                $table->index(['user_id', 'status']);
                $table->index(['seller_store_id', 'status']);
                $table->index(['type', 'status']);
            });
        }

        if (!Schema::hasTable('seller_product_media')) {
            Schema::create('seller_product_media', function (Blueprint $table) {
                $table->id();
                $table->foreignId('seller_product_id')->constrained('seller_products')->cascadeOnDelete();
                $table->string('media_type', 20)->default('image');
                $table->string('file_path');
                $table->unsignedInteger('sort_order')->default(0);
                $table->string('alt_text')->nullable();
                $table->timestamps();

                $table->index(['seller_product_id', 'sort_order']);
            });
        }

        if (!Schema::hasTable('order_shipments')) {
            Schema::create('order_shipments', function (Blueprint $table) {
                $table->id();
                $table->foreignId('order_id')->unique()->constrained()->cascadeOnDelete();
                $table->string('status', 20)->default('pending');
                $table->string('service_code', 40)->nullable();
                $table->string('service_name')->nullable();
                $table->decimal('shipping_amount', 10, 2)->default(0);
                $table->unsignedInteger('delivery_days')->nullable();
                $table->string('tracking_code')->nullable();
                $table->string('recipient_name')->nullable();
                $table->string('recipient_email')->nullable();
                $table->string('recipient_phone', 40)->nullable();
                $table->string('postal_code', 20);
                $table->string('address_line')->nullable();
                $table->string('number', 40)->nullable();
                $table->string('complement')->nullable();
                $table->string('neighborhood')->nullable();
                $table->string('city')->nullable();
                $table->string('state', 10)->nullable();
                $table->json('quote_payload')->nullable();
                $table->timestamp('shipped_at')->nullable();
                $table->timestamp('delivered_at')->nullable();
                $table->timestamps();

                $table->index(['status', 'service_code']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('order_shipments');
        Schema::dropIfExists('seller_product_media');
        Schema::dropIfExists('seller_products');
        Schema::dropIfExists('seller_stores');
    }
};
