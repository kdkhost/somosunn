<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $this->addEventGroupLink();
        $this->createEventCouponsTable();
        $this->addRegistrationFields();
        $this->seedPermissions();
    }

    public function down(): void
    {
        // Rollback conservador: esta migration ja pode conter inscricoes,
        // cupons, links de grupo e permissoes em uso financeiro/operacional.
        // A remocao estrutural deve ser feita manualmente em uma janela
        // controlada, com backup e analise dos dados existentes.
    }

    private function addEventGroupLink(): void
    {
        if (!Schema::hasTable('events') || Schema::hasColumn('events', 'whatsapp_group_link')) {
            return;
        }

        Schema::table('events', function (Blueprint $table) {
            $table->string('whatsapp_group_link', 2048)->nullable();
        });
    }

    private function createEventCouponsTable(): void
    {
        if (Schema::hasTable('event_coupons')) {
            return;
        }

        Schema::create('event_coupons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained('events')->cascadeOnDelete();
            $table->string('code', 40);
            $table->string('type', 20)->default('free');
            $table->decimal('discount_value', 10, 2)->default(100);
            $table->unsignedInteger('max_uses')->nullable();
            $table->unsignedInteger('used_count')->default(0);
            $table->dateTime('starts_at')->nullable();
            $table->dateTime('expires_at')->nullable();
            $table->boolean('active')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['event_id', 'code'], 'event_coupons_event_code_unique');
            $table->index(['event_id', 'active']);
            $table->index('expires_at');
        });
    }

    private function addRegistrationFields(): void
    {
        if (!Schema::hasTable('event_registrations')) {
            return;
        }

        Schema::table('event_registrations', function (Blueprint $table) {
            if (!Schema::hasColumn('event_registrations', 'coupon_id')) {
                $table->foreignId('coupon_id')->nullable()->constrained('event_coupons')->nullOnDelete();
            }

            if (!Schema::hasColumn('event_registrations', 'payment_status')) {
                $table->string('payment_status', 30)->default('pending');
            }

            if (!Schema::hasColumn('event_registrations', 'joined_group_at')) {
                $table->timestamp('joined_group_at')->nullable();
            }
        });
    }

    private function seedPermissions(): void
    {
        if (!Schema::hasTable('permissions')) {
            return;
        }

        $labels = [
            'admin.events.coupons.view' => 'Ver cupons de eventos',
            'admin.events.coupons.create' => 'Criar cupons de eventos',
            'admin.events.coupons.edit' => 'Editar cupons de eventos',
            'admin.events.coupons.delete' => 'Excluir cupons de eventos',
            'admin.events.coupons.toggle' => 'Ativar/desativar cupons de eventos',
            'admin.events.group_link.manage' => 'Gerenciar link do grupo do evento',
        ];

        foreach ($labels as $name => $label) {
            $payload = [
                'label' => $label,
                'created_at' => now(),
                'updated_at' => now(),
            ];

            if (Schema::hasColumn('permissions', 'category')) {
                $payload['category'] = 'Eventos';
            }

            if (Schema::hasColumn('permissions', 'sort_order')) {
                $payload['sort_order'] = 0;
            }

            DB::table('permissions')->updateOrInsert(['name' => $name], $payload);
        }

        if (!Schema::hasTable('roles') || !Schema::hasTable('permission_role')) {
            return;
        }

        $roleIds = DB::table('roles')
            ->whereIn('name', ['superadmin', 'admin'])
            ->pluck('id', 'name');
        $permissionIds = DB::table('permissions')
            ->whereIn('name', array_keys($labels))
            ->pluck('id');

        foreach ($roleIds as $roleId) {
            foreach ($permissionIds as $permissionId) {
                DB::table('permission_role')->updateOrInsert([
                    'role_id' => $roleId,
                    'permission_id' => $permissionId,
                ], [
                    'role_id' => $roleId,
                    'permission_id' => $permissionId,
                ]);
            }
        }
    }

    private function permissionNames(): array
    {
        return [
            'admin.events.coupons.view',
            'admin.events.coupons.create',
            'admin.events.coupons.edit',
            'admin.events.coupons.delete',
            'admin.events.coupons.toggle',
            'admin.events.group_link.manage',
        ];
    }
};
