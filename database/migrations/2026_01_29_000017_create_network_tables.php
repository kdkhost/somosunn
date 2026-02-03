<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'level')) {
                $table->string('level')->default('iniciante')->after('role');
            }
        });

        Schema::create('interactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_from_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('user_to_id')->constrained('users')->cascadeOnDelete();
            $table->string('level')->default('iniciante');
            $table->text('message')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();
        });

        Schema::create('satisfactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('interaction_id')->constrained('interactions')->cascadeOnDelete();
            $table->unsignedTinyInteger('rating');
            $table->string('feedback')->nullable();
            $table->boolean('whatsapp_notified')->default(false);
            $table->timestamps();
        });

        Schema::create('rankings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->enum('level', ['iniciante', 'sucesso'])->default('iniciante');
            $table->integer('interactions_count')->default(0);
            $table->decimal('average_rating', 5, 2)->default(0);
            $table->decimal('score', 8, 2)->default(0);
            $table->timestamps();
            $table->unique(['user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rankings');
        Schema::dropIfExists('satisfactions');
        Schema::dropIfExists('interactions');

        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'level')) {
                $table->dropColumn('level');
            }
        });
    }
};
