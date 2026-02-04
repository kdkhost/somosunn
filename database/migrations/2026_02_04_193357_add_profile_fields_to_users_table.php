<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            // Endereço completo particionado
            if (!Schema::hasColumn('users', 'street')) {
                $table->string('street')->nullable()->after('cep');
            }
            if (!Schema::hasColumn('users', 'number')) {
                $table->string('number')->nullable()->after('street');
            }
            if (!Schema::hasColumn('users', 'complement')) {
                $table->string('complement')->nullable()->after('number');
            }
            if (!Schema::hasColumn('users', 'neighborhood')) {
                $table->string('neighborhood')->nullable()->after('complement');
            }
            if (!Schema::hasColumn('users', 'city')) {
                $table->string('city')->nullable()->after('neighborhood');
            }
            if (!Schema::hasColumn('users', 'state')) {
                $table->string('state', 2)->nullable()->after('city');
            }
            
            // Redes sociais
            if (!Schema::hasColumn('users', 'website')) {
                $table->string('website')->nullable();
            }
            if (!Schema::hasColumn('users', 'facebook')) {
                $table->string('facebook')->nullable();
            }
            if (!Schema::hasColumn('users', 'instagram')) {
                $table->string('instagram')->nullable();
            }
            if (!Schema::hasColumn('users', 'twitter')) {
                $table->string('twitter')->nullable();
            }
            if (!Schema::hasColumn('users', 'linkedin')) {
                $table->string('linkedin')->nullable();
            }
            if (!Schema::hasColumn('users', 'youtube')) {
                $table->string('youtube')->nullable();
            }
            
            // Privacidade
            if (!Schema::hasColumn('users', 'show_email_public')) {
                $table->boolean('show_email_public')->default(false);
            }
            if (!Schema::hasColumn('users', 'show_phone_public')) {
                $table->boolean('show_phone_public')->default(false);
            }
            if (!Schema::hasColumn('users', 'show_address_public')) {
                $table->boolean('show_address_public')->default(false);
            }
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'street', 'number', 'complement', 'neighborhood', 'city', 'state',
                'website', 'facebook', 'instagram', 'twitter', 'linkedin', 'youtube',
                'show_email_public', 'show_phone_public', 'show_address_public'
            ]);
        });
    }
};
