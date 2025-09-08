<?php

namespace Database\Factories;

use App\Models\excel\references;
use Illuminate\Database\Eloquent\Factories\Factory;

class ReferencesFactory extends Factory
{
    protected $model = references::class;

    public function definition(): array
    {
        return [
            'full_name' => $this->faker->name,
            'address' => $this->faker->address,
            'contact_number' => $this->faker->phoneNumber,
            'nPersonalInfo_id' => null,
        ];
    }
}
