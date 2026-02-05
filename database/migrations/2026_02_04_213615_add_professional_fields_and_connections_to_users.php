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
        // Add professional fields to users
        Schema::table('users', function (Blueprint $table) {
            $table->string('occupation')->nullable()->after('bio'); // Cargo/Função
            $table->string('company')->nullable()->after('occupation'); // Empresa
        });

        // Create connections table
        Schema::create('connections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('requester_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('requested_id')->constrained('users')->onDelete('cascade');
            $table->enum('status', ['pending', 'accepted', 'rejected'])->default('pending');
            $table->timestamps();

            // Prevent duplicate connections (A->B is same as B->A logic will be handled in app, 
            // but unique constraint helps A->B)
            $table->unique(['requester_id', 'requested_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('connections');

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['occupation', 'company']);
        });
    }
};
