<?php

namespace Database\Factories;

use App\Models\excel\Education_background;
use Illuminate\Database\Eloquent\Factories\Factory;

class EducationBackgroundFactory extends Factory
{
    protected $model = Education_background::class;

    public function definition(): array
    {
        return [
            'school_name' => $this->faker->company . ' University',
            'degree' => $this->faker->randomElement(['BSIT', 'BSCS', 'BSEE', 'BSN']),
            'attendance_from' => $this->faker->year,
            'attendance_to' => $this->faker->year,
            'highest_units' => $this->faker->randomNumber(2),
            'year_graduated' => $this->faker->year,
            'scholarship' => $this->faker->optional()->word,
            'level' => $this->faker->randomElement(['Elementary', 'High School', 'College']),
            'nPersonalInfo_id' => null,
        ];
    }
}
