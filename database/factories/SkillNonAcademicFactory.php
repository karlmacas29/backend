<?php

namespace Database\Factories;

use App\Models\excel\skill_non_academic;
use Illuminate\Database\Eloquent\Factories\Factory;

class SkillNonAcademicFactory extends Factory
{
    protected $model = skill_non_academic::class;

    public function definition(): array
    {
        return [
            'skill' => $this->faker->word,
            'non_academic' => $this->faker->sentence(3),
            'organization' => $this->faker->company,
            'nPersonalInfo_id' => null,
        ];
    }
}
