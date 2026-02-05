<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add professional fields to users if not exist
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'occupation')) {
                $table->string('occupation')->nullable()->after('bio');
            }
            if (!Schema::hasColumn('users', 'company')) {
                $table->string('company')->nullable()->after('occupation');
            }
        });

        // Create connections table only if not exists
        if (!Schema::hasTable('connections')) {
            Schema::create('connections', function (Blueprint $table) {
                $table->id();
                $table->foreignId('requester_id')->constrained('users')->onDelete('cascade');
                $table->foreignId('requested_id')->constrained('users')->onDelete('cascade');
                $table->enum('status', ['pending', 'accepted', 'rejected'])->default('pending');
                $table->timestamps();

                $table->unique(['requester_id', 'requested_id']);
            });
        }
    }

    public function down(): void
    {
        // Don't drop table if it might be shared or created by other migrations
        // keeping it safe
        
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'occupation')) {
                $table->dropColumn(['occupation', 'company']);
            }
        });
    }
};
