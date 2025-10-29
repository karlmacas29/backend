<?php

namespace Database\Factories;

use App\Models\excel\nFamily;
use Illuminate\Database\Eloquent\Factories\Factory;

class FamilyFactory extends Factory
{
    protected $model = nFamily::class;

    public function definition(): array
    {
        return [
            'spouse_name' => $this->faker->lastName,
            'spouse_firstname' => $this->faker->firstName,
            'spouse_middlename' => $this->faker->lastName,
            'spouse_extension' => $this->faker->optional()->suffix,
            'spouse_occupation' => $this->faker->jobTitle,
            'spouse_employer' => $this->faker->company,
            'spouse_employer_address' => $this->faker->address,
            'spouse_employer_telephone' => $this->faker->phoneNumber,
            'father_lastname' => $this->faker->lastName,
            'father_firstname' => $this->faker->firstName,
            'father_middlename' => $this->faker->lastName,
            'father_extension' => $this->faker->optional()->suffix,
            'mother_lastname' => $this->faker->lastName,
            'mother_firstname' => $this->faker->firstName,
            'mother_middlename' => $this->faker->lastName,
            'mother_maidenname' => $this->faker->lastName,
            'nPersonalInfo_id' => null,
        ];
    }
}
