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
        Schema::table('events', function (Blueprint $table) {
            if (!Schema::hasColumn('events', 'type')) {
                $table->enum('type', ['event', 'album'])->default('event')->after('id');
            }
            if (!Schema::hasColumn('events', 'slug')) {
                $table->string('slug')->nullable()->unique()->after('title');
            }
        });

        // Criar o álbum "Onde o network me levou" se não existir
        $exists = \DB::table('events')->where('slug', 'onde-o-network-me-levou')->exists();
        if (!$exists) {
            $admin = \DB::table('users')->where('role', 'superadmin')->first();
            $adminId = $admin ? $admin->id : 1;

            \DB::table('events')->insert([
                'user_id' => $adminId,
                'title' => 'Onde o network me levou',
                'slug' => 'onde-o-network-me-levou',
                'type' => 'album',
                'description' => 'Álbum de fotos da comunidade UNN.',
                'published' => true,
                'start_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            if (Schema::hasColumn('events', 'type')) {
                $table->dropColumn('type');
            }
            if (Schema::hasColumn('events', 'slug')) {
                $table->dropColumn('slug');
            }
        });
    }
};
