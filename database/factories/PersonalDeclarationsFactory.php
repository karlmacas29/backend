<?php

namespace Database\Factories;

use App\Models\excel\Personal_declarations;
use Illuminate\Database\Eloquent\Factories\Factory;

class PersonalDeclarationsFactory extends Factory
{
    protected $model = Personal_declarations::class;

    public function definition(): array
    {
        return [
            'a_third_degree_answer' => $this->faker->boolean,
            'b_fourth_degree_answer' => $this->faker->boolean,
            '34_if_yes' => $this->faker->optional()->sentence,

            'a_found_guilty' => $this->faker->boolean,
            'guilty_yes' => $this->faker->optional()->sentence,
            'b_criminally_charged' => $this->faker->boolean,
            'case_date_filed' => $this->faker->optional()->date(),
            'case_status' => $this->faker->optional()->word,

            '36_convited_answer' => $this->faker->boolean,
            '36_if_yes' => $this->faker->optional()->sentence,

            '37_service' => $this->faker->boolean,
            '37_if_yes' => $this->faker->optional()->sentence,

            'a_candidate' => $this->faker->boolean,
            'candidate_yes' => $this->faker->optional()->sentence,
            'b_resigned' => $this->faker->boolean,
            'resigned_yes' => $this->faker->optional()->sentence,

            '39_status' => $this->faker->boolean,
            '39_if_yes' => $this->faker->optional()->sentence,

            'a_indigenous' => $this->faker->boolean,
            'indigenous_yes' => $this->faker->optional()->sentence,
            'b_disability' => $this->faker->boolean,
            'disability_yes' => $this->faker->optional()->sentence,
            'c_solo' => $this->faker->boolean,
            'solo_parent_yes' => $this->faker->optional()->sentence,

            'nPersonalInfo_id' => null,
        ];
    }
}
