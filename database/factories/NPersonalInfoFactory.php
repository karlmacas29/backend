<?php

namespace Database\Factories;

use App\Models\excel\nPersonal_info;
use Illuminate\Database\Eloquent\Factories\Factory;

class NPersonalInfoFactory extends Factory
{
    protected $model = nPersonal_info::class;

    public function definition(): array
    {
        return [
            'lastname' => $this->faker->lastName,
            'firstname' => $this->faker->firstName,
            'middlename' => $this->faker->lastName,
            'name_extension' => $this->faker->optional()->suffix,
            'date_of_birth' => $this->faker->date(),
            'sex' => $this->faker->randomElement(['Male', 'Female']),
            'place_of_birth' => $this->faker->city,
            'height' => $this->faker->numberBetween(150, 200),
            'weight' => $this->faker->numberBetween(50, 120),
            'blood_type' => $this->faker->randomElement(['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-']),
            'gsis_no' => $this->faker->numerify('###########'),
            'pagibig_no' => $this->faker->numerify('###########'),
            'philhealth_no' => $this->faker->numerify('###########'),
            'sss_no' => $this->faker->numerify('###########'),
            'tin_no' => $this->faker->numerify('###########'),

            'image_path' => $this->faker->imageUrl(400, 400, 'people', true, 'profile'),

            'civil_status' => $this->faker->randomElement(['Single', 'Married', 'Divorced', 'Widowed']),
            'citizenship' => 'Filipino',
            'citizenship_status' => $this->faker->randomElement(['Natural-born', 'Naturalized']),

            'residential_house' => $this->faker->buildingNumber,
            'residential_street' => $this->faker->streetName,
            'residential_subdivision' => $this->faker->optional()->word,
            'residential_barangay' => $this->faker->word,
            'residential_city' => $this->faker->city,
            'residential_province' => $this->faker->state,
            'residential_zip' => $this->faker->postcode,

            'permanent_house' => $this->faker->buildingNumber,
            'permanent_street' => $this->faker->streetName,
            'permanent_subdivision' => $this->faker->optional()->word,
            'permanent_barangay' => $this->faker->word,
            'permanent_city' => $this->faker->city,
            'permanent_province' => $this->faker->state,
            'permanent_zip' => $this->faker->postcode,

            'excel_file' => $this->faker->fileExtension,
            'telephone_number' => $this->faker->optional()->phoneNumber,
            'cellphone_number' => $this->faker->phoneNumber,
            'email_address' => $this->faker->unique()->safeEmail,
        ];
    }
}
