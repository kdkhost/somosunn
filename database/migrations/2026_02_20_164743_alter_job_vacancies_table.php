<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('job_vacancies') || !Schema::hasColumn('job_vacancies', 'visibility')) {
            return;
        }

        $driver = DB::getDriverName();

        if ($driver === 'mysql') {
            DB::statement("ALTER TABLE job_vacancies MODIFY visibility VARCHAR(20) NOT NULL DEFAULT 'both'");
        }

        DB::table('job_vacancies')->whereNull('visibility')->update(['visibility' => 'both']);
        DB::table('job_vacancies')->where('visibility', '')->update(['visibility' => 'both']);
        DB::table('job_vacancies')->where('visibility', 'public')->update(['visibility' => 'external']);
        DB::table('job_vacancies')->where('visibility', 'private')->update(['visibility' => 'internal']);
        DB::table('job_vacancies')
            ->whereNotIn('visibility', ['internal', 'external', 'both'])
            ->update(['visibility' => 'both']);
    }

    public function down(): void
    {
        if (!Schema::hasTable('job_vacancies') || !Schema::hasColumn('job_vacancies', 'visibility')) {
            return;
        }

        $driver = DB::getDriverName();

        DB::table('job_vacancies')->whereNull('visibility')->update(['visibility' => 'public']);
        DB::table('job_vacancies')->where('visibility', '')->update(['visibility' => 'public']);
        DB::table('job_vacancies')->where('visibility', 'external')->update(['visibility' => 'public']);
        DB::table('job_vacancies')->where('visibility', 'both')->update(['visibility' => 'public']);
        DB::table('job_vacancies')->where('visibility', 'internal')->update(['visibility' => 'private']);

        if ($driver === 'mysql') {
            DB::statement("ALTER TABLE job_vacancies MODIFY visibility VARCHAR(20) NOT NULL DEFAULT 'public'");
        }
    }
};
