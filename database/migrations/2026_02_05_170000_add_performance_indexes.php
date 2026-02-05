<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('events') && Schema::hasColumn('events', 'published') && Schema::hasColumn('events', 'start_at')) {
            try {
                Schema::table('events', function (Blueprint $table) {
                    $table->index(['published', 'start_at'], 'events_published_start_at_idx');
                });
            } catch (\Throwable $e) {
                // Ignore if index already exists or driver doesn't support it the same way.
            }
        }

        if (Schema::hasTable('plans') && Schema::hasColumn('plans', 'is_active')) {
            if (Schema::hasColumn('plans', 'highlight')) {
                try {
                    Schema::table('plans', function (Blueprint $table) {
                        $table->index(['is_active', 'highlight'], 'plans_active_highlight_idx');
                    });
                } catch (\Throwable $e) {
                }
            } elseif (Schema::hasColumn('plans', 'is_featured')) {
                try {
                    Schema::table('plans', function (Blueprint $table) {
                        $table->index(['is_active', 'is_featured'], 'plans_active_featured_idx');
                    });
                } catch (\Throwable $e) {
                }
            }
        }

        if (Schema::hasTable('event_registrations') && Schema::hasColumn('event_registrations', 'event_id')) {
            if (Schema::hasColumn('event_registrations', 'status')) {
                try {
                    Schema::table('event_registrations', function (Blueprint $table) {
                        $table->index(['event_id', 'status'], 'event_reg_event_status_idx');
                    });
                } catch (\Throwable $e) {
                }
            }

            if (Schema::hasColumn('event_registrations', 'order_id')) {
                try {
                    Schema::table('event_registrations', function (Blueprint $table) {
                        $table->index(['order_id'], 'event_reg_order_idx');
                    });
                } catch (\Throwable $e) {
                }
            }
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('events')) {
            try {
                Schema::table('events', function (Blueprint $table) {
                    $table->dropIndex('events_published_start_at_idx');
                });
            } catch (\Throwable $e) {
            }
        }

        if (Schema::hasTable('plans')) {
            try {
                Schema::table('plans', function (Blueprint $table) {
                    $table->dropIndex('plans_active_highlight_idx');
                });
            } catch (\Throwable $e) {
            }

            try {
                Schema::table('plans', function (Blueprint $table) {
                    $table->dropIndex('plans_active_featured_idx');
                });
            } catch (\Throwable $e) {
            }
        }

        if (Schema::hasTable('event_registrations')) {
            try {
                Schema::table('event_registrations', function (Blueprint $table) {
                    $table->dropIndex('event_reg_event_status_idx');
                });
            } catch (\Throwable $e) {
            }

            try {
                Schema::table('event_registrations', function (Blueprint $table) {
                    $table->dropIndex('event_reg_order_idx');
                });
            } catch (\Throwable $e) {
            }
        }
    }
};

