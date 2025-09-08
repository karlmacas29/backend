<?php

namespace Database\Factories;

use App\Models\excel\Voluntary_work;
use Illuminate\Database\Eloquent\Factories\Factory;

class VoluntaryWorkFactory extends Factory
{
    protected $model = Voluntary_work::class;

    public function definition(): array
    {
        return [
            'organization_name' => $this->faker->company,
            'inclusive_date_from' => $this->faker->date(),
            'inclusive_date_to' => $this->faker->date(),
            'number_of_hours' => $this->faker->numberBetween(10, 300),
            'position' => $this->faker->jobTitle,
            'nPersonalInfo_id' => null,
        ];
    }
}
