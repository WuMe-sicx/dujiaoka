<?php

namespace Database\Factories;

use App\Models\Goods;
use App\Models\GoodsGroup;
use Illuminate\Database\Eloquent\Factories\Factory;

class GoodsFactory extends Factory
{
    protected $model = Goods::class;

    public function definition(): array
    {
        return [
            'group_id' => GoodsGroup::factory(),
            'gd_name' => $this->faker->sentence(3),
            'gd_description' => $this->faker->sentence(10),
            'gd_keywords' => $this->faker->words(3, true),
            'picture' => null,
            'retail_price' => $this->faker->randomFloat(2, 10, 500),
            'actual_price' => $this->faker->randomFloat(2, 10, 500),
            'in_stock' => $this->faker->numberBetween(0, 100),
            'sales_volume' => $this->faker->numberBetween(0, 50),
            'ord' => $this->faker->numberBetween(1, 100),
            'buy_limit_num' => $this->faker->numberBetween(1, 10),
            'buy_prompt' => $this->faker->sentence(),
            'description' => '<p>' . $this->faker->paragraph() . '</p>',
            'type' => Goods::AUTOMATIC_DELIVERY,
            'is_open' => 1,
            'stock_alert_threshold' => 10,
            'access_password' => null,
            'purchase_limits' => null,
        ];
    }

    public function manual(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => Goods::MANUAL_PROCESSING,
        ]);
    }

    public function closed(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_open' => 0,
        ]);
    }

    public function withPassword(string $password = 'secret'): static
    {
        return $this->state(fn (array $attributes) => [
            'access_password' => $password,
        ]);
    }

    public function withPurchaseLimits(array $limits): static
    {
        return $this->state(fn (array $attributes) => [
            'purchase_limits' => $limits,
        ]);
    }
}
