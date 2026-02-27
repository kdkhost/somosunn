<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        if (\Illuminate\Support\Facades\DB::getDriverName() === 'sqlite') {
            Schema::table('job_vacancies', function (Blueprint $table) {
                // SQLite não suporta bem alteração de colunas enum, então faremos uma string de fallback com validação em app logic
                $table->dropColumn('visibility');
            });
            Schema::table('job_vacancies', function (Blueprint $table) {
                $table->string('visibility')->default('public');
            });
        } else {
            Schema::table('job_vacancies', function (Blueprint $table) {
                $table->enum('visibility', ['public', 'private'])->default('public')->change();
            });
        }
    }

    public function down()
    {
        Schema::table('job_vacancies', function (Blueprint $table) {
            $table->string('visibility')->default('public')->change();
        });
    }
};
