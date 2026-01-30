<?php

namespace Database\Factories;

use App\Models\Pay;
use Illuminate\Database\Eloquent\Factories\Factory;

class PayFactory extends Factory
{
    protected $model = Pay::class;

    public function definition(): array
    {
        return [
            'pay_name' => $this->faker->randomElement(['支付宝', '微信支付', 'PayPal', 'Stripe']),
            'pay_check' => $this->faker->word(),
            'pay_method' => 'GET',
            'pay_client' => Pay::CLIENT_ALL,
            'merchant_id' => $this->faker->uuid(),
            'merchant_key' => $this->faker->sha256(),
            'merchant_pem' => null,
            'pay_handleroute' => '/pay/test',
            'is_open' => Pay::STATUS_OPEN,
            'ord' => $this->faker->numberBetween(1, 100),
            'fee_rate' => 0.00,
            'fee_fixed' => 0.00,
        ];
    }

    public function closed(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_open' => Pay::STATUS_CLOSE,
        ]);
    }

    public function withFee(float $rate = 2.0, float $fixed = 0.0): static
    {
        return $this->state(fn (array $attributes) => [
            'fee_rate' => $rate,
            'fee_fixed' => $fixed,
        ]);
    }
}
