<?php

namespace Database\Factories;

use App\Models\excel\Work_experience;
use Illuminate\Database\Eloquent\Factories\Factory;

class WorkExperienceFactory extends Factory
{
    protected $model = Work_experience::class;

    public function definition(): array
    {
        return [
            'work_date_from' => $this->faker->date(),
            'work_date_to' => $this->faker->date(),
            'position_title' => $this->faker->jobTitle,
            'department' => $this->faker->company,
            'monthly_salary' => $this->faker->numberBetween(15000, 80000),
            'salary_grade' => $this->faker->optional()->randomNumber(2),
            'status_of_appointment' => $this->faker->randomElement(['Permanent', 'Contractual', 'Temporary']),
            'government_service' => $this->faker->boolean,
            'nPersonalInfo_id' => null,
        ];
    }
}
