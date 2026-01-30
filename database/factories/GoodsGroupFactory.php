<?php

namespace Database\Factories;

use App\Models\GoodsGroup;
use Illuminate\Database\Eloquent\Factories\Factory;

class GoodsGroupFactory extends Factory
{
    protected $model = GoodsGroup::class;

    public function definition(): array
    {
        return [
            'gp_name' => $this->faker->word() . '分类',
            'ord' => $this->faker->numberBetween(1, 100),
            'is_open' => 1,
        ];
    }

    public function closed(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_open' => 0,
        ]);
    }
}
