<?php
namespace Database\Factories;

use App\Models\JobVacancy;
use Illuminate\Database\Eloquent\Factories\Factory;

class JobVacancyFactory extends Factory
{
    protected $model = JobVacancy::class;

    public function definition()
    {
        return [
            'user_id' => 1,
            'title' => $this->faker->jobTitle,
            'company_name' => $this->faker->company,
            'location' => $this->faker->city . ', ' . $this->faker->stateAbbr,
            'type' => $this->faker->randomElement(['CLT', 'PJ', 'Freelancer', 'Estágio']),
            'short_description' => $this->faker->sentence(8),
            'description' => $this->faker->paragraph(4),
            'requirements' => json_encode([$this->faker->word, $this->faker->word, $this->faker->word]),
            'benefits' => json_encode([$this->faker->word, $this->faker->word]),
            'salary_range' => 'R$ ' . $this->faker->numberBetween(3000, 10000) . ' - R$ ' . $this->faker->numberBetween(10000, 20000),
            'visibility' => 'public',
            'is_active' => true,
            'expires_at' => now()->addDays($this->faker->numberBetween(10, 60)),
        ];
    }
}
