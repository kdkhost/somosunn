<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $this->addEventExhibitorColumns();
        $this->createRegistrationsTable();
        $this->seedPermission();
    }

    public function down(): void
    {
        Schema::dropIfExists('event_exhibitor_registrations');

        if (Schema::hasTable('events')) {
            $columns = [
                'exhibitor_sales_enabled',
                'exhibitor_total_slots',
                'exhibitor_description',
                'exhibitor_internal_notes',
                'exhibitor_area_image',
                'exhibitor_includes_ticket',
                'exhibitor_batch_1_price',
                'exhibitor_batch_1_deadline',
                'exhibitor_batch_1_slots',
                'exhibitor_batch_2_price',
                'exhibitor_batch_2_deadline',
                'exhibitor_batch_2_slots',
                'exhibitor_batch_3_price',
                'exhibitor_batch_3_deadline',
                'exhibitor_batch_3_slots',
                'exhibitor_show_publicly',
            ];

            $existing = array_values(array_filter($columns, fn ($column) => Schema::hasColumn('events', $column)));
            if (!empty($existing)) {
                Schema::table('events', function (Blueprint $table) use ($existing) {
                    $table->dropColumn($existing);
                });
            }
        }

        if (Schema::hasTable('permissions')) {
            DB::table('permissions')->where('name', 'events.exhibitors.manage')->delete();
        }
    }

    private function addEventExhibitorColumns(): void
    {
        if (!Schema::hasTable('events')) {
            return;
        }

        $columns = [
            'exhibitor_sales_enabled' => fn (Blueprint $table) => $table->boolean('exhibitor_sales_enabled')->default(false),
            'exhibitor_total_slots' => fn (Blueprint $table) => $table->unsignedInteger('exhibitor_total_slots')->nullable(),
            'exhibitor_description' => fn (Blueprint $table) => $table->text('exhibitor_description')->nullable(),
            'exhibitor_internal_notes' => fn (Blueprint $table) => $table->text('exhibitor_internal_notes')->nullable(),
            'exhibitor_area_image' => fn (Blueprint $table) => $table->string('exhibitor_area_image')->nullable(),
            'exhibitor_includes_ticket' => fn (Blueprint $table) => $table->boolean('exhibitor_includes_ticket')->default(false),
            'exhibitor_batch_1_price' => fn (Blueprint $table) => $table->decimal('exhibitor_batch_1_price', 10, 2)->nullable(),
            'exhibitor_batch_1_deadline' => fn (Blueprint $table) => $table->dateTime('exhibitor_batch_1_deadline')->nullable(),
            'exhibitor_batch_1_slots' => fn (Blueprint $table) => $table->unsignedInteger('exhibitor_batch_1_slots')->nullable(),
            'exhibitor_batch_2_price' => fn (Blueprint $table) => $table->decimal('exhibitor_batch_2_price', 10, 2)->nullable(),
            'exhibitor_batch_2_deadline' => fn (Blueprint $table) => $table->dateTime('exhibitor_batch_2_deadline')->nullable(),
            'exhibitor_batch_2_slots' => fn (Blueprint $table) => $table->unsignedInteger('exhibitor_batch_2_slots')->nullable(),
            'exhibitor_batch_3_price' => fn (Blueprint $table) => $table->decimal('exhibitor_batch_3_price', 10, 2)->nullable(),
            'exhibitor_batch_3_deadline' => fn (Blueprint $table) => $table->dateTime('exhibitor_batch_3_deadline')->nullable(),
            'exhibitor_batch_3_slots' => fn (Blueprint $table) => $table->unsignedInteger('exhibitor_batch_3_slots')->nullable(),
            'exhibitor_show_publicly' => fn (Blueprint $table) => $table->boolean('exhibitor_show_publicly')->default(true),
        ];

        foreach ($columns as $column => $definition) {
            if (Schema::hasColumn('events', $column)) {
                continue;
            }

            Schema::table('events', function (Blueprint $table) use ($definition) {
                $definition($table);
            });
        }
    }

    private function createRegistrationsTable(): void
    {
        if (Schema::hasTable('event_exhibitor_registrations')) {
            return;
        }

        Schema::create('event_exhibitor_registrations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained('events')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
            $table->string('name');
            $table->string('email');
            $table->string('phone', 50)->nullable();
            $table->string('document', 30)->nullable();
            $table->string('company_name')->nullable();
            $table->string('company_document', 30)->nullable();
            $table->string('brand_name')->nullable();
            $table->text('description')->nullable();
            $table->unsignedInteger('quantity')->default(1);
            $table->decimal('unit_price', 10, 2)->default(0);
            $table->decimal('total_price', 10, 2)->default(0);
            $table->string('batch_label', 30)->nullable();
            $table->string('status', 30)->default('pending');
            $table->string('payment_status', 30)->default('pending');
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('event_id');
            $table->index('user_id');
            $table->index('order_id');
            $table->index('status');
            $table->index('payment_status');
        });
    }

    private function seedPermission(): void
    {
        if (!Schema::hasTable('permissions')) {
            return;
        }

        $payload = [
            'label' => 'Gerenciar areas de expositores',
            'updated_at' => now(),
            'created_at' => now(),
        ];

        if (Schema::hasColumn('permissions', 'category')) {
            $payload['category'] = 'Eventos';
        }

        if (Schema::hasColumn('permissions', 'sort_order')) {
            $payload['sort_order'] = 0;
        }

        DB::table('permissions')->updateOrInsert(
            ['name' => 'events.exhibitors.manage'],
            $payload
        );
    }
};
