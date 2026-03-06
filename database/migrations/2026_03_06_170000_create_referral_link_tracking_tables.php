<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('referral_link_visits')) {
            Schema::create('referral_link_visits', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('referrer_user_id')->nullable();
                $table->string('referral_code', 20)->nullable();
                $table->string('session_id', 120)->nullable();
                $table->string('visitor_token', 64)->nullable();
                $table->unsignedBigInteger('registered_user_id')->nullable();
                $table->string('landing_page_path', 255)->nullable();
                $table->text('landing_page_url')->nullable();
                $table->string('first_page_path', 255)->nullable();
                $table->text('first_page_url')->nullable();
                $table->string('last_page_path', 255)->nullable();
                $table->text('last_page_url')->nullable();
                $table->text('referrer_url')->nullable();
                $table->string('utm_source', 120)->nullable();
                $table->string('utm_medium', 120)->nullable();
                $table->string('utm_campaign', 120)->nullable();
                $table->string('utm_content', 120)->nullable();
                $table->string('utm_term', 120)->nullable();
                $table->string('ip_hash', 64)->nullable();
                $table->text('user_agent')->nullable();
                $table->string('country', 10)->nullable();
                $table->string('region', 120)->nullable();
                $table->string('city', 120)->nullable();
                $table->unsignedInteger('clicks_count')->default(0);
                $table->unsignedInteger('pageviews_count')->default(0);
                $table->unsignedInteger('checkout_started_count')->default(0);
                $table->unsignedInteger('purchases_count')->default(0);
                $table->timestamp('first_visited_at')->nullable();
                $table->timestamp('last_visited_at')->nullable();
                $table->timestamp('registered_at')->nullable();
                $table->timestamp('first_checkout_started_at')->nullable();
                $table->timestamp('first_purchase_at')->nullable();
                $table->timestamp('last_purchase_at')->nullable();
                $table->unsignedBigInteger('first_order_id')->nullable();
                $table->unsignedBigInteger('first_paid_order_id')->nullable();
                $table->unsignedBigInteger('first_plan_id')->nullable();
                $table->decimal('total_revenue_amount', 12, 2)->default(0);
                $table->timestamps();

                $table->index(['referrer_user_id', 'created_at']);
                $table->index(['referral_code', 'created_at']);
                $table->index(['registered_user_id', 'created_at']);
                $table->index(['visitor_token', 'created_at']);
                $table->index(['session_id', 'created_at']);
            });
        }

        if (!Schema::hasTable('referral_link_events')) {
            Schema::create('referral_link_events', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('referral_link_visit_id')->nullable();
                $table->unsignedBigInteger('referrer_user_id')->nullable();
                $table->unsignedBigInteger('actor_user_id')->nullable();
                $table->unsignedBigInteger('registered_user_id')->nullable();
                $table->string('event_type', 40);
                $table->string('channel', 40)->nullable();
                $table->string('page_path', 255)->nullable();
                $table->text('page_url')->nullable();
                $table->unsignedBigInteger('order_id')->nullable();
                $table->unsignedBigInteger('plan_id')->nullable();
                $table->decimal('amount', 12, 2)->nullable();
                $table->json('metadata')->nullable();
                $table->timestamp('occurred_at')->nullable();
                $table->timestamps();

                $table->index(['referrer_user_id', 'event_type', 'created_at'], 'referral_link_events_referrer_type_created_idx');
                $table->index(['registered_user_id', 'event_type', 'created_at'], 'referral_link_events_registered_type_created_idx');
                $table->index(['order_id', 'event_type'], 'referral_link_events_order_type_idx');
                $table->index(['channel', 'event_type'], 'referral_link_events_channel_type_idx');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('referral_link_events');
        Schema::dropIfExists('referral_link_visits');
    }
};
