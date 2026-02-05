<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('connections')) {
            try {
                Schema::table('connections', function (Blueprint $table) {
                    $table->index(['requester_id', 'status'], 'connections_requester_status_idx');
                });
            } catch (\Throwable $e) {
                // ignore if already exists
            }

            try {
                Schema::table('connections', function (Blueprint $table) {
                    $table->index(['requested_id', 'status'], 'connections_requested_status_idx');
                });
            } catch (\Throwable $e) {
                // ignore if already exists
            }
        }

        if (Schema::hasTable('users')) {
            if (Schema::hasColumn('users', 'name')) {
                try {
                    Schema::table('users', function (Blueprint $table) {
                        $table->index(['name'], 'users_name_idx');
                    });
                } catch (\Throwable $e) {
                }
            }

            if (Schema::hasColumn('users', 'city') && Schema::hasColumn('users', 'state')) {
                try {
                    Schema::table('users', function (Blueprint $table) {
                        $table->index(['city', 'state'], 'users_city_state_idx');
                    });
                } catch (\Throwable $e) {
                }
            }

            if (Schema::hasColumn('users', 'level')) {
                try {
                    Schema::table('users', function (Blueprint $table) {
                        $table->index(['level'], 'users_level_idx');
                    });
                } catch (\Throwable $e) {
                }
            }

            if (Schema::hasColumn('users', 'role')) {
                try {
                    Schema::table('users', function (Blueprint $table) {
                        $table->index(['role'], 'users_role_idx');
                    });
                } catch (\Throwable $e) {
                }
            }
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('connections')) {
            try {
                Schema::table('connections', function (Blueprint $table) {
                    $table->dropIndex('connections_requester_status_idx');
                });
            } catch (\Throwable $e) {
            }

            try {
                Schema::table('connections', function (Blueprint $table) {
                    $table->dropIndex('connections_requested_status_idx');
                });
            } catch (\Throwable $e) {
            }
        }

        if (Schema::hasTable('users')) {
            try {
                Schema::table('users', function (Blueprint $table) {
                    $table->dropIndex('users_name_idx');
                });
            } catch (\Throwable $e) {
            }

            try {
                Schema::table('users', function (Blueprint $table) {
                    $table->dropIndex('users_city_state_idx');
                });
            } catch (\Throwable $e) {
            }

            try {
                Schema::table('users', function (Blueprint $table) {
                    $table->dropIndex('users_level_idx');
                });
            } catch (\Throwable $e) {
            }

            try {
                Schema::table('users', function (Blueprint $table) {
                    $table->dropIndex('users_role_idx');
                });
            } catch (\Throwable $e) {
            }
        }
    }
};

