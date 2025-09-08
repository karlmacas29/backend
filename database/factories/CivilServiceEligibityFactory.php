<?php

namespace Database\Factories;

use App\Models\excel\Civil_service_eligibity;
use Illuminate\Database\Eloquent\Factories\Factory;

class CivilServiceEligibityFactory extends Factory
{
    protected $model = Civil_service_eligibity::class;

    public function definition(): array
    {
        return [
            'eligibility' => $this->faker->randomElement(['Professional', 'Sub-professional']),
            'rating' => $this->faker->numberBetween(70, 100),
            'date_of_examination' => $this->faker->date(),
            'place_of_examination' => $this->faker->city,
            'license_number' => $this->faker->numerify('LIC#####'),
            'date_of_validity' => $this->faker->date(),
            'nPersonalInfo_id' => null,
        ];
    }
}
