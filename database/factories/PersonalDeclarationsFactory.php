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
            'question_34a' => $this->faker->boolean,
            'question_34b' => $this->faker->boolean,
            'response_34' => $this->faker->optional()->sentence,

            'question_35a' => $this->faker->boolean,
            'response_35a' => $this->faker->optional()->sentence,
            'question_35b' => $this->faker->boolean,
            'response_35b_date' => $this->faker->optional()->date(),
            'response_35b_status' => $this->faker->optional()->word,

            'question_36' => $this->faker->boolean,
            'response_36' => $this->faker->optional()->sentence,

            'question_37' => $this->faker->boolean,
            'response_37' => $this->faker->optional()->sentence,

            'question_38a' => $this->faker->boolean,
            'response_38a' => $this->faker->optional()->sentence,
            'question_38b' => $this->faker->boolean,
            'response_38b' => $this->faker->optional()->sentence,

            'question_39' => $this->faker->boolean,
            'response_39' => $this->faker->optional()->sentence,

            'question_40a' => $this->faker->boolean,
            'response_40a' => $this->faker->optional()->sentence,
            'question_40b' => $this->faker->boolean,
            'response_40b' => $this->faker->optional()->sentence,
            'question_40c' => $this->faker->boolean,
            'response_40c' => $this->faker->optional()->sentence,

            'nPersonalInfo_id' => null,
        ];
    }
}
