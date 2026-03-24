<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'lgpd_accepted_at')) {
                $table->timestamp('lgpd_accepted_at')->nullable()->after('remember_token');
            }

            if (!Schema::hasColumn('users', 'lgpd_version')) {
                $table->string('lgpd_version', 64)->nullable()->after('lgpd_accepted_at');
            }

            if (!Schema::hasColumn('users', 'lgpd_accept_ip')) {
                $table->string('lgpd_accept_ip', 45)->nullable()->after('lgpd_version');
            }

            if (!Schema::hasColumn('users', 'lgpd_accept_user_agent')) {
                $table->text('lgpd_accept_user_agent')->nullable()->after('lgpd_accept_ip');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $columns = [
                'lgpd_accepted_at',
                'lgpd_version',
                'lgpd_accept_ip',
                'lgpd_accept_user_agent',
            ];

            $existing = array_values(array_filter($columns, static fn (string $column) => Schema::hasColumn('users', $column)));

            if ($existing !== []) {
                $table->dropColumn($existing);
            }
        });
    }
};
