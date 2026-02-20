<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\JobVacancy;

class JobVacancySeeder extends Seeder
{
    public function run()
    {
        JobVacancy::factory()->count(5)->create([
            'is_active' => true,
            'visibility' => 'public',
        ]);
    }
}
