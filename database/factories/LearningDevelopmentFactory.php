<?php

namespace Database\Factories;

use App\Models\excel\Learning_development;
use Illuminate\Database\Eloquent\Factories\Factory;

class LearningDevelopmentFactory extends Factory
{
    protected $model = Learning_development::class;

    public function definition(): array
    {
        return [
            'training_title' => $this->faker->sentence(3),
            'inclusive_date_from' => $this->faker->date(),
            'inclusive_date_to' => $this->faker->date(),
            'number_of_hours' => $this->faker->numberBetween(4, 120),
            'type' => $this->faker->randomElement(['Technical', 'Management', 'Leadership']),
            'conducted_by' => $this->faker->company,
            'nPersonalInfo_id' => null,
        ];
    }
}
