<?php

namespace Database\Factories;

use App\Models\excel\Children;
use App\Models\excel\nPersonal_info;
use Illuminate\Database\Eloquent\Factories\Factory;

class ChildrenFactory extends Factory
{
    protected $model = Children::class;

    public function definition(): array
    {
        return [
            'child_name' => $this->faker->name,
            'birth_date' => $this->faker->date(),
            'nPersonalInfo_id' => nPersonal_info::factory(), // ✅ Correct way
        ];
    }
}
